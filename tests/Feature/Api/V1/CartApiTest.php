<?php

namespace Tests\Feature\Api\V1;

use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_guest_cart_add_item_returns_cart_token(): void
    {
        $product = $this->createActiveProduct();

        $res = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['meta' => ['cart_token']]);

        $token = $res->json('meta.cart_token');
        $this->assertNotEmpty($token);

        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ], ['X-Cart-Token' => $token])
            ->assertOk();

        $this->assertSame(1, (int) CartItem::query()->where('product_id', $product->id)->count());
    }

    public function test_authenticated_cart_add_item_works(): void
    {
        $customer = $this->createCustomer();
        $product = $this->createActiveProduct();

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('carts', [
            'customer_id' => $customer->id,
            'status' => 'active',
        ]);
    }
}
