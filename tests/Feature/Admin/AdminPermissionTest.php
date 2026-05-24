<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_super_admin_can_access_dashboard(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_without_dashboard_view_gets_403(): void
    {
        $admin = $this->createAdminWithPermission(['products.view']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_with_products_view_can_access_product_index(): void
    {
        $admin = $this->createAdminWithPermission(['products.view']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_admin_without_products_view_gets_403(): void
    {
        $admin = $this->createAdminWithPermission(['dashboard.view']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }
}
