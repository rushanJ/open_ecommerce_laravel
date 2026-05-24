<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = 'MEK-TEST-'.now()->format('Ymd').'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT);

        return [
            'order_number' => $number,
            'customer_id' => null,
            'customer_email' => $this->faker->unique()->safeEmail(),
            'customer_phone' => $this->faker->numerify('07########'),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'fulfillment_status' => 'unfulfilled',
            'currency_code' => 'LKR',
            'coupon_id' => null,
            'coupon_code' => null,
            'subtotal' => '100.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '100.0000',
            'paid_total' => '0.0000',
            'refunded_total' => '0.0000',
            'customer_note' => null,
            'admin_note' => null,
            'placed_at' => now(),
            'cancelled_at' => null,
            'metadata' => null,
        ];
    }

    public function forCustomer(?Customer $customer): static
    {
        return $this->state(function () use ($customer) {
            if ($customer === null) {
                return [];
            }

            return [
                'customer_id' => $customer->getKey(),
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
            ];
        });
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $grand = (string) ($attributes['grand_total'] ?? '0.0000');

            return [
                'payment_status' => 'paid',
                'paid_total' => $grand,
            ];
        });
    }
}
