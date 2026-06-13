<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalogue;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a full-text catalogue search request.
 */
class SearchRequest extends FormRequest
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
            'q' => ['required', 'string', 'min:1', 'max:150'],
            'category' => ['sometimes', 'string', 'exists:categories,id'],
            'brand' => ['sometimes', 'string', 'exists:brands,id'],
            'per_page' => ['sometimes', 'integer', 'between:1,60'],
        ];
    }
}
