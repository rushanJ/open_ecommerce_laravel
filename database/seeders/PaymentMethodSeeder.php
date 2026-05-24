<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::query()->updateOrCreate(
            ['code' => 'payhere'],
            [
                'name' => 'PayHere',
                'provider' => 'payhere',
                'status' => 'active',
                'config' => [
                    'enabled' => (bool) config('open_ecommerce_laravel.payments.payhere.enabled', false),
                    'mode' => config('open_ecommerce_laravel.payments.payhere.mode', 'sandbox'),
                ],
            ]
        );
    }
}

