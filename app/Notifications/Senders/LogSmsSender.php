<?php

declare(strict_types=1);

namespace App\Notifications\Senders;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

/**
 * Default SMS sender: records the message to sms_logs and the app log.
 * Swap the binding for a real gateway (Twilio, Vonage, etc.) in production.
 */
class LogSmsSender implements SmsSender
{
    /**
     * Send an SMS to a phone number.
     */
    public function send(string $to, string $message): void
    {
        SmsLog::query()->create([
            'to' => $to,
            'message' => $message,
            'provider' => 'log',
            'status' => 'sent',
            'sent_at' => Date::now(),
        ]);

        Log::info('SMS dispatched', ['to' => $to]);
    }
}
