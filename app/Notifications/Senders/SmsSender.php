<?php

declare(strict_types=1);

namespace App\Notifications\Senders;

/**
 * Provider-agnostic SMS transport.
 */
interface SmsSender
{
    /**
     * Send an SMS to a phone number.
     */
    public function send(string $to, string $message): void;
}
