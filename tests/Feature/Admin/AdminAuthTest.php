<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use Database\Seeders\AdminPermissionSeeder;
use Database\Seeders\AdminRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AdminRoleSeeder::class);
        $this->seed(AdminPermissionSeeder::class);
    }

    public function test_admin_login_page_loads(): void
    {
        $this->get(route('admin.login'))
            ->assertOk();
    }

    public function test_active_admin_can_login(): void
    {
        /** @var AdminUser $admin */
        $admin = AdminUser::factory()->create([
            'email' => 'active-admin@example.test',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_inactive_admin_cannot_login(): void
    {
        /** @var AdminUser $admin */
        $admin = AdminUser::factory()->create([
            'email' => 'inactive-admin@example.test',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertSessionHasErrors();

        $this->assertGuest('admin');
    }

    public function test_unauthenticated_admin_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
    }

    public function test_admin_login_is_rate_limited(): void
    {
        /** @var AdminUser $admin */
        $admin = AdminUser::factory()->create([
            'email' => 'rate-limit@example.test',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.submit'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
