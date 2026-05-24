<?php

namespace Tests\Feature\Customer;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_valid_percentage_coupon_applies_discount(): void
    {
        $product = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->create([
                'code' => 'TENOFF',
            ]);

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertRedirect();

        $cartId = (int) CartItem::query()->where('product_id', $product->id)->value('cart_id');

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'coupon_id' => $coupon->id,
            'subtotal' => '1000.0000',
            'discount_total' => '100.0000',
            'grand_total' => '900.0000',
        ]);
    }

    public function test_fixed_cart_coupon_applies_discount(): void
    {
        $product = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $coupon = Coupon::factory()
            ->fixedCart(250)
            ->create([
                'code' => 'FIXED250',
            ]);

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertRedirect();

        $cartId = (int) CartItem::query()->where('product_id', $product->id)->value('cart_id');

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'coupon_id' => $coupon->id,
            'discount_total' => '250.0000',
            'grand_total' => '750.0000',
        ]);
    }

    public function test_minimum_order_amount_is_enforced(): void
    {
        $product = $this->createActiveProduct([
            'regular_price' => '500.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $coupon = Coupon::factory()
            ->fixedCart(50)
            ->create([
                'code' => 'MIN1000',
                'minimum_order_amount' => '1000.0000',
            ]);

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertSessionHasErrors(['code']);
    }

    public function test_expired_coupon_rejected(): void
    {
        $product = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->expired()
            ->create([
                'code' => 'OLD',
            ]);

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertSessionHasErrors(['code']);
    }

    public function test_product_restricted_coupon_only_applies_to_eligible_cart(): void
    {
        $eligible = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $other = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->create([
                'code' => 'ONLYA',
            ]);

        $this->restrictCouponToProduct($coupon, $eligible);

        $addOther = $this->post(route('customer.cart.items.store'), [
            'product_id' => $other->id,
            'quantity' => 1,
        ]);
        $addOther->assertRedirect();

        $this->withCookiesFromResponse($addOther);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertSessionHasErrors(['code']);

        $addEligible = $this->post(route('customer.cart.items.store'), [
            'product_id' => $eligible->id,
            'quantity' => 1,
        ]);
        $addEligible->assertRedirect();

        $this->withCookiesFromResponse($addEligible);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertRedirect();

        $cartId = (int) CartItem::query()->where('product_id', $eligible->id)->value('cart_id');

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'coupon_id' => $coupon->id,
            'subtotal' => '2000.0000',
            'discount_total' => '100.0000',
            'grand_total' => '1900.0000',
        ]);
    }

    public function test_category_restricted_coupon_only_applies_to_eligible_cart(): void
    {
        $category = Category::factory()->create();

        $eligible = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);
        $eligible->categories()->sync([$category->id]);

        $other = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->create([
                'code' => 'CAT10',
            ]);

        $this->restrictCouponToCategory($coupon, $category);

        $addOther = $this->post(route('customer.cart.items.store'), [
            'product_id' => $other->id,
            'quantity' => 1,
        ]);
        $addOther->assertRedirect();

        $this->withCookiesFromResponse($addOther);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertSessionHasErrors(['code']);

        $addEligible = $this->post(route('customer.cart.items.store'), [
            'product_id' => $eligible->id,
            'quantity' => 1,
        ]);
        $addEligible->assertRedirect();

        $this->withCookiesFromResponse($addEligible);

        $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ])->assertRedirect();

        $cartId = (int) CartItem::query()->where('product_id', $eligible->id)->value('cart_id');

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'coupon_id' => $coupon->id,
            'subtotal' => '2000.0000',
            'discount_total' => '100.0000',
            'grand_total' => '1900.0000',
        ]);
    }

    public function test_removing_coupon_recalculates_totals(): void
    {
        $product = $this->createActiveProduct([
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => '10.0000',
        ]);

        $coupon = Coupon::factory()
            ->percentage(10)
            ->create([
                'code' => 'RM10',
            ]);

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $apply = $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ]);
        $apply->assertRedirect();

        $this->withCookiesFromResponse($apply);

        $cartId = (int) CartItem::query()->where('product_id', $product->id)->value('cart_id');

        $this->delete(route('customer.cart.coupon.remove'))->assertRedirect();

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'coupon_id' => null,
            'coupon_code' => null,
            'discount_total' => '0.0000',
            'subtotal' => '1000.0000',
            'grand_total' => '1000.0000',
        ]);
    }
}
