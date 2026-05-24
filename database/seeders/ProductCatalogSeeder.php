<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\TaxClass;
use App\Services\ProductService;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (! Category::query()->where('slug', 'smartphones')->exists()) {
            $this->call(CategorySeeder::class);
        }
        if (! Brand::query()->exists()) {
            $this->call(BrandSeeder::class);
        }
        if (! ProductAttribute::query()->exists()) {
            $this->call(ProductAttributeSeeder::class);
        }

        /** @var ProductService $service */
        $service = app(ProductService::class);

        $taxId = TaxClass::query()->where('slug', 'standard')->value('id');

        $apple = Brand::query()->where('slug', 'apple')->firstOrFail();
        $samsung = Brand::query()->where('slug', 'samsung')->firstOrFail();
        $sony = Brand::query()->where('slug', 'sony')->firstOrFail();
        $nike = Brand::query()->where('slug', 'nike')->firstOrFail();
        $adidas = Brand::query()->where('slug', 'adidas')->firstOrFail();

        $smartphones = Category::query()->where('slug', 'smartphones')->firstOrFail()->getKey();
        $audio = Category::query()->where('slug', 'audio')->firstOrFail()->getKey();
        $shoes = Category::query()->where('slug', 'shoes')->firstOrFail()->getKey();
        $tshirts = Category::query()->where('slug', 't-shirts')->firstOrFail()->getKey();

        $now = now();

        $published = $now->copy();

        $this->upsertProduct($service, [
            'brand_id' => $apple->getKey(),
            'product_type' => 'variable',
            'name' => 'Apple iPhone 15',
            'slug' => 'apple-iphone-15',
            'sku' => null,
            'barcode' => null,
            'short_description' => 'Latest Apple smartphone with advanced camera and performance.',
            'description' => 'Apple iPhone 15 in multiple storage and color options.',
            'status' => 'active',
            'visibility' => 'visible',
            'is_featured' => true,
            'regular_price' => 324990,
            'sale_price' => null,
            'cost_price' => 250000,
            'tax_class_id' => $taxId,
            'manage_stock' => false,
            'stock_quantity' => null,
            'low_stock_threshold' => null,
            'stock_status' => 'in_stock',
            'backorders_allowed' => false,
            'weight' => 0.2010,
            'length' => null,
            'width' => null,
            'height' => null,
            'digital_file_path' => null,
            'meta_title' => 'Apple iPhone 15',
            'meta_description' => 'Buy Apple iPhone 15 online. Multiple storage and colors.',
            'published_at' => $published,
            'category_ids' => [$smartphones],
            'attributes' => $this->attributesFormPayload(['color' => 'black', 'storage' => '128gb']),
            'images' => [
                ['path' => 'storage/catalog/apple-iphone-15-front.jpg', 'alt_text' => 'Apple iPhone 15 front', 'sort_order' => 0, 'is_primary' => true],
                ['path' => 'storage/catalog/apple-iphone-15-back.jpg', 'alt_text' => 'Apple iPhone 15 back', 'sort_order' => 1, 'is_primary' => false],
            ],
            'variants' => [
                array_merge($this->variantShell(324990, 30, 'iPhone 15 Black 128GB', 'IP15-BLK-128'), [
                    'attribute_values' => [
                        $this->pivotPair('color', 'black'),
                        $this->pivotPair('storage', '128gb'),
                    ],
                ]),
                array_merge($this->variantShell(359990, 22, 'iPhone 15 Blue 256GB', 'IP15-BLU-256'), [
                    'attribute_values' => [
                        $this->pivotPair('color', 'blue'),
                        $this->pivotPair('storage', '256gb'),
                    ],
                ]),
            ],
        ]);

        $this->upsertProduct($service, [
            'brand_id' => $samsung->getKey(),
            'product_type' => 'variable',
            'name' => 'Samsung Galaxy S24',
            'slug' => 'samsung-galaxy-s24',
            'sku' => null,
            'barcode' => null,
            'short_description' => 'Flagship Android smartphone with Galaxy AI features.',
            'description' => 'Samsung Galaxy S24 flagship smartphone line.',
            'status' => 'active',
            'visibility' => 'visible',
            'is_featured' => true,
            'regular_price' => 289990,
            'sale_price' => 269990,
            'cost_price' => 220000,
            'tax_class_id' => $taxId,
            'manage_stock' => false,
            'stock_quantity' => null,
            'low_stock_threshold' => null,
            'stock_status' => 'in_stock',
            'backorders_allowed' => false,
            'weight' => 0.1670,
            'length' => null,
            'width' => null,
            'height' => null,
            'digital_file_path' => null,
            'meta_title' => 'Samsung Galaxy S24',
            'meta_description' => 'Samsung Galaxy S24 with vivid display and pro-grade camera.',
            'published_at' => $published,
            'category_ids' => [$smartphones],
            'attributes' => $this->attributesFormPayload(['color' => 'black', 'storage' => '128gb']),
            'images' => [
                ['path' => 'storage/catalog/samsung-galaxy-s24-1.jpg', 'alt_text' => 'Samsung Galaxy S24', 'sort_order' => 0, 'is_primary' => true],
                ['path' => 'storage/catalog/samsung-galaxy-s24-2.jpg', 'alt_text' => 'Galaxy S24 profile', 'sort_order' => 1, 'is_primary' => false],
            ],
            'variants' => [
                array_merge($this->variantShell(289990, 28, 'Galaxy S24 Black 128GB', 'SGS24-BLK-128'), [
                    'sale_price' => 269990,
                    'attribute_values' => [
                        $this->pivotPair('color', 'black'),
                        $this->pivotPair('storage', '128gb'),
                    ],
                ]),
                array_merge($this->variantShell(314990, 18, 'Galaxy S24 White 256GB', 'SGS24-WHT-256'), [
                    'sale_price' => 294990,
                    'attribute_values' => [
                        $this->pivotPair('color', 'white'),
                        $this->pivotPair('storage', '256gb'),
                    ],
                ]),
            ],
        ]);

        $this->upsertProduct($service, [
            'brand_id' => $sony->getKey(),
            'product_type' => 'simple',
            'name' => 'Sony WH-1000XM5 Headphones',
            'slug' => 'sony-wh-1000xm5-headphones',
            'sku' => 'SONY-WH1000XM5-BLK',
            'barcode' => '4548736148790',
            'short_description' => 'Industry-leading noise cancelling wireless headphones.',
            'description' => 'Sony WH-1000XM5 with premium sound and all-day comfort.',
            'status' => 'active',
            'visibility' => 'visible',
            'is_featured' => false,
            'regular_price' => 89990,
            'sale_price' => null,
            'cost_price' => 62000,
            'tax_class_id' => $taxId,
            'manage_stock' => true,
            'stock_quantity' => 45,
            'low_stock_threshold' => 5,
            'stock_status' => 'in_stock',
            'backorders_allowed' => false,
            'weight' => 0.2500,
            'length' => null,
            'width' => null,
            'height' => null,
            'digital_file_path' => null,
            'meta_title' => 'Sony WH-1000XM5',
            'meta_description' => 'Wireless noise cancelling headphones by Sony.',
            'published_at' => $published,
            'category_ids' => [$audio],
            'attributes' => $this->attributesFormPayload(['color' => 'black']),
            'images' => [
                ['path' => 'storage/catalog/sony-wh1000xm5-1.jpg', 'alt_text' => 'Sony WH-1000XM5', 'sort_order' => 0, 'is_primary' => true],
            ],
            'variants' => [],
        ]);

        $this->upsertProduct($service, [
            'brand_id' => $nike->getKey(),
            'product_type' => 'variable',
            'name' => 'Nike Air Max',
            'slug' => 'nike-air-max',
            'sku' => null,
            'barcode' => null,
            'short_description' => 'Comfortable running shoes with visible Air cushioning.',
            'description' => 'Nike Air Max line — breathable upper and durable sole.',
            'status' => 'active',
            'visibility' => 'visible',
            'is_featured' => true,
            'regular_price' => 45990,
            'sale_price' => null,
            'cost_price' => 28000,
            'tax_class_id' => $taxId,
            'manage_stock' => false,
            'stock_quantity' => null,
            'low_stock_threshold' => null,
            'stock_status' => 'in_stock',
            'backorders_allowed' => false,
            'weight' => 0.8500,
            'length' => null,
            'width' => null,
            'height' => null,
            'digital_file_path' => null,
            'meta_title' => 'Nike Air Max',
            'meta_description' => 'Nike Air Max shoes in multiple sizes and colors.',
            'published_at' => $published,
            'category_ids' => [$shoes],
            'attributes' => $this->attributesFormPayload(['color' => 'black', 'shoe-size' => '9']),
            'images' => [
                ['path' => 'storage/catalog/nike-air-max-black.jpg', 'alt_text' => 'Nike Air Max Black', 'sort_order' => 0, 'is_primary' => true],
                ['path' => 'storage/catalog/nike-air-max-white.jpg', 'alt_text' => 'Nike Air Max White', 'sort_order' => 1, 'is_primary' => false],
            ],
            'variants' => [
                array_merge($this->variantShell(45990, 14, 'Nike Air Max Black Size 9', 'NK-AM-B9'), [
                    'attribute_values' => [
                        $this->pivotPair('color', 'black'),
                        $this->pivotPair('shoe-size', '9'),
                    ],
                ]),
                array_merge($this->variantShell(45990, 10, 'Nike Air Max White Size 10', 'NK-AM-W10'), [
                    'attribute_values' => [
                        $this->pivotPair('color', 'white'),
                        $this->pivotPair('shoe-size', '10'),
                    ],
                ]),
            ],
        ]);

        $this->upsertProduct($service, [
            'brand_id' => $adidas->getKey(),
            'product_type' => 'variable',
            'name' => 'Adidas Basic T-Shirt',
            'slug' => 'adidas-basic-t-shirt',
            'sku' => null,
            'barcode' => null,
            'short_description' => 'Soft cotton everyday tee.',
            'description' => 'Adidas basic cotton t-shirt — essential fit.',
            'status' => 'active',
            'visibility' => 'visible',
            'is_featured' => true,
            'regular_price' => 3490,
            'sale_price' => null,
            'cost_price' => 1500,
            'tax_class_id' => $taxId,
            'manage_stock' => false,
            'stock_quantity' => null,
            'low_stock_threshold' => null,
            'stock_status' => 'in_stock',
            'backorders_allowed' => false,
            'weight' => 0.2000,
            'length' => null,
            'width' => null,
            'height' => null,
            'digital_file_path' => null,
            'meta_title' => 'Adidas Basic T-Shirt',
            'meta_description' => 'Adidas basic tee in multiple sizes and colors.',
            'published_at' => $published,
            'category_ids' => [$tshirts],
            'attributes' => $this->attributesFormPayload(['color' => 'red', 'size' => 'm']),
            'images' => [
                ['path' => 'storage/catalog/adidas-tee-red.jpg', 'alt_text' => 'Adidas T-Shirt Red', 'sort_order' => 0, 'is_primary' => true],
                ['path' => 'storage/catalog/adidas-tee-black.jpg', 'alt_text' => 'Adidas T-Shirt Black', 'sort_order' => 1, 'is_primary' => false],
            ],
            'variants' => [
                array_merge($this->variantShell(3490, 120, 'Adidas Tee Red M', 'AD-TS-R-M'), [
                    'attribute_values' => [
                        $this->pivotPair('color', 'red'),
                        $this->pivotPair('size', 'm'),
                    ],
                ]),
                array_merge($this->variantShell(3490, 80, 'Adidas Tee Black L', 'AD-TS-B-L'), [
                    'attribute_values' => [
                        $this->pivotPair('color', 'black'),
                        $this->pivotPair('size', 'l'),
                    ],
                ]),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function upsertProduct(ProductService $service, array $data): void
    {
        $slug = $data['slug'];
        $product = Product::withTrashed()->where('slug', $slug)->first();
        if ($product !== null && $product->trashed()) {
            $product->restore();
        }
        if ($product !== null) {
            $service->updateProduct($product, $data);
        } else {
            $service->createProduct($data);
        }
    }

    /**
     * @param  array<string, string>  $mapBySlug  attribute slug => value slug
     * @return list<array{attribute_id: int, attribute_value_id: int|null, custom_value: null}>
     */
    private function attributesFormPayload(array $mapBySlug): array
    {
        $rows = [];
        $i = 0;
        foreach (ProductAttribute::query()->orderBy('sort_order')->orderBy('name')->get() as $attr) {
            $vid = null;
            if (isset($mapBySlug[$attr->slug])) {
                $vid = ProductAttributeValue::query()
                    ->where('attribute_id', $attr->getKey())
                    ->where('slug', $mapBySlug[$attr->slug])
                    ->value('id');
            }
            $rows[$i] = [
                'attribute_id' => (int) $attr->getKey(),
                'attribute_value_id' => $vid !== null ? (int) $vid : null,
                'custom_value' => null,
            ];
            $i++;
        }

        return $rows;
    }

    /**
     * @return array{sku: string|null, barcode: null, name: string, regular_price: float|int, sale_price: null, cost_price: null, stock_quantity: int, stock_status: string, weight: null, image_path: null, status: string}
     */
    private function variantShell(float|int $price, int $qty, string $name, ?string $sku): array
    {
        return [
            'sku' => $sku,
            'barcode' => null,
            'name' => $name,
            'regular_price' => $price,
            'sale_price' => null,
            'cost_price' => null,
            'stock_quantity' => $qty,
            'stock_status' => 'in_stock',
            'weight' => null,
            'image_path' => null,
            'status' => 'active',
        ];
    }

    /**
     * @return array{attribute_id: int, attribute_value_id: int}
     */
    private function pivotPair(string $attributeSlug, string $valueSlug): array
    {
        $attribute = ProductAttribute::query()->where('slug', $attributeSlug)->firstOrFail();
        $value = ProductAttributeValue::query()
            ->where('attribute_id', $attribute->getKey())
            ->where('slug', $valueSlug)
            ->firstOrFail();

        return [
            'attribute_id' => (int) $attribute->getKey(),
            'attribute_value_id' => (int) $value->getKey(),
        ];
    }
}
