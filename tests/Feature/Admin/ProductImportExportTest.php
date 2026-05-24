<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ImportJob;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Support\CreatesOpenEcommerceLaravelTestData;
use Tests\TestCase;

class ProductImportExportTest extends TestCase
{
    use CreatesOpenEcommerceLaravelTestData;
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    protected function csvHeaders(): array
    {
        return [
            'sku', 'product_type', 'name', 'slug', 'brand', 'categories', 'short_description', 'description',
            'status', 'visibility', 'regular_price', 'sale_price', 'cost_price', 'stock_quantity', 'stock_status',
            'manage_stock', 'weight', 'length', 'width', 'height', 'tax_class', 'meta_title', 'meta_description',
            'variant_sku', 'variant_name', 'variant_regular_price', 'variant_sale_price', 'variant_stock_quantity', 'variant_stock_status',
        ];
    }

    /**
     * @param  list<list<string>>  $rows
     */
    protected function makeCsvFile(string $name, array $rows): UploadedFile
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $this->csvHeaders());
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $content = stream_get_contents($handle) ?: '';
        fclose($handle);

        return UploadedFile::fake()->createWithContent($name, $content);
    }

    protected function seedMainWarehouse(): Warehouse
    {
        $this->ensureOpenEcommerceLaravelStorefrontFixtures();

        return Warehouse::query()->updateOrCreate(
            ['name' => 'Main Warehouse'],
            ['code' => 'MAIN', 'address' => null, 'status' => 'active'],
        );
    }

    protected function adminForImport(): \App\Models\AdminUser
    {
        return $this->createAdminWithPermission([
            'products.view',
            'products.export',
            'products.create',
            'products.update',
        ]);
    }

    public function test_admin_can_export_products_csv(): void
    {
        $this->createActiveProduct(['name' => 'Export Me', 'sku' => 'EXP-001']);
        $admin = $this->createAdminWithPermission(['products.view', 'products.export']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.products.export'));

        $response->assertOk();
        $this->assertStringContainsString('sku', $response->streamedContent());
        $this->assertStringContainsString('EXP-001', $response->streamedContent());
    }

    public function test_import_rejects_invalid_file_type(): void
    {
        $admin = $this->adminForImport();
        $file = UploadedFile::fake()->create('bad.pdf', 100, 'application/pdf');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.import.upload'), ['file' => $file])
            ->assertSessionHasErrors('file');
    }

    public function test_import_creates_job_rows(): void
    {
        $this->seedMainWarehouse();
        $admin = $this->adminForImport();

        $file = $this->makeCsvFile('one.csv', [
            [
                'TST-SIMPLE', 'simple', 'One', 'one-'.uniqid(),
                '', '', '', '', 'draft', 'visible', '10.00', '', '', '5', 'in_stock', '1',
                '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            ],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.import.upload'), ['file' => $file])
            ->assertRedirect();

        $this->assertSame(1, ImportJob::query()->count());
        $job = ImportJob::query()->firstOrFail();
        $this->assertSame(1, $job->rows()->count());
        $this->assertSame('validated', $job->status);
    }

    public function test_invalid_row_blocks_validation(): void
    {
        $this->seedMainWarehouse();
        $admin = $this->adminForImport();

        $file = $this->makeCsvFile('bad.csv', [
            [
                '', 'simple', '', 'x',
                '', '', '', '', 'draft', 'visible', '', '', '', '', 'in_stock', '0',
                '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            ],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.import.upload'), ['file' => $file])
            ->assertRedirect();

        $job = ImportJob::query()->firstOrFail();
        $this->assertSame('failed', $job->status);
        $this->assertContains('invalid', $job->rows()->pluck('status')->all());

        $this->actingAs($admin, 'admin')
            ->post(route('admin.import-jobs.process', $job))
            ->assertSessionHas('error');
    }

    public function test_valid_simple_product_imports_and_updates_inventory(): void
    {
        $warehouse = $this->seedMainWarehouse();
        $admin = $this->adminForImport();

        $slug = 'imp-simple-'.uniqid();
        $file = $this->makeCsvFile('simple.csv', [
            [
                'IMP-SKU-1', 'simple', 'Imported Simple', $slug,
                'NewBrandX', 'NewCatA|NewCatB', 's', 'd', 'draft', 'visible', '25.50', '', '', '20', 'in_stock', '1',
                '1', '2', '3', '4', '', 'm', 'md', '', '', '', '', '', '',
            ],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.import.upload'), ['file' => $file])
            ->assertSessionHasNoErrors();

        $job = ImportJob::query()->firstOrFail();
        $this->assertSame('validated', $job->status);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.import-jobs.process', $job))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.import-jobs.show', $job));

        $product = Product::query()->where('sku', 'IMP-SKU-1')->firstOrFail();
        $this->assertSame('simple', $product->product_type);
        $this->assertTrue(Brand::query()->where('name', 'NewBrandX')->exists());
        $this->assertTrue(Category::query()->where('name', 'NewCatA')->exists());

        $stock = InventoryStock::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $product->getKey())
            ->whereNull('variant_id')
            ->first();
        $this->assertNotNull($stock);
        $this->assertEqualsWithDelta(20.0, (float) $stock->quantity, 0.0001);
    }

    public function test_valid_variable_product_imports_variants(): void
    {
        $this->seedMainWarehouse();
        $admin = $this->adminForImport();
        $slug = 'imp-var-'.uniqid();

        $file = $this->makeCsvFile('var.csv', [
            [
                'PARENT-V-1', 'variable', 'Var Hoodie', $slug,
                '', '', '', '', 'draft', 'visible', '0', '', '', '', 'in_stock', '0',
                '', '', '', '', '', '', '', 'V-SKU-S', 'Small', '30', '', '4', 'in_stock',
            ],
            [
                'PARENT-V-1', 'variable', 'Var Hoodie', '',
                '', '', '', '', 'draft', 'visible', '0', '', '', '', 'in_stock', '0',
                '', '', '', '', '', '', '', 'V-SKU-M', 'Medium', '30', '', '6', 'in_stock',
            ],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.products.import.upload'), ['file' => $file]);

        $job = ImportJob::query()->firstOrFail();
        $this->actingAs($admin, 'admin')
            ->post(route('admin.import-jobs.process', $job));

        $product = Product::query()->where('sku', 'PARENT-V-1')->firstOrFail();
        $this->assertSame('variable', $product->product_type);
        $this->assertCount(2, $product->variants);
        $this->assertTrue(ProductVariant::query()->where('sku', 'V-SKU-S')->exists());
        $this->assertTrue(ProductVariant::query()->where('sku', 'V-SKU-M')->exists());
    }

    public function test_reimport_same_sku_updates_not_duplicates(): void
    {
        $this->seedMainWarehouse();
        $admin = $this->adminForImport();
        $slug = 'imp-dup-'.uniqid();

        $makeFile = fn (string $name) => $this->makeCsvFile($name, [
            [
                'IMP-DUP-1', 'simple', $name, $slug,
                '', '', '', '', 'draft', 'visible', '10', '', '', '', 'in_stock', '0',
                '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            ],
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.products.import.upload'), ['file' => $makeFile('a.csv')]);
        $job1 = ImportJob::query()->latest('id')->firstOrFail();
        $this->actingAs($admin, 'admin')->post(route('admin.import-jobs.process', $job1));

        $id1 = Product::query()->where('sku', 'IMP-DUP-1')->value('id');

        $this->actingAs($admin, 'admin')->post(route('admin.products.import.upload'), ['file' => $makeFile('b.csv')]);
        $job2 = ImportJob::query()->latest('id')->firstOrFail();
        $this->actingAs($admin, 'admin')->post(route('admin.import-jobs.process', $job2));

        $this->assertSame(1, Product::query()->where('sku', 'IMP-DUP-1')->count());
        $this->assertSame($id1, Product::query()->where('sku', 'IMP-DUP-1')->value('id'));
        $this->assertSame('b.csv', Product::query()->find($id1)?->name);
    }
}
