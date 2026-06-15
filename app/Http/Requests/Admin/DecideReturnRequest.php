<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Support\Enums\RefundMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates an admin decision (approve/reject) on a return request.
 */
class DecideReturnRequest extends FormRequest
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
            'refund_method' => ['nullable', new Enum(RefundMethod::class)],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
