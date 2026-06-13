<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates new account registration.
 */
class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:'.(string) config('ranga.validation.phone_regex'), 'unique:users,phone'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'locale' => ['sometimes', 'string', 'in:bn,en'],
            'referral_code' => ['nullable', 'string', 'max:12'],
        ];
    }
}
