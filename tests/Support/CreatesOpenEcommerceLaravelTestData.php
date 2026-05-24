<?php

namespace Tests\Support;

use App\Models\AdminPermission;
use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use App\Models\Store;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\Payments\PayHereService;
use App\Services\SettingService;
use Database\Seeders\AdminPermissionSeeder;
use Database\Seeders\AdminRoleSeeder;
use Illuminate\Support\Facades\DB;

trait CreatesOpenEcommerceLaravelTestData
{
    private bool $openEcommerceLaravelAdminAclSeeded = false;

    private bool $openEcommerceLaravelStorefrontFixturesReady = false;

    protected function seedOpenEcommerceLaravelAdminAcl(): void
    {
        if ($this->openEcommerceLaravelAdminAclSeeded) {
            return;
        }

        $this->seed(AdminRoleSeeder::class);
        $this->seed(AdminPermissionSeeder::class);

        $this->openEcommerceLaravelAdminAclSeeded = true;
    }

    protected function ensureOpenEcommerceLaravelStorefrontFixtures(): void
    {
        if ($this->openEcommerceLaravelStorefrontFixturesReady) {
            return;
        }

        if (Store::query()->count() === 0) {
            Store::query()->create([
                'name' => 'open_ecommerce_laravel Test Store',
                'legal_name' => 'open_ecommerce_laravel Test Store',
                'domain' => null,
                'email' => 'store@example.test',
                'phone' => '0770000000',
                'logo_path' => null,
                'favicon_path' => null,
                'currency_code' => 'LKR',
                'timezone' => 'Asia/Colombo',
                'address_line_1' => '1 Test Street',
                'address_line_2' => null,
                'city' => 'Colombo',
                'district' => 'Colombo',
                'province' => 'Western',
                'postal_code' => null,
                'country_code' => 'LK',
                'status' => 'active',
                'metadata' => null,
            ]);
        }

        $this->openEcommerceLaravelStorefrontFixturesReady = true;
    }

