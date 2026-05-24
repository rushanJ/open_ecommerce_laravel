<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminPermissionSeeder extends Seeder
{
    /**
     * @return array<string, list<string>>
     */
    private function moduleActions(): array
    {
        return [
            'dashboard' => ['view'],
            'settings' => ['view', 'create', 'update', 'delete'],
            'admins' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete'],
            'customers' => ['view', 'create', 'update', 'delete', 'export'],
            'products' => ['view', 'create', 'update', 'delete', 'publish', 'export'],
            'categories' => ['view', 'create', 'update', 'delete'],
            'brands' => ['view', 'create', 'update', 'delete'],
            'attributes' => ['view', 'create', 'update', 'delete'],
            'orders' => ['view', 'create', 'update', 'cancel', 'refund', 'export'],
            'payments' => ['view', 'update', 'refund'],
            'coupons' => ['view', 'create', 'update', 'delete'],
            'reports' => ['view', 'export'],
            'content' => ['view', 'create', 'update', 'delete'],
            'reviews' => ['view', 'approve', 'reject', 'delete'],
            'shipping' => ['view', 'create', 'update', 'delete'],
            'taxes' => ['view', 'create', 'update', 'delete'],
            'activity_logs' => ['view'],
            'inventory' => ['view', 'create', 'update', 'delete'],
        ];
    }

    private function permissionName(string $module, string $action): string
    {
        $moduleLabel = Str::headline(str_replace('_', ' ', $module));
        $actionLabel = Str::headline(str_replace('_', ' ', $action));

        return "{$moduleLabel} — {$actionLabel}";
    }

    private function permissionSlug(string $module, string $action): string
    {
        return $module.'.'.$action;
    }

    /**
     * @param  list<string>  $slugs
     */
    private function syncRolePermissionsBySlug(string $roleSlug, array $slugs): void
    {
        $roleId = DB::table('admin_roles')->where('slug', $roleSlug)->value('id');
        if (! $roleId) {
            return;
        }

        $permissionIds = DB::table('admin_permissions')
            ->whereIn('slug', $slugs)
            ->pluck('id');

        DB::table('admin_role_permissions')->where('role_id', $roleId)->delete();

        $now = now();
        $rows = [];
        foreach ($permissionIds as $permissionId) {
            $rows[] = [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if ($rows !== []) {
            DB::table('admin_role_permissions')->insert($rows);
        }
    }

    /**
     * @param  list<string>  $modules
     * @return list<string>
     */
    private function allSlugsForModules(array $modules): array
    {
        $map = $this->moduleActions();
        $slugs = [];
        foreach ($modules as $module) {
            if (! isset($map[$module])) {
                continue;
            }
            foreach ($map[$module] as $action) {
                $slugs[] = $this->permissionSlug($module, $action);
            }
        }

        return $slugs;
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $map = $this->moduleActions();

            foreach ($map as $module => $actions) {
                foreach ($actions as $action) {
                    $slug = $this->permissionSlug($module, $action);
                    $name = $this->permissionName($module, $action);

                    $payload = [
                        'module' => $module,
                        'action' => $action,
                        'name' => $name,
                        'updated_at' => $now,
                    ];

                    if (DB::table('admin_permissions')->where('slug', $slug)->exists()) {
                        DB::table('admin_permissions')->where('slug', $slug)->update($payload);
                    } else {
                        DB::table('admin_permissions')->insert(array_merge($payload, [
                            'slug' => $slug,
                            'created_at' => $now,
                        ]));
                    }
                }
            }

            $allSlugs = DB::table('admin_permissions')->pluck('slug')->all();
            $this->syncRolePermissionsBySlug('super-admin', $allSlugs);

            $storeManagerModules = [
                'dashboard', 'settings', 'products', 'categories', 'brands', 'attributes', 'inventory', 'orders', 'customers',
                'coupons', 'reports', 'reviews', 'shipping', 'taxes', 'content',
            ];
            $this->syncRolePermissionsBySlug('store-manager', $this->allSlugsForModules($storeManagerModules));

            $orderManagerModules = ['dashboard', 'orders', 'payments', 'customers', 'reports'];
            $orderSlugs = $this->allSlugsForModules($orderManagerModules);
            $orderSlugs[] = 'inventory.view';
            $this->syncRolePermissionsBySlug('order-manager', array_values(array_unique($orderSlugs)));

            $catalogManagerModules = ['dashboard', 'products', 'categories', 'brands', 'attributes', 'reviews'];
            $catalogSlugs = $this->allSlugsForModules($catalogManagerModules);
            $catalogSlugs[] = 'inventory.view';
            $catalogSlugs[] = 'inventory.update';
            $this->syncRolePermissionsBySlug('catalog-manager', array_values(array_unique($catalogSlugs)));

            $supportSlugs = [
                'dashboard.view',
                'orders.view',
                'customers.view',
                'reviews.view',
            ];
            $this->syncRolePermissionsBySlug('support-agent', $supportSlugs);
        });
    }
}
