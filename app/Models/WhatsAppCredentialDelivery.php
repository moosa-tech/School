<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppCredentialDelivery extends Model
{
    protected $table = 'whatsapp_credential_deliveries';

    protected $fillable = [
        'school_id',
        'user_id',
        'recipient_number',
        'username',
        'temporary_password',
        'status',
        'attempts',
        'provider_message_id',
        'failure_reason',
        'sent_at',
        'delivered_at',
        'expires_at',
    ];

    protected $hidden = [
        'recipient_number',
        'username',
        'temporary_password',
    ];

    protected function casts(): array
    {
        return [
            'recipient_number' => 'encrypted',
            'username' => 'encrypted',
            'temporary_password' => 'encrypted',
            'attempts' => 'integer',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
