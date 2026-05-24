<?php

namespace Tests\Feature\Security;

use App\Models\CartItem;
use App\Services\SettingService;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_security_headers_are_present(): void
    {
        $res = $this->get(route('customer.home'));

        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_payhere_notify_route_is_csrf_exempt_and_other_routes_are_not(): void
    {
        $middleware = new PreventRequestForgery($this->app, $this->app->make('encrypter'));
        $except = $middleware->getExcludedPaths();

        $this->assertContains('payments/payhere/notify', $except);
        $this->assertNotContains('cart/coupon', $except);

        // PayHere notify should not fail purely due to CSRF (signature validation happens in app code).
        $notifyProbe = $this->post('/payments/payhere/notify', []);
        $this->assertSame(200, $notifyProbe->getStatusCode());
    }

    public function test_customer_cannot_update_another_cart_item(): void
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

        $itemId = (int) CartItem::query()->where('product_id', $product->id)->value('id');

        $attacker = $this->createCustomer();

        $this->defaultCookies = [];
        $this->unencryptedCookies = [];

        $this->actingAs($attacker, 'customer')
            ->put(route('customer.cart.items.update', $itemId), [
                'quantity' => 9,
            ])
            ->assertRedirect(route('customer.cart.index'))
            ->assertSessionHasErrors(['cart']);
    }

    public function test_media_upload_rejects_invalid_file_type(): void
    {
        $admin = $this->createAdminWithPermission(['content.create']);

        $file = UploadedFile::fake()->create('malware.exe', 10, 'application/octet-stream');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.media.store'), [
                'file' => $file,
            ])
            ->assertSessionHasErrors(['file']);
    }

    public function test_media_upload_accepts_valid_image(): void
    {
        $admin = $this->createAdminWithPermission(['content.create']);

        $file = UploadedFile::fake()->image('valid.png', 10, 10);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.media.store'), [
                'file' => $file,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('media', 1);
    }

    public function test_secret_settings_are_not_returned_in_public_settings(): void
    {
        DB::table('system_settings')->insert([
            'key' => 'payhere.merchant_secret',
            'value' => 'super-secret',
            'type' => 'string',
            'group' => 'payhere',
            'is_public' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        /** @var SettingService $settings */
        $settings = app(SettingService::class);
        $settings->forgetCache();

        $public = $settings->publicSettings();

        $this->assertArrayNotHasKey('payhere.merchant_secret', $public);
    }
}
