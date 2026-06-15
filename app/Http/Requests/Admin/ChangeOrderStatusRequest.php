<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates an admin order status update.
 */
class ChangeOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('orders.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(OrderStatus::class)],
            'comment' => ['nullable', 'string', 'max:1000'],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }
}
