<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a bulk settings upsert from the admin panel.
 */
class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array', 'min:1'],
            'settings.*.group' => ['required', 'string', 'max:50'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['present'],
            'settings.*.is_public' => ['sometimes', 'boolean'],
        ];
    }
}
