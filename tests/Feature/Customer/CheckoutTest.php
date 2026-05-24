<?php

namespace Tests\Feature\Customer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_checkout_redirects_when_cart_empty(): void
    {
        $this->get(route('customer.checkout.index'))
            ->assertRedirect(route('customer.cart.index'));
    }

    public function test_checkout_creates_pending_order_from_cart(): void
    {
        $warehouse = $this->createDefaultWarehouse();

        /** @var Product $product */
        $product = $this->createActiveProduct([
            'name' => 'Checkout Product',
            'regular_price' => '1000.0000',
            'manage_stock' => true,
            'stock_quantity' => null,
        ]);

        $this->attachInventoryForProduct($product, null, $warehouse, 100.0);

        $shipping = $this->createSriLankaFlatShippingRate(250.0);

        $add = $this->post(route('customer.cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
        $add->assertRedirect();

        $this->withCookiesFromResponse($add);

        $cartId = (int) CartItem::query()->where('product_id', $product->id)->value('cart_id');

        $coupon = Coupon::factory()
            ->percentage(10)
            ->create([
                'code' => 'CHK10',
            ]);

        $applyCoupon = $this->post(route('customer.cart.coupon.apply'), [
            'code' => $coupon->code,
        ]);
        $applyCoupon->assertRedirect();

        $this->withCookiesFromResponse($applyCoupon);

        $payload = [
            'customer_email' => 'buyer@example.test',
            'customer_phone' => '0772222222',

            'billing_first_name' => 'Test',
            'billing_last_name' => 'Buyer',
            'billing_phone' => '0772222222',
            'billing_email' => 'buyer@example.test',
            'billing_address_line_1' => 'Billing 1',
            'billing_city' => 'Colombo',
            'billing_district' => 'Colombo',
            'billing_province' => 'Western',
            'billing_country_code' => 'LK',

            'ship_to_different_address' => false,

            'shipping_rate_id' => $shipping['rate']->id,
        ];

        $this->post(route('customer.checkout.store'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame('pending', $order->status);
        $this->assertSame('unpaid', $order->payment_status);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'unit_price' => '1000.0000',
        ]);

        $this->assertDatabaseHas('order_addresses', [
            'order_id' => $order->id,
            'type' => 'billing',
        ]);

        $this->assertDatabaseHas('order_addresses', [
            'order_id' => $order->id,
            'type' => 'shipping',
        ]);

        $this->assertDatabaseHas('carts', [
            'id' => $cartId,
            'status' => 'converted',
        ]);

        $this->assertSame('200.0000', (string) $order->discount_total);
        $this->assertSame('250.0000', (string) $order->shipping_total);
        $this->assertSame('2050.0000', (string) $order->grand_total);
        $this->assertSame((int) $coupon->id, (int) $order->coupon_id);
        $this->assertSame($coupon->code, $order->coupon_code);

        $stock = InventoryStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->whereNull('variant_id')
            ->firstOrFail();

        $this->assertSame('2.0000', (string) $stock->reserved_quantity);
    }
}
