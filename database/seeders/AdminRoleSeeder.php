<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access.', 'is_system' => true],
            ['name' => 'Store Manager', 'slug' => 'store-manager', 'description' => 'Store operations, catalog, and orders.', 'is_system' => true],
            ['name' => 'Order Manager', 'slug' => 'order-manager', 'description' => 'Orders, payments, and customer-facing fulfilment.', 'is_system' => true],
            ['name' => 'Catalog Manager', 'slug' => 'catalog-manager', 'description' => 'Products, categories, brands, and reviews.', 'is_system' => true],
            ['name' => 'Support Agent', 'slug' => 'support-agent', 'description' => 'Limited read access for support.', 'is_system' => true],
        ];

        DB::transaction(function () use ($roles, $now): void {
            foreach ($roles as $role) {
                $slug = $role['slug'];

                $payload = [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'is_system' => $role['is_system'],
                    'updated_at' => $now,
                ];

                if (DB::table('admin_roles')->where('slug', $slug)->exists()) {
                    DB::table('admin_roles')->where('slug', $slug)->update($payload);
                } else {
                    DB::table('admin_roles')->insert(array_merge($payload, [
                        'slug' => $slug,
                        'created_at' => $now,
                    ]));
                }
            }
        });
    }
}
