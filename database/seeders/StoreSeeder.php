<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        if (Store::query()->exists()) {
            return;
        }

        Store::query()->create([
            'name' => 'open_ecommerce_laravel',
            'legal_name' => 'open_ecommerce_laravel',
            'domain' => 'open-ecommerce-laravel.test',
            'email' => 'admin@open-ecommerce-laravel.test',
            'phone' => null,
            'logo_path' => null,
            'favicon_path' => null,
            'currency_code' => 'LKR',
            'timezone' => 'Asia/Colombo',
            'address_line_1' => null,
            'address_line_2' => null,
            'city' => null,
            'district' => null,
            'province' => null,
            'postal_code' => null,
            'country_code' => 'LK',
            'status' => 'active',
            'metadata' => null,
        ]);
    }
}
