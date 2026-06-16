<?php

declare(strict_types=1);

namespace App\Notifications\Senders;

/**
 * Provider-agnostic WhatsApp transport.
 */
interface WhatsAppSender
{
    /**
     * Send a templated WhatsApp message.
     *
     * @param  array<string, mixed>  $variables
     */
    public function send(string $to, string $template, array $variables = []): void;
}
