<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalogue;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates catalogue listing filters supplied as query parameters.
 */
class ProductIndexRequest extends FormRequest
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
            'category' => ['sometimes', 'string', 'exists:categories,id'],
            'brand' => ['sometimes', 'string', 'exists:brands,id'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],
            'rating' => ['sometimes', 'integer', 'between:1,5'],
            'featured' => ['sometimes', 'boolean'],
            'attribute_values' => ['sometimes', 'array'],
            'attribute_values.*' => ['string', 'exists:attribute_values,id'],
            'sort' => ['sometimes', 'string', 'in:latest,price_asc,price_desc,name,featured'],
            'per_page' => ['sometimes', 'integer', 'between:1,60'],
        ];
    }
}
