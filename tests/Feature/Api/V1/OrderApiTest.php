<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_guest_order_lookup_works(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'MEK-LOOKUP-001',
            'customer_id' => null,
            'customer_email' => 'guest@example.test',
            'customer_phone' => '0771111222',
        ]);

        $this->postJson('/api/v1/orders/lookup', [
            'order_number' => $order->order_number,
            'contact' => 'guest@example.test',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_cannot_access_another_customer_order(): void
    {
        $a = $this->createCustomer();
        $b = $this->createCustomer();

        $order = Order::factory()->forCustomer($a)->create();

        Sanctum::actingAs($b);

        $this->getJson('/api/v1/orders/'.$order->id)
            ->assertStatus(404)
            ->assertJsonPath('success', false);
    }
}
