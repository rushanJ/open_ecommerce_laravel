<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        $zoneAll = ShippingZone::query()->updateOrCreate(
            ['name' => 'All Sri Lanka', 'country_code' => 'LK', 'province' => null, 'district' => null, 'city' => null],
            ['status' => 'active']
        );

        $zoneColombo = ShippingZone::query()->updateOrCreate(
            ['name' => 'Colombo District', 'country_code' => 'LK', 'province' => null, 'district' => 'Colombo', 'city' => null],
            ['status' => 'active']
        );

        $zoneWestern = ShippingZone::query()->updateOrCreate(
            ['name' => 'Western Province', 'country_code' => 'LK', 'province' => 'Western', 'district' => null, 'city' => null],
            ['status' => 'active']
        );

        $standard = ShippingMethod::query()->updateOrCreate(
            ['code' => 'standard_delivery'],
            [
                'name' => 'Standard Delivery',
                'type' => 'flat_rate',
                'status' => 'active',
                'config' => null,
            ]
        );

        $express = ShippingMethod::query()->updateOrCreate(
            ['code' => 'express_delivery'],
            [
                'name' => 'Express Delivery',
                'type' => 'flat_rate',
                'status' => 'active',
                'config' => null,
            ]
        );

        $free = ShippingMethod::query()->updateOrCreate(
            ['code' => 'free_shipping'],
            [
                'name' => 'Free Shipping',
                'type' => 'free_shipping',
                'status' => 'active',
                'config' => null,
            ]
        );

        $this->rate($zoneAll, $standard, '500', null, null);
        $this->rate($zoneAll, $express, '900', null, null);
        $this->rate($zoneWestern, $standard, '350', null, null);
        $this->rate($zoneColombo, $express, '600', null, null);
        $this->rate($zoneAll, $free, '0', '25000', null);
    }

    private function rate(ShippingZone $zone, ShippingMethod $method, string $amount, ?string $minOrder, ?string $maxOrder): void
    {
        ShippingRate::query()->updateOrCreate(
            [
                'shipping_zone_id' => $zone->getKey(),
                'shipping_method_id' => $method->getKey(),
            ],
            [
                'min_order_amount' => $minOrder,
                'max_order_amount' => $maxOrder,
                'min_weight' => null,
                'max_weight' => null,
                'rate' => $amount,
                'status' => 'active',
            ]
        );
    }
}
