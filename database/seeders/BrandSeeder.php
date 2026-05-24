<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Apple', 'slug' => 'apple', 'sort_order' => 1, 'description' => 'Consumer electronics and software.'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'sort_order' => 2, 'description' => 'Electronics and appliances.'],
            ['name' => 'Sony', 'slug' => 'sony', 'sort_order' => 3, 'description' => 'Audio, imaging, and entertainment.'],
            ['name' => 'Nike', 'slug' => 'nike', 'sort_order' => 4, 'description' => 'Athletic footwear and apparel.'],
            ['name' => 'Adidas', 'slug' => 'adidas', 'sort_order' => 5, 'description' => 'Sportswear and footwear.'],
        ];

        foreach ($rows as $row) {
            Brand::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'logo_path' => null,
                    'status' => 'active',
                    'sort_order' => $row['sort_order'],
                ]
            );
        }
    }
}
