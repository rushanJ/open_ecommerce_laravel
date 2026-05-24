<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $roots = [
                [
                    'slug' => 'electronics',
                    'name' => 'Electronics',
                    'description' => 'Devices, accessories, and consumer electronics.',
                    'sort_order' => 1,
                ],
                [
                    'slug' => 'fashion',
                    'name' => 'Fashion',
                    'description' => 'Clothing, footwear, and accessories.',
                    'sort_order' => 2,
                ],
                [
                    'slug' => 'home-living',
                    'name' => 'Home & Living',
                    'description' => 'Home essentials and lifestyle products.',
                    'sort_order' => 3,
                ],
            ];

            $children = [
                [
                    'parent_slug' => 'electronics',
                    'slug' => 'smartphones',
                    'name' => 'Smartphones',
                    'description' => null,
                    'sort_order' => 1,
                ],
                [
                    'parent_slug' => 'electronics',
                    'slug' => 'laptops',
                    'name' => 'Laptops',
                    'description' => null,
                    'sort_order' => 2,
                ],
                [
                    'parent_slug' => 'electronics',
                    'slug' => 'audio',
                    'name' => 'Audio',
                    'description' => null,
                    'sort_order' => 3,
                ],
                [
                    'parent_slug' => 'fashion',
                    'slug' => 'shoes',
                    'name' => 'Shoes',
                    'description' => null,
                    'sort_order' => 1,
                ],
                [
                    'parent_slug' => 'fashion',
                    'slug' => 't-shirts',
                    'name' => 'T-Shirts',
                    'description' => null,
                    'sort_order' => 2,
                ],
                [
                    'parent_slug' => 'home-living',
                    'slug' => 'kitchen',
                    'name' => 'Kitchen',
                    'description' => null,
                    'sort_order' => 1,
                ],
                [
                    'parent_slug' => 'home-living',
                    'slug' => 'furniture',
                    'name' => 'Furniture',
                    'description' => null,
                    'sort_order' => 2,
                ],
            ];

            $idsBySlug = [];

            foreach ($roots as $row) {
                $slug = $row['slug'];
                $idsBySlug[$slug] = $this->upsertCategory(null, $row);
            }

            foreach ($children as $row) {
                $parentSlug = $row['parent_slug'];
                unset($row['parent_slug']);
                $parentId = $idsBySlug[$parentSlug] ?? DB::table('categories')->where('slug', $parentSlug)->value('id');
                if ($parentId === null) {
                    continue;
                }
                $idsBySlug[$row['slug']] = $this->upsertCategory((int) $parentId, $row);
            }
        });
    }

    /**
     * @param  array{name: string, slug: string, description: ?string, sort_order: int}  $data
     */
    private function upsertCategory(?int $parentId, array $data): int
    {
        $now = now();
        $slug = $data['slug'];

        $payload = [
            'parent_id' => $parentId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image_path' => null,
            'banner_path' => null,
            'status' => 'active',
            'sort_order' => $data['sort_order'],
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'updated_at' => $now,
        ];

        if (DB::table('categories')->where('slug', $slug)->exists()) {
            DB::table('categories')->where('slug', $slug)->update($payload);
        } else {
            DB::table('categories')->insert(array_merge($payload, [
                'slug' => $slug,
                'created_at' => $now,
            ]));
        }

        return (int) DB::table('categories')->where('slug', $slug)->value('id');
    }
}
