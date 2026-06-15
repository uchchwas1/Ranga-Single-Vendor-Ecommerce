<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an upsert of SEO metadata for a model (admin).
 */
class UpsertSeoMetaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('seo.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'model_type' => ['required', 'string', 'in:product,category,brand,page,blog_post'],
            'model_id' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'schema_markup' => ['nullable', 'array'],
            'canonical_url' => ['nullable', 'string', 'max:2048'],
        ];
    }
}
