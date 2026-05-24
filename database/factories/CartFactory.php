<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => null,
            'session_id' => null,
            'status' => 'active',
            'currency_code' => 'LKR',
            'coupon_id' => null,
            'coupon_code' => null,
            'subtotal' => '0.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '0.0000',
            'expires_at' => now()->addDays(30),
        ];
    }

    public function guestSession(string $sessionId): static
    {
        return $this->state(fn () => [
            'customer_id' => null,
            'session_id' => $sessionId,
        ]);
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn () => [
            'customer_id' => $customer->getKey(),
            'session_id' => null,
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn () => [
            'status' => 'converted',
        ]);
    }
}
