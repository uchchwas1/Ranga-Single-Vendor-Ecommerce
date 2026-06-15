<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\Enums\PopupTrigger;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates creation of a marketing popup (admin).
 */
class CreatePopupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('cms.manage') ?? false;
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
            'content' => ['nullable', 'string'],
            'trigger_type' => ['required', new Enum(PopupTrigger::class)],
            'trigger_delay' => ['sometimes', 'integer', 'min:0'],
            'show_once' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
