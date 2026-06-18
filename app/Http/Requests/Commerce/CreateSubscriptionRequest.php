<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use App\Support\Enums\SubscriptionInterval;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates creation of a product subscription.
 */
class CreateSubscriptionRequest extends FormRequest
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
            'product_id' => ['required', 'string', 'exists:products,id'],
            'variant_id' => ['nullable', 'string', 'exists:product_variants,id'],
            'interval' => ['required', new Enum(SubscriptionInterval::class)],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
