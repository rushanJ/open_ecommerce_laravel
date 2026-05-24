<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->lexify('pm_????');

        return [
            'code' => $code,
            'name' => strtoupper($code).' Method',
            'provider' => 'manual',
            'status' => 'active',
            'config' => null,
        ];
    }

    public function payHere(): static
    {
        return $this->state(fn () => [
            'code' => 'payhere',
            'name' => 'PayHere',
            'provider' => 'payhere',
            'status' => 'active',
            'config' => [
                'enabled' => true,
                'mode' => 'sandbox',
            ],
        ]);
    }
}
