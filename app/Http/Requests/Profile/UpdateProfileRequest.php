<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates profile updates.
 */
class UpdateProfileRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'regex:'.(string) config('ranga.validation.phone_regex'),
                Rule::unique('users', 'phone')->ignore($this->user()?->getAuthIdentifier()),
            ],
            'locale' => ['sometimes', 'string', 'in:bn,en'],
            'timezone' => ['sometimes', 'string', 'timezone:all'],
        ];
    }
}
