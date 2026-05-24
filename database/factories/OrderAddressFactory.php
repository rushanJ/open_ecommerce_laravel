<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderAddress>
 */
class OrderAddressFactory extends Factory
{
    protected $model = OrderAddress::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => 'billing',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->numerify('07########'),
            'email' => $this->faker->safeEmail(),
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => null,
            'city' => 'Colombo',
            'district' => 'Colombo',
            'province' => 'Western',
            'postal_code' => null,
            'country_code' => 'LK',
        ];
    }

    public function forOrder(Order $order, string $type = 'billing'): static
    {
        return $this->state(fn () => [
            'order_id' => $order->getKey(),
            'type' => $type,
        ]);
    }
}
