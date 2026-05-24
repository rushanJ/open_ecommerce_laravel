<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class PublicCatalogApiTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_product_list_returns_json(): void
    {
        $this->createActiveProduct(['name' => 'API List Product']);

        $res = $this->getJson('/api/v1/products');

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);
    }

    public function test_product_detail_by_slug(): void
    {
        $product = $this->createActiveProduct(['name' => 'Detail Product', 'slug' => 'detail-product-api']);

        $res = $this->getJson('/api/v1/products/'.$product->slug);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'detail-product-api')
            ->assertJsonPath('data.name', 'Detail Product');
    }
}
