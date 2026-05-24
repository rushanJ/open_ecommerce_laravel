<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ship_to_different_address' => $this->boolean('ship_to_different_address'),
            'billing_country_code' => $this->input('billing_country_code', 'LK'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $shipDiff = Rule::requiredIf(fn () => $this->boolean('ship_to_different_address'));

        return [
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'billing_first_name' => ['required', 'string', 'max:255'],
            'billing_last_name' => ['nullable', 'string', 'max:255'],
            'billing_phone' => ['nullable', 'string', 'max:50'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'billing_address_line_1' => ['required', 'string', 'max:255'],
            'billing_address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['required', 'string', 'max:100'],
            'billing_district' => ['nullable', 'string', 'max:100'],
            'billing_province' => ['nullable', 'string', 'max:100'],
            'billing_postal_code' => ['nullable', 'string', 'max:20'],
            'billing_country_code' => ['required', 'string', 'max:5'],

            'ship_to_different_address' => ['boolean'],

            'shipping_first_name' => [$shipDiff, 'nullable', 'string', 'max:255'],
            'shipping_last_name' => ['nullable', 'string', 'max:255'],
            'shipping_phone' => ['nullable', 'string', 'max:50'],
            'shipping_email' => ['nullable', 'email', 'max:255'],
            'shipping_address_line_1' => [$shipDiff, 'nullable', 'string', 'max:255'],
            'shipping_address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => [$shipDiff, 'nullable', 'string', 'max:100'],
            'shipping_district' => ['nullable', 'string', 'max:100'],
            'shipping_province' => ['nullable', 'string', 'max:100'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_country_code' => ['nullable', 'string', 'max:5'],

            'shipping_rate_id' => ['required', 'integer', Rule::exists('shipping_rates', 'id')->where('status', 'active')],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
