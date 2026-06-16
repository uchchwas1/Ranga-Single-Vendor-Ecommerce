<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a web-push subscription registration.
 */
class StorePushSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'string', 'max:1024'],
            'p256dh' => ['nullable', 'string', 'max:255'],
            'auth' => ['nullable', 'string', 'max:255'],
            'device_type' => ['nullable', 'string', 'max:30'],
        ];
    }
}
