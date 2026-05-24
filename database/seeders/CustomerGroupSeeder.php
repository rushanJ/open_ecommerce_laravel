<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $groups = [
            ['name' => 'General Customer', 'slug' => 'general-customer', 'description' => 'Default retail customers.'],
            ['name' => 'Wholesale Customer', 'slug' => 'wholesale-customer', 'description' => 'B2B / volume pricing tier.'],
            ['name' => 'VIP Customer', 'slug' => 'vip-customer', 'description' => 'Priority support and perks.'],
        ];

        DB::transaction(function () use ($groups, $now): void {
            foreach ($groups as $group) {
                $slug = $group['slug'];
                $payload = [
                    'name' => $group['name'],
                    'description' => $group['description'],
                    'updated_at' => $now,
                ];

                if (DB::table('customer_groups')->where('slug', $slug)->exists()) {
                    DB::table('customer_groups')->where('slug', $slug)->update($payload);
                } else {
                    DB::table('customer_groups')->insert(array_merge($payload, [
                        'slug' => $slug,
                        'created_at' => $now,
                    ]));
                }
            }
        });
    }
}
