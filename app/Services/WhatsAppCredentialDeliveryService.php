<?php

namespace App\Services;

use App\Exceptions\WhatsAppRateLimitException;
use App\Jobs\SendWhatsAppCredential;
use App\Models\WhatsAppCredentialDelivery;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WhatsAppCredentialDeliveryService
{
    private const MAX_ATTEMPTS = 3;

    /**
     * @return list<string>
     */
    public function missingConfiguration(): array
    {
        $keys = [
            'access_token' => 'WHATSAPP_CLOUD_API_TOKEN',
            'phone_number_id' => 'WHATSAPP_PHONE_NUMBER_ID',
            'api_version' => 'WHATSAPP_GRAPH_API_VERSION',
            'template_name' => 'WHATSAPP_CREDENTIAL_TEMPLATE',
        ];

        $missing = [];
        foreach ($keys as $key => $envName) {
            $value = config('services.whatsapp_cloud.'.$key);
            $validFormat = match ($key) {
                'phone_number_id' => is_string($value) && preg_match('/^[0-9]+$/D', $value) === 1,
                'api_version' => is_string($value) && preg_match('/^v[0-9]+\.[0-9]+$/D', $value) === 1,
                'template_name' => is_string($value) && preg_match('/^[A-Za-z0-9_]+$/D', $value) === 1,
                default => filled($value),
            };

            if (! $validFormat) {
                $missing[] = $envName;
            }
        }

        $applicationUrl = config('app.url');
        if (
            ! is_string($applicationUrl)
            || parse_url($applicationUrl, PHP_URL_SCHEME) !== 'https'
            || blank(parse_url($applicationUrl, PHP_URL_HOST))
        ) {
            $missing[] = 'APP_URL (must be a public HTTPS URL)';
        }

        return $missing;
    }

    public function webhookIsConfigured(): bool
    {
        return filled(config('services.whatsapp_cloud.webhook_verify_token'))
            && filled(config('services.whatsapp_cloud.app_secret'));
    }

    public function isValidRecipient(string $phone): bool
    {
        return preg_match('/^\+[1-9][0-9]{7,14}$/D', $phone) === 1;
    }

    public function queueCredentials(
        int $schoolId,
        int $userId,
        string $phone,
        string $username,
        string $password
    ): bool {
        if ($this->missingConfiguration() !== [] || ! $this->isValidRecipient($phone)) {
            return false;
        }

        $delivery = WhatsAppCredentialDelivery::create([
            'school_id' => $schoolId,
            'user_id' => $userId,
            'recipient_number' => $phone,
            'username' => $username,
            'temporary_password' => $password,
            'status' => 'pending',
            'expires_at' => now()->addHour(),
        ]);

        SendWhatsAppCredential::dispatch($delivery->id)->afterCommit();

        return true;
    }

    public function send(int $deliveryId): void
    {
        $delivery = DB::transaction(function () use ($deliveryId): ?WhatsAppCredentialDelivery {
            $delivery = WhatsAppCredentialDelivery::query()
                ->lockForUpdate()
                ->find($deliveryId);

            if (! $delivery || $delivery->status !== 'pending') {
                return null;
            }

            if ($delivery->expires_at->isPast()) {
                $this->clearCredentials($delivery, 'expired', 'Credential delivery expired before sending.');

                return null;
            }

            if ($this->missingConfiguration() !== []) {
                $this->clearCredentials($delivery, 'failed', 'WhatsApp provider configuration is incomplete.');

                return null;
            }

            $delivery->forceFill([
                'status' => 'sending',
                'attempts' => $delivery->attempts + 1,
                'failure_reason' => null,
            ])->save();

            return $delivery;
        });

        if (! $delivery) {
            return;
        }

        try {
            $response = Http::acceptJson()
                ->withToken(config('services.whatsapp_cloud.access_token'))
                ->connectTimeout(5)
                ->timeout(15)
                ->post($this->messagesEndpoint(), [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => ltrim($delivery->recipient_number, '+'),
                    'type' => 'template',
                    'template' => [
                        'name' => config('services.whatsapp_cloud.template_name'),
                        'language' => [
                            'code' => config('services.whatsapp_cloud.template_language'),
                        ],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    ['type' => 'text', 'text' => route('login')],
                                    ['type' => 'text', 'text' => $delivery->username],
                                    ['type' => 'text', 'text' => $delivery->temporary_password],
                                    ['type' => 'text', 'text' => 'Change this temporary password after your first login.'],
                                ],
                            ],
                        ],
                    ],
                ]);
        } catch (ConnectionException) {
            $this->markFailed($delivery, 'Provider response was not received; automatic resend stopped to avoid duplicates.');

            return;
        }

        if ($response->status() === 429) {
            if ($delivery->attempts < self::MAX_ATTEMPTS) {
                $delivery->forceFill([
                    'status' => 'pending',
                    'failure_reason' => 'Provider rate limited the request.',
                ])->save();

                throw new WhatsAppRateLimitException;
            }

            $this->markFailed($delivery, 'Provider rate limit persisted after safe retries.');

            return;
        }

        if (! $response->successful()) {
            $this->markFailed($delivery, 'Provider rejected the credential message (HTTP '.$response->status().').');

            return;
        }

        $providerMessageId = data_get($response->json(), 'messages.0.id');
        if (! is_string($providerMessageId) || $providerMessageId === '') {
            $this->markFailed($delivery, 'Provider response did not confirm message acceptance.');

            return;
        }

        $delivery->forceFill([
            'provider_message_id' => $providerMessageId,
            'status' => 'sent',
            'sent_at' => now(),
            'recipient_number' => null,
            'username' => null,
            'temporary_password' => null,
            'failure_reason' => null,
        ])->save();
    }

    /**
     * Apply an authenticated provider status without allowing older callbacks to roll state back.
     */
    public function updateProviderStatus(string $messageId, string $status): void
    {
        $delivery = WhatsAppCredentialDelivery::query()
            ->where('provider_message_id', $messageId)
            ->first();

        if (! $delivery) {
            return;
        }

        if ($status === 'sent' && $delivery->status === 'pending') {
            $delivery->forceFill(['status' => 'sent', 'sent_at' => now()])->save();

            return;
        }

        if ($status === 'delivered' && $delivery->status === 'sent') {
            $delivery->forceFill(['status' => 'delivered', 'delivered_at' => now()])->save();

            return;
        }

        if ($status === 'read' && in_array($delivery->status, ['sent', 'delivered'], true)) {
            $delivery->forceFill(['status' => 'read', 'delivered_at' => $delivery->delivered_at ?? now()])->save();

            return;
        }

        if ($status === 'failed' && in_array($delivery->status, ['sent'], true)) {
            $delivery->forceFill([
                'status' => 'failed',
                'failure_reason' => 'Provider webhook reported delivery failure.',
            ])->save();
        }
    }

    private function messagesEndpoint(): string
    {
        return sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            config('services.whatsapp_cloud.api_version'),
            config('services.whatsapp_cloud.phone_number_id')
        );
    }

    private function markFailed(WhatsAppCredentialDelivery $delivery, string $reason): void
    {
        $this->clearCredentials($delivery, 'failed', $reason);
    }

    private function clearCredentials(
        WhatsAppCredentialDelivery $delivery,
        string $status,
        string $reason
    ): void {
        $delivery->forceFill([
            'status' => $status,
            'recipient_number' => null,
            'username' => null,
            'temporary_password' => null,
            'failure_reason' => $reason,
        ])->save();
    }
}
