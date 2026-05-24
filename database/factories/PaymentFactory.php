<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'payment_reference' => 'PAY-TEST-'.$this->faker->unique()->numerify('######'),
            'provider_transaction_id' => null,
            'status' => 'initiated',
            'amount' => '1000.0000',
            'currency_code' => 'LKR',
            'gateway_response' => null,
            'paid_at' => null,
        ];
    }

    public function forOrder(Order $order): static
    {
        return $this->state(fn () => [
            'order_id' => $order->getKey(),
            'amount' => $order->grand_total,
            'currency_code' => $order->currency_code,
        ]);
    }
}
