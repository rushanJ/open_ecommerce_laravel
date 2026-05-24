<?php

namespace Tests\Feature\AdminApi;

use App\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class AdminApiReadTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_valid_token_can_access_products(): void
    {
        $this->createActiveProduct(['name' => 'Admin API Product']);

        /** @var ApiTokenService $svc */
        $svc = app(ApiTokenService::class);
        $created = $svc->createToken('p', ['products.read']);

        $res = $this->withHeader('Authorization', 'Bearer '.$created['plain'])
            ->getJson('/api/admin/v1/products');

        $res->assertOk()
            ->assertJsonPath('success', true);

        $this->assertStringNotContainsString('token_hash', $res->getContent());
        $this->assertStringNotContainsString($created['token']->token_hash, $res->getContent());
    }
}
