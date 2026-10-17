<?php

use App\Models\WhatsAppCredentialDelivery;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(static function (): void {
    WhatsAppCredentialDelivery::query()
        ->where('status', 'pending')
        ->where('expires_at', '<=', now())
        ->update([
            'status' => 'expired',
            'recipient_number' => null,
            'username' => null,
            'temporary_password' => null,
            'failure_reason' => 'Credential delivery expired before sending.',
            'updated_at' => now(),
        ]);

    WhatsAppCredentialDelivery::query()
        ->where('status', 'sending')
        ->where('expires_at', '<=', now())
        ->update([
            'status' => 'failed',
            'recipient_number' => null,
            'username' => null,
            'temporary_password' => null,
            'failure_reason' => 'Delivery outcome could not be confirmed before credentials expired.',
            'updated_at' => now(),
        ]);
})->everyFiveMinutes()->name('expire-whatsapp-credential-secrets')->withoutOverlapping();
