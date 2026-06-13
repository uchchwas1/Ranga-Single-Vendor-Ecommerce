<?php

declare(strict_types=1);

namespace App\Http\Requests\Catalogue;

use App\Models\Product;
use App\Support\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates creation of a catalogue product (admin).
 */
class CreateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', Product::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'string', 'exists:categories,id'],
            'brand_id' => ['nullable', 'string', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')],
            'barcode' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'specifications' => ['nullable', 'array'],
            'faqs' => ['nullable', 'array'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'status' => ['required', new Enum(ProductStatus::class)],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_digital' => ['sometimes', 'boolean'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['sometimes', 'string', 'max:10'],
            'dimensions' => ['nullable', 'array'],
            'video_url' => ['nullable', 'url'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
