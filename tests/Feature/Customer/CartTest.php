<?php

namespace Tests\Feature\Customer;

use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class CartTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_guest_can_add_simple_product_to_cart(): void
    {
        $product = $this->createActiveProduct();

        $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_guest_cart_persists_by_session(): void
    {
        $product = $this->createActiveProduct();

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $this->get(route('customer.cart.index'))
            ->assertOk()
            ->assertSeeText($product->name);
    }

    public function test_adding_same_product_increments_quantity(): void
    {
        $product = $this->createActiveProduct();

        $first = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $first->assertRedirect();

        $this->withCookiesFromResponse($first);

        $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->assertSame(1, (int) CartItem::query()->where('product_id', $product->id)->count());

        $qty = (string) CartItem::query()->where('product_id', $product->id)->value('quantity');
        $this->assertSame('2.0000', $qty);
    }

    public function test_variable_product_requires_variant(): void
    {
        $bundle = $this->createVariantProduct();
        $product = $bundle['product'];

        $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
            ->assertSessionHasErrors(['variant_id']);
    }

    public function test_cannot_add_inactive_product(): void
    {
        $product = $this->createActiveProduct();
        $product->forceFill(['status' => 'inactive'])->save();

        $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasErrors(['product_id']);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $product = $this->createActiveProduct([
            'manage_stock' => true,
            'stock_quantity' => '2.0000',
        ]);

        $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertSessionHasErrors(['quantity']);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $product = $this->createActiveProduct([
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $itemId = (int) CartItem::query()->where('product_id', $product->id)->value('id');

        $update = $this->put(route('customer.cart.items.update', $itemId), [
            'quantity' => 2,
        ]);
        $update->assertRedirect();

        $qty = (string) CartItem::query()->whereKey($itemId)->value('quantity');
        $this->assertSame('2.0000', $qty);
    }

    public function test_can_remove_cart_item(): void
    {
        $product = $this->createActiveProduct();

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $itemId = (int) CartItem::query()->where('product_id', $product->id)->value('id');

        $this->delete(route('customer.cart.items.destroy', $itemId))
            ->assertRedirect();

        $this->assertDatabaseMissing('cart_items', [
            'id' => $itemId,
        ]);
    }

    public function test_cart_totals_recalculate_correctly(): void
    {
        $product = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect();

        $cartId = (int) CartItem::query()->where('product_id', $product->id)->value('cart_id');

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'subtotal' => '2000.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '2000.0000',
        ]);
    }
}
