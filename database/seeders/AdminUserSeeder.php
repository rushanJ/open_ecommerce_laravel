<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Default password is for local development only.
 *
 * Change OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD in .env and re-seed, or update the admin user
 * directly in production immediately after first deploy.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $name = env('OPEN_ECOMMERCE_LARAVEL_ADMIN_NAME', 'Super Admin');
            $email = env('OPEN_ECOMMERCE_LARAVEL_ADMIN_EMAIL', 'admin@open-ecommerce-laravel.test');
            $password = env('OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD', 'password');

            $admin = AdminUser::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'status' => 'active',
                    'phone' => null,
                    'avatar_path' => null,
                ]
            );

            $roleId = DB::table('admin_roles')->where('slug', 'super-admin')->value('id');
            if (! $roleId) {
                return;
            }

            $now = now();
            $exists = DB::table('admin_user_roles')
                ->where('admin_user_id', $admin->id)
                ->where('role_id', $roleId)
                ->exists();

            if ($exists) {
                DB::table('admin_user_roles')
                    ->where('admin_user_id', $admin->id)
                    ->where('role_id', $roleId)
                    ->update(['updated_at' => $now]);
            } else {
                DB::table('admin_user_roles')->insert([
                    'admin_user_id' => $admin->id,
                    'role_id' => $roleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
