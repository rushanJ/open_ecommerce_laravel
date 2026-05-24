<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = strtoupper($this->faker->unique()->bothify('COUPON###'));

        return [
            'code' => $code,
            'name' => 'Coupon '.$code,
            'description' => null,
            'type' => 'percentage',
            'value' => '10.0000',
            'minimum_order_amount' => null,
            'maximum_discount_amount' => null,
            'usage_limit' => null,
            'usage_limit_per_customer' => null,
            'used_count' => 0,
            'starts_at' => null,
            'ends_at' => null,
            'status' => 'active',
        ];
    }

    public function percentage(float $percent): static
    {
        return $this->state(fn () => [
            'type' => 'percentage',
            'value' => number_format($percent, 4, '.', ''),
        ]);
    }

    public function fixedCart(float $amount): static
    {
        return $this->state(fn () => [
            'type' => 'fixed_cart',
            'value' => number_format($amount, 4, '.', ''),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'ends_at' => now()->subDay(),
        ]);
    }
}
