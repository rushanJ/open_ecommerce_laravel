<?php

namespace Tests\Feature\AdminApi;

use App\Models\ApiToken;
use App\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_token_returns_401(): void
    {
        $this->getJson('/api/admin/v1/health')->assertStatus(401);
    }

    public function test_invalid_token_returns_401(): void
    {
        $this->withHeader('Authorization', 'Bearer invalid')
            ->getJson('/api/admin/v1/health')
            ->assertStatus(401);
    }

    public function test_expired_token_returns_401(): void
    {
        /** @var ApiTokenService $svc */
        $svc = app(ApiTokenService::class);
        $created = $svc->createToken('exp', ['products.read'], now()->addDay());
        $created['token']->forceFill(['expires_at' => now()->subMinute()])->save();

        $this->withAdminBearer($created['plain'])
            ->getJson('/api/admin/v1/health')
            ->assertStatus(401);
    }

    public function test_missing_ability_returns_403(): void
    {
        /** @var ApiTokenService $svc */
        $svc = app(ApiTokenService::class);
        $created = $svc->createToken('orders', ['products.read']);

        $this->withAdminBearer($created['plain'])
            ->getJson('/api/admin/v1/orders')
            ->assertStatus(403);
    }

    public function test_valid_token_can_access_health(): void
    {
        /** @var ApiTokenService $svc */
        $svc = app(ApiTokenService::class);
        $created = $svc->createToken('health', []);

        $this->withAdminBearer($created['plain'])
            ->getJson('/api/admin/v1/health')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    protected function withAdminBearer(string $plain): self
    {
        return $this->withHeader('Authorization', 'Bearer '.$plain);
    }
}
