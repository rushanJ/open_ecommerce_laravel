<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()->forStorefront()->get();
        $categories = Category::query()->get();
        $customers = Customer::query()->get();

        DB::transaction(function () use ($products, $categories, $customers): void {
            $welcome = Coupon::query()->updateOrCreate(
                ['code' => 'WELCOME10'],
                [
                    'name' => 'Welcome 10%',
                    'description' => '10% off for new customers.',
                    'type' => 'percentage',
                    'value' => 10,
                    'minimum_order_amount' => 5000,
                    'maximum_discount_amount' => 3000,
                    'usage_limit' => null,
                    'usage_limit_per_customer' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
            $welcome->products()->sync([]);
            $welcome->categories()->sync([]);
            $welcome->customers()->sync([]);

            $freeShip = Coupon::query()->updateOrCreate(
                ['code' => 'FREESHIP25'],
                [
                    'name' => 'Free Shipping 25k+',
                    'description' => 'Free shipping for orders above 25,000.',
                    'type' => 'free_shipping',
                    'value' => 0,
                    'minimum_order_amount' => 25000,
                    'maximum_discount_amount' => null,
                    'usage_limit' => null,
                    'usage_limit_per_customer' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
            $freeShip->products()->sync([]);
            $freeShip->categories()->sync([]);
            $freeShip->customers()->sync([]);

            // Electronics category by name/slug fallback
            $electronics = $categories->first(fn ($c) => str_contains(mb_strtolower($c->name), 'elect'))
                ?? $categories->first(fn ($c) => ($c->slug ?? null) === 'electronics');

            $electro = Coupon::query()->updateOrCreate(
                ['code' => 'ELECTRO1500'],
                [
                    'name' => 'Electronics 1500 off',
                    'description' => '1500 off eligible electronics items.',
                    'type' => 'fixed_product',
                    'value' => 1500,
                    'minimum_order_amount' => null,
                    'maximum_discount_amount' => null,
                    'usage_limit' => null,
                    'usage_limit_per_customer' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
            $electro->products()->sync([]);
            $electro->customers()->sync([]);
            $electro->categories()->sync($electronics ? [$electronics->getKey()] : []);

            $iphoneProducts = $products->filter(fn ($p) => str_contains(mb_strtolower($p->name), 'iphone'))->take(10);
            $iphone = Coupon::query()->updateOrCreate(
                ['code' => 'IPHONE5'],
                [
                    'name' => 'iPhone 5%',
                    'description' => '5% off selected iPhone products.',
                    'type' => 'percentage',
                    'value' => 5,
                    'minimum_order_amount' => null,
                    'maximum_discount_amount' => null,
                    'usage_limit' => null,
                    'usage_limit_per_customer' => null,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
            $iphone->categories()->sync([]);
            $iphone->customers()->sync([]);
            $iphone->products()->sync($iphoneProducts->pluck('id')->all());

            $vipCustomer = $customers->firstWhere('email', 'customer@open-ecommerce-laravel.test')
                ?? $customers->firstWhere('email', 'demo.customer@open-ecommerce-laravel.test')
                ?? $customers->first();

            $vip = Coupon::query()->updateOrCreate(
                ['code' => 'VIP20'],
                [
                    'name' => 'VIP 20%',
                    'description' => '20% off for VIP customers.',
                    'type' => 'percentage',
                    'value' => 20,
                    'minimum_order_amount' => null,
                    'maximum_discount_amount' => null,
                    'usage_limit' => null,
                    'usage_limit_per_customer' => 2,
                    'starts_at' => null,
                    'ends_at' => null,
                    'status' => 'active',
                ]
            );
            $vip->products()->sync([]);
            $vip->categories()->sync([]);
            $vip->customers()->sync($vipCustomer ? [$vipCustomer->getKey()] : []);
        });
    }
}

