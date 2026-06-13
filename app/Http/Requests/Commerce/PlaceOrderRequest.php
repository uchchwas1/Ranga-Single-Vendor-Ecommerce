<?php

declare(strict_types=1);

namespace App\Http\Requests\Commerce;

use App\Support\Enums\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates a checkout / order placement request.
 */
class PlaceOrderRequest extends FormRequest
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
        $implemented = array_values(array_filter(
            array_map(static fn (PaymentGateway $g): string => $g->value, PaymentGateway::cases()),
            static fn (string $code): bool => PaymentGateway::from($code)->isImplemented(),
        ));

        return [
            'shipping' => ['required', 'array'],
            'shipping.name' => ['required', 'string', 'max:255'],
            'shipping.phone' => ['required', 'string', 'max:20'],
            'shipping.address_line_1' => ['required', 'string', 'max:255'],
            'shipping.address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping.city' => ['required', 'string', 'max:100'],
            'shipping.state' => ['nullable', 'string', 'max:100'],
            'shipping.postal_code' => ['nullable', 'string', 'max:20'],
            'shipping.country_code' => ['nullable', 'string', 'size:2'],

            'billing' => ['nullable', 'array'],
            'billing.name' => ['nullable', 'string', 'max:255'],
            'billing.phone' => ['nullable', 'string', 'max:20'],
            'billing.address_line_1' => ['nullable', 'string', 'max:255'],
            'billing.city' => ['nullable', 'string', 'max:100'],

            'shipping_method' => ['required', 'string', 'exists:shipping_methods,code'],
            'payment_gateway' => ['required', 'string', Rule::in($implemented)],
            'guest_email' => ['nullable', 'email'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
