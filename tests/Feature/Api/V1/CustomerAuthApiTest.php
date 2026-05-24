<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class CustomerAuthApiTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_register_returns_token(): void
    {
        $res = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Api',
            'last_name' => 'User',
            'email' => 'api-register@example.test',
            'phone' => '0779999001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $res->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['customer', 'token']]);

        $this->assertNotEmpty($res->json('data.token'));
    }

    public function test_login_returns_token(): void
    {
        $customer = $this->createCustomer([
            'email' => 'api-login@example.test',
            'password' => Hash::make('secretpass1'),
            'status' => 'active',
        ]);

        $res = $this->postJson('/api/v1/auth/login', [
            'email' => $customer->email,
            'password' => 'secretpass1',
        ]);

        $res->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['customer', 'token']]);
    }

    public function test_auth_me_works(): void
    {
        $customer = $this->createCustomer([
            'email' => 'api-me@example.test',
            'password' => Hash::make('secretpass1'),
            'status' => 'active',
        ]);

        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'api-me@example.test');
    }
}
