<?php

namespace App\Exceptions;

use RuntimeException;

class WhatsAppRateLimitException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('WhatsApp provider rate limit response; safe retry scheduled.');
    }
}
