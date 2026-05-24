<?php

namespace Tests\Feature\Webhooks;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_creates_delivery_row(): void
    {
        Http::fake([
            'https://example.test/hook' => Http::response(['ok' => true], 200),
        ]);

        WebhookEndpoint::query()->create([
            'name' => 'Test',
            'url' => 'https://example.test/hook',
            'events' => ['order.created'],
            'secret' => 'shh',
            'status' => 'active',
        ]);

        app(WebhookService::class)->dispatch('order.created', ['order_id' => 1]);

        $this->assertDatabaseHas('webhook_deliveries', [
            'event_type' => 'order.created',
        ]);

        $delivery = WebhookDelivery::query()->firstOrFail();
        $this->assertSame(1, (int) $delivery->attempts);
        $this->assertNotNull($delivery->delivered_at);
    }

    public function test_webhook_signature_header_sent_when_secret_set(): void
    {
        Http::fake(function (\Illuminate\Http\Client\Request $request) {
            return Http::response([], 200);
        });

        WebhookEndpoint::query()->create([
            'name' => 'Signed',
            'url' => 'https://example.test/signed',
            'events' => ['*'],
            'secret' => 'secret-key',
            'status' => 'active',
        ]);

        app(WebhookService::class)->dispatch('payment.paid', ['payment_id' => 9]);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            $headers = $request->headers();
            $sigList = $headers['X-Open-Ecommerce-Laravel-Signature'] ?? $headers['x-open-ecommerce-laravel-signature'] ?? null;
            $sig = is_array($sigList) ? ($sigList[0] ?? null) : $sigList;
            if (! is_string($sig) || $sig === '') {
                return false;
            }

            $expected = hash_hmac('sha256', (string) $request->body(), 'secret-key');

            return hash_equals($expected, $sig);
        });
    }

    public function test_webhook_failure_does_not_throw_from_dispatch_safe(): void
    {
        Http::fake([
            'https://example.test/fail' => Http::response('no', 500),
        ]);

        WebhookEndpoint::query()->create([
            'name' => 'Fail',
            'url' => 'https://example.test/fail',
            'events' => ['order.created'],
            'secret' => null,
            'status' => 'active',
        ]);

        app(WebhookService::class)->dispatchSafe('order.created', ['order_id' => 2]);

        $this->assertTrue(true);
    }
}
