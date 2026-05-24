<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    public function test_admin_with_products_view_can_list_products(): void
    {
        $admin = $this->createAdminWithPermission(['products.view']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_admin_with_products_create_can_create_simple_product(): void
    {
        $admin = $this->createAdminWithPermission(['products.create']);

        $name = 'Test Simple Product '.uniqid();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'product_type' => 'simple',
                'name' => $name,
                'status' => 'active',
                'visibility' => 'visible',
                'regular_price' => '1999.0000',
                'stock_status' => 'in_stock',
                'manage_stock' => '0',
                'backorders_allowed' => '0',
                'is_featured' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('products', [
            'name' => $name,
            'product_type' => 'simple',
            'status' => 'active',
        ]);
    }

    public function test_admin_create_pin_blocks_product_creation_when_incorrect(): void
    {
        config(['open_ecommerce_laravel.admin.create_pin' => '2468']);

        $admin = $this->createAdminWithPermission(['products.create']);
        $name = 'PIN Protected Product '.uniqid();
        $payload = [
            'product_type' => 'simple',
            'name' => $name,
            'status' => 'active',
            'visibility' => 'visible',
            'regular_price' => '1999.0000',
            'stock_status' => 'in_stock',
            'manage_stock' => '0',
            'backorders_allowed' => '0',
            'is_featured' => '0',
        ];

        $this->actingAs($admin, 'admin')
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $payload + [
                'admin_create_pin' => '0000',
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['admin_create_pin']);

        $this->assertDatabaseMissing('products', ['name' => $name]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), $payload + [
                'admin_create_pin' => '2468',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['name' => $name]);
    }

    public function test_slug_auto_generates_if_missing(): void
    {
        $admin = $this->createAdminWithPermission(['products.create']);

        $name = 'Auto Slug Product '.uniqid();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'product_type' => 'simple',
                'name' => $name,
                'status' => 'draft',
                'visibility' => 'visible',
                'regular_price' => '500.0000',
                'stock_status' => 'in_stock',
                'manage_stock' => '0',
                'backorders_allowed' => '0',
                'is_featured' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $product = Product::query()->where('name', $name)->firstOrFail();
        $this->assertNotSame('', trim((string) $product->slug));
        $this->assertSame(Str::slug($name), $product->slug);
    }

    public function test_product_can_attach_categories(): void
    {
        $admin = $this->createAdminWithPermission(['products.create']);

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $name = 'Categorized Product '.uniqid();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'product_type' => 'simple',
                'name' => $name,
                'status' => 'draft',
                'visibility' => 'visible',
                'regular_price' => '500.0000',
                'stock_status' => 'in_stock',
                'manage_stock' => '0',
                'backorders_allowed' => '0',
                'is_featured' => '0',
                'category_ids' => [$categoryA->id, $categoryB->id],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $product = Product::query()->where('name', $name)->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$categoryA->id, $categoryB->id],
            $product->categories()->pluck('categories.id')->map(fn ($id) => (int) $id)->all()
        );
    }

    public function test_product_can_attach_brand(): void
    {
        $admin = $this->createAdminWithPermission(['products.create']);

        $brand = Brand::factory()->create();
        $name = 'Branded Product '.uniqid();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.store'), [
                'product_type' => 'simple',
                'name' => $name,
                'brand_id' => $brand->id,
                'status' => 'draft',
                'visibility' => 'visible',
                'regular_price' => '500.0000',
                'stock_status' => 'in_stock',
                'manage_stock' => '0',
                'backorders_allowed' => '0',
                'is_featured' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $product = Product::query()->where('name', $name)->firstOrFail();
        $this->assertSame((int) $brand->id, (int) $product->brand_id);
    }

    public function test_admin_with_products_update_can_update_product(): void
    {
        $admin = $this->createAdminWithPermission(['products.update']);

        $product = Product::factory()->create([
            'name' => 'Original',
            'slug' => 'original-'.uniqid(),
            'status' => 'draft',
            'visibility' => 'visible',
            'regular_price' => '100.0000',
            'stock_status' => 'in_stock',
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.products.update', $product), [
                'product_type' => 'simple',
                'name' => 'Updated',
                'slug' => $product->slug,
                'status' => 'active',
                'visibility' => 'visible',
                'regular_price' => '150.0000',
                'stock_status' => 'in_stock',
                'manage_stock' => '0',
                'backorders_allowed' => '0',
                'is_featured' => '0',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $product->refresh();
        $this->assertSame('Updated', $product->name);
        $this->assertSame('active', $product->status);
    }

    public function test_admin_with_products_delete_can_soft_delete_product(): void
    {
        $admin = $this->createAdminWithPermission(['products.delete']);

        $product = Product::factory()->create([
            'name' => 'To Delete',
            'slug' => 'to-delete-'.uniqid(),
            'status' => 'draft',
            'visibility' => 'visible',
            'regular_price' => '100.0000',
            'stock_status' => 'in_stock',
        ]);

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.products.destroy', $product))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }
}
