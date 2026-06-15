<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates creation of a navigation menu with items (admin).
 */
class CreateMenuRequest extends FormRequest
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
            'location' => ['required', 'string', 'max:255', Rule::unique('menus', 'location')],
            'items' => ['sometimes', 'array'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.url' => ['nullable', 'string', 'max:2048'],
            'items.*.target' => ['sometimes', 'string', 'in:_self,_blank'],
            'items.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
