<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            StoreSeeder::class,
            SystemSettingSeeder::class,
            EmailTemplateSeeder::class,
            PaymentMethodSeeder::class,
            ShippingSeeder::class,
            AdminRoleSeeder::class,
            AdminPermissionSeeder::class,
            AdminUserSeeder::class,
            CustomerGroupSeeder::class,
            TaxSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductAttributeSeeder::class,
            ProductCatalogSeeder::class,
            InventorySeeder::class,
            CmsSeeder::class,
            SeoSeeder::class,
        ]);
    }
}
