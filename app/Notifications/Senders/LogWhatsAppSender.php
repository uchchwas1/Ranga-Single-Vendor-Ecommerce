<?php

declare(strict_types=1);

namespace App\Notifications\Senders;

use App\Models\WhatsAppLog;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

/**
 * Default WhatsApp sender: records to whatsapp_logs and the app log.
 */
class LogWhatsAppSender implements WhatsAppSender
{
    /**
     * Send a templated WhatsApp message.
     *
     * @param  array<string, mixed>  $variables
     */
    public function send(string $to, string $template, array $variables = []): void
    {
        WhatsAppLog::query()->create([
            'to' => $to,
            'template' => $template,
            'variables' => $variables,
            'status' => 'sent',
            'sent_at' => Date::now(),
        ]);

        Log::info('WhatsApp dispatched', ['to' => $to, 'template' => $template]);
    }
}
