# Admin CRUD foundation pattern (open_ecommerce_laravel)

This document describes conventions for future admin modules. Controllers should stay thin; domain logic belongs in services or actions later.

## Controllers

- Place admin HTTP controllers under `app/Http/Controllers/Admin/`.
- Name by resource in singular + `Controller`: e.g. `ProductController`, `OrderController`.
- Extend `BaseAdminController` for shared redirect and permission helpers.
- Use route middleware `auth:admin` and `admin.permission:{slug}` on groups or routes.

## Routes

- File: `routes/admin.php` (prefix `admin`, middleware `web`).
- Prefer named routes following `admin.{resource}.{action}`:
  - Index: `admin.products.index`
  - Create form: `admin.products.create`
  - Store: `admin.products.store`
  - Show: `admin.products.show`
  - Edit form: `admin.products.edit`
  - Update: `admin.products.update`
  - Destroy: `admin.products.destroy`
- Nested or special resources may add segments (e.g. `admin.settings.store` for a settings screen).

## Permissions

- Slugs are `{module}.{action}` (see `AdminPermissionSeeder` / `admin_permissions.slug`).
- Middleware: `admin.permission:products.view`.
- Add new permissions in the seeder’s `moduleActions()` map (no duplicates; seeder upserts by slug).
- Use `abortUnlessCan('products.update')` inside a controller when a single action needs a stricter check than the route middleware.

## Blade views

Per module directory `resources/views/admin/{module}/`:

| View | Purpose |
|------|---------|
| `index.blade.php` | List + filters + pagination |
| `create.blade.php` | Create form |
| `edit.blade.php` | Edit form |
| `show.blade.php` | Read-only detail |
| `partials/form.blade.php` | Shared fields for create/edit |

Shared partials (filters, table actions, delete confirmation UI, pagination wrapper):  
`resources/views/admin/shared/`.

Use existing `x-admin.*` components and `__('admin.*')` strings.

## Form requests

- Namespace: `App\Http\Requests\Admin`.
- Name `{Resource}{Action}Request`, e.g. `ProductStoreRequest`, `ProductUpdateRequest`.
- Use them for validation and authorization snippets where appropriate.

## Services and repositories

- Optional: `app/Services/Admin/` for use-case style classes when logic grows (pricing, stock, etc.).
- Optional: repositories only if querying becomes heavy or duplicated; start with Eloquent on the service/controller until duplication hurts.

## Pagination and filtering

- Paginate in the controller: `Model::query()->paginate(20)->withQueryString()`.
- Pass filters from `request()` explicitly in the controller; use the `admin.shared.search-filter` and `admin.shared.status-filter` partials to preserve query keys.
- Reset filters by linking to the index route without query parameters.

## Soft deletes

- If the model uses `SoftDeletes`, scope admin lists to `withTrashed()` only when an “include archived” / status filter requires it.
- Use a dedicated “restore” action and permission when needed; document the slug in the seeder.

## Flash messages

- Success: `session('success')` — use `$this->success()`, `backWithSuccess()`, or `->with('success', __('admin.success_*'))`.
- Error: `session('error')` — use `$this->error()`, `backWithError()`, or validation errors via `FormRequest`.
- Keep messages localized with `lang/en/admin.php` (and future locales).

## Deletes

- Prefer a dedicated confirmation UI (e.g. `admin.shared.delete-modal`) on show/edit, or a `DELETE` form with clear intent.
- Use `@include('admin.shared.table-actions', …)` for compact row actions when modules implement full CRUD.

## Summary

Keep routes, permissions, views, and requests predictable: one resource per folder, one permission slug shape, and shared partials for repeated UI so new modules look familiar.
