<?php

namespace App\Jobs;

use App\Exceptions\WhatsAppRateLimitException;
use App\Models\WhatsAppCredentialDelivery;
use App\Services\WhatsAppCredentialDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendWhatsAppCredential implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 30;

    public function __construct(public readonly int $deliveryId)
    {
        $this->onQueue('whatsapp');
    }

    public function handle(WhatsAppCredentialDeliveryService $deliveryService): void
    {
        $deliveryService->send($this->deliveryId);
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = WhatsAppCredentialDelivery::find($this->deliveryId);

        if ($delivery && in_array($delivery->status, ['pending', 'sending'], true)) {
            $delivery->forceFill([
                'status' => 'failed',
                'recipient_number' => null,
                'username' => null,
                'temporary_password' => null,
                'failure_reason' => $exception instanceof WhatsAppRateLimitException
                    ? 'Provider rate limit retries were exhausted.'
                    : 'Credential delivery job failed before provider acceptance could be confirmed.',
            ])->save();
        }
    }
}
