<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppCredentialDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request, WhatsAppCredentialDeliveryService $deliveryService): Response
    {
        $verifyToken = config('services.whatsapp_cloud.webhook_verify_token');

        abort_unless($deliveryService->webhookIsConfigured(), 503);

        abort_unless(
            $request->query('hub.mode') === 'subscribe'
                && hash_equals((string) $verifyToken, (string) $request->query('hub.verify_token')),
            403
        );

        return response((string) $request->query('hub.challenge'), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function receive(Request $request, WhatsAppCredentialDeliveryService $deliveryService): SymfonyResponse
    {
        $appSecret = config('services.whatsapp_cloud.app_secret');
        $signature = $request->header('X-Hub-Signature-256');

        abort_unless($deliveryService->webhookIsConfigured(), 503);

        $expectedSignature = 'sha256='.hash_hmac('sha256', $request->getContent(), (string) $appSecret);
        abort_unless(is_string($signature) && hash_equals($expectedSignature, $signature), 403);

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                foreach ($change['value']['statuses'] ?? [] as $status) {
                    if (isset($status['id'], $status['status'])) {
                        $deliveryService->updateProviderStatus(
                            (string) $status['id'],
                            (string) $status['status']
                        );
                    }
                }
            }
        }

        return response()->noContent();
    }
}
