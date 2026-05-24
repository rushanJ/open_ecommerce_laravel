<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Color',
                'slug' => 'color',
                'sort_order' => 1,
                'values' => ['Black', 'White', 'Blue', 'Red'],
            ],
            [
                'name' => 'Size',
                'slug' => 'size',
                'sort_order' => 2,
                'values' => ['S', 'M', 'L', 'XL'],
            ],
            [
                'name' => 'Storage',
                'slug' => 'storage',
                'sort_order' => 3,
                'values' => ['128GB', '256GB', '512GB'],
            ],
            [
                'name' => 'Shoe Size',
                'slug' => 'shoe-size',
                'sort_order' => 4,
                'values' => ['8', '9', '10', '11'],
            ],
        ];

        foreach ($definitions as $def) {
            $attr = ProductAttribute::query()->updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'type' => 'select',
                    'is_filterable' => true,
                    'sort_order' => $def['sort_order'],
                ]
            );

            foreach ($def['values'] as $order => $label) {
                $slug = Str::slug($label) ?: (string) $label;
                ProductAttributeValue::query()->updateOrCreate(
                    [
                        'attribute_id' => $attr->getKey(),
                        'slug' => $slug,
                    ],
                    [
                        'value' => $label,
                        'sort_order' => $order,
                        'color_code' => null,
                        'image_path' => null,
                    ]
                );
            }
        }
    }
}