    /**
     * @param  string|array<int, string>  $permissions
     */
    protected function createAdminWithPermission(string|array $permissions): AdminUser
    {
        $this->seedOpenEcommerceLaravelAdminAcl();

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        $slug = 'test-role-'.uniqid();
        $now = now();
        DB::table('admin_roles')->insert([
            'name' => 'Test Role '.$slug,
            'slug' => $slug,
            'description' => null,
            'is_system' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /** @var AdminRole $role */
        $role = AdminRole::query()->where('slug', $slug)->firstOrFail();

        $permissionIds = AdminPermission::query()
            ->whereIn('slug', $permissions)
            ->pluck('id')
            ->all();

        $rows = [];
        foreach ($permissionIds as $permissionId) {
            $rows[] = [
                'role_id' => $role->getKey(),
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows !== []) {
            DB::table('admin_role_permissions')->insert($rows);
        }

        /** @var AdminUser $admin */
        $admin = AdminUser::factory()->create([
            'status' => 'active',
        ]);

        DB::table('admin_user_roles')->insert([
            'admin_user_id' => $admin->getKey(),
            'role_id' => $role->getKey(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $admin->fresh(['roles.permissions']);
    }

    protected function createSuperAdmin(): AdminUser
    {
        $this->seedOpenEcommerceLaravelAdminAcl();

        $roleId = DB::table('admin_roles')->where('slug', 'super-admin')->value('id');
        if (! $roleId) {
            throw new \RuntimeException('Missing super-admin role.');
        }

        /** @var AdminUser $admin */
        $admin = AdminUser::factory()->create([
            'status' => 'active',
        ]);

        $now = now();
        DB::table('admin_user_roles')->insert([
            'admin_user_id' => $admin->getKey(),
            'role_id' => $roleId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $admin->fresh(['roles.permissions']);
    }

    protected function createCustomer(array $overrides = []): Customer
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        /** @var Customer $customer */
        $customer = Customer::factory()->create($overrides);

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function createActiveProduct(array $overrides = []): Product
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        /** @var Product $product */
        $product = Product::factory()
            ->activeStorefront()
            ->create(array_merge([
                'manage_stock' => true,
                'stock_quantity' => '100.0000',
                'stock_status' => 'in_stock',
            ], $overrides));

        return $product->fresh();
    }

    /**
     * @return array{product: Product, variant: ProductVariant}
     */
    protected function createVariantProduct(): array
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        /** @var Product $product */
        $product = Product::factory()
            ->activeStorefront()
            ->variable()
            ->create([
                'manage_stock' => false,
                'stock_quantity' => null,
            ]);

        /** @var ProductVariant $variant */
        $variant = ProductVariant::factory()
            ->forProduct($product)
            ->create([
                'regular_price' => '200.0000',
                'stock_quantity' => '50.0000',
                'stock_status' => 'in_stock',
                'status' => 'active',
            ]);

        return [
            'product' => $product->fresh(),
            'variant' => $variant->fresh(),
        ];
    }

    protected function createCartWithItems(?Customer $customer = null): Cart
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        $product = $this->createActiveProduct();

        if ($customer === null) {
            if (! session()->isStarted()) {
                $this->startSession();
            }
        }

        /** @var Cart $cart */
        $cart = $customer !== null
            ? Cart::factory()->forCustomer($customer)->create()
            : Cart::factory()->create([
                'customer_id' => null,
                'session_id' => session()->getId(),
                'status' => 'active',
                'currency_code' => 'LKR',
                'expires_at' => now()->addDays(30),
            ]);

        CartItem::factory()
            ->forCart($cart)
            ->forProduct($product)
            ->create();

        $cart->refresh();

        return $cart;
    }

    protected function createDefaultWarehouse(): Warehouse
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        return Warehouse::factory()->create([
            'status' => 'active',
        ]);
    }

    /**
     * @return array{zone: ShippingZone, method: ShippingMethod, rate: ShippingRate}
     */
    protected function createSriLankaFlatShippingRate(float $amount = 250.0): array
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        /** @var ShippingZone $zone */
        $zone = ShippingZone::query()->create([
            'name' => 'Sri Lanka',
            'country_code' => 'LK',
            'province' => 'Western',
            'district' => 'Colombo',
            'city' => 'Colombo',
            'status' => 'active',
        ]);

        /** @var ShippingMethod $method */
        $method = ShippingMethod::query()->create([
            'name' => 'Standard',
            'code' => 'std-'.uniqid(),
            'type' => 'flat_rate',
            'status' => 'active',
            'config' => null,
        ]);

        /** @var ShippingRate $rate */
        $rate = ShippingRate::query()->create([
            'shipping_zone_id' => $zone->getKey(),
            'shipping_method_id' => $method->getKey(),
            'min_order_amount' => null,
            'max_order_amount' => null,
            'min_weight' => null,
            'max_weight' => null,
            'rate' => number_format($amount, 4, '.', ''),
            'status' => 'active',
        ]);

        return [
            'zone' => $zone,
            'method' => $method,
            'rate' => $rate,
        ];
    }

    protected function attachInventoryForProduct(Product $product, ?ProductVariant $variant, Warehouse $warehouse, float $qtyOnHand): InventoryStock
    {
        $qty = number_format($qtyOnHand, 4, '.', '');

        return InventoryStock::factory()
            ->forWarehouseProduct($warehouse, $product, $variant)
            ->create([
                'quantity' => $qty,
                'reserved_quantity' => '0.0000',
                'available_quantity' => $qty,
            ]);
    }

    protected function createPendingOrder(): Order
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        $warehouse = $this->createDefaultWarehouse();
        $product = $this->createActiveProduct([
            'manage_stock' => true,
            'stock_quantity' => null,
        ]);

        $this->attachInventoryForProduct($product, null, $warehouse, 100.0);

        /** @var Order $order */
        $order = Order::factory()->create([
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => '100.0000',
            'discount_total' => '0.0000',
            'tax_total' => '0.0000',
            'shipping_total' => '0.0000',
            'grand_total' => '100.0000',
        ]);

        OrderItem::factory()
            ->fromProduct($order, $product, null, 1.0)
            ->create();

        OrderAddress::factory()
            ->forOrder($order, 'billing')
            ->create([
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'email' => 'buyer@example.test',
                'phone' => '0771111111',
                'address_line_1' => 'Billing street',
                'city' => 'Colombo',
                'district' => 'Colombo',
                'province' => 'Western',
                'country_code' => 'LK',
            ]);

        $order->load('items');

        DB::transaction(function () use ($order): void {
            /** @var InventoryService $inventory */
            $inventory = app(InventoryService::class);

            foreach ($order->items as $orderItem) {
                $product = Product::query()->find($orderItem->product_id);
                if ($product === null) {
                    continue;
                }

                $variant = $orderItem->variant_id ? ProductVariant::query()->find($orderItem->variant_id) : null;
                $remaining = (float) $orderItem->quantity;

                $rows = InventoryStock::query()
                    ->where('product_id', $product->getKey())
                    ->when(
                        $variant !== null,
                        fn ($q) => $q->where('variant_id', $variant->getKey()),
                        fn ($q) => $q->whereNull('variant_id'),
                    )
                    ->orderByDesc('available_quantity')
                    ->lockForUpdate()
                    ->get();

                foreach ($rows as $stockRow) {
                    if ($remaining <= 0) {
                        break;
                    }

                    $avail = max(0.0, (float) $stockRow->quantity - (float) $stockRow->reserved_quantity);
                    $take = min($remaining, $avail);

                    if ($take <= 0) {
                        continue;
                    }

                    $warehouse = Warehouse::query()->findOrFail($stockRow->warehouse_id);

                    $inventory->reserveStock(
                        $warehouse,
                        $product,
                        $variant,
                        $take,
                        'Order '.$order->order_number,
                        null,
                        'order',
                        $order->getKey()
                    );

                    $remaining -= $take;
                }

                if ($remaining > 0 && ! $product->backorders_allowed) {
                    throw new \RuntimeException('Test order could not reserve full inventory (fixture setup).');
                }
            }
        });

        return $order->fresh(['items']);
    }

    protected function createPaidOrder(): Order
    {
        $order = $this->createPendingOrder();

        $order->forceFill([
            'payment_status' => 'paid',
            'paid_total' => $order->grand_total,
        ])->save();

        return $order->fresh(['items']);
    }

    protected function createPayHerePayment(Order $order): Payment
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        /** @var SettingService $settings */
        $settings = app(SettingService::class);
        $settings->forgetCache();
        $settings->set('payhere.enabled', true, 'boolean', 'payhere', false);
        $settings->set('payhere.mode', 'sandbox', 'string', 'payhere', false);
        $settings->set('payhere.merchant_id', '121XXXX', 'string', 'payhere', false);
        $settings->set('payhere.merchant_secret', 'test-secret', 'string', 'payhere', false);

        PaymentMethod::query()->updateOrCreate(
            ['code' => 'payhere'],
            [
                'name' => 'PayHere',
                'provider' => 'payhere',
                'status' => 'active',
                'config' => ['enabled' => true, 'mode' => 'sandbox'],
            ],
        );

        /** @var PayHereService $payHere */
        $payHere = app(PayHereService::class);

        return $payHere->createPaymentForOrder($order->fresh(['billingAddress']));
    }

    protected function payHereNotifyMd5Sig(array $payload): string
    {
        /** @var SettingService $settings */
        $settings = app(SettingService::class);

        $merchantId = (string) $settings->get('payhere.merchant_id', '');
        $merchantSecret = (string) $settings->get('payhere.merchant_secret', '');

        $orderId = (string) ($payload['order_id'] ?? '');
        $amount = (string) ($payload['payhere_amount'] ?? '');
        $currency = (string) ($payload['payhere_currency'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');

        return strtoupper(md5(
            $merchantId.
            $orderId.
            $amount.
            $currency.
            $statusCode.
            strtoupper(md5($merchantSecret))
        ));
    }

    protected function restrictCouponToProduct(Coupon $coupon, Product $product): void
    {
        DB::table('coupon_products')->insert([
            'coupon_id' => $coupon->getKey(),
            'product_id' => $product->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function restrictCouponToCategory(Coupon $coupon, Category $category): void
    {
        DB::table('coupon_categories')->insert([
            'coupon_id' => $coupon->getKey(),
            'category_id' => $category->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
