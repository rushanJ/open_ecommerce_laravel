<?php

namespace App\Services\ImportExport;

use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ImportJob;
use App\Models\ImportJobRow;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\TaxClass;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\SlugService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ProductImportService
{
    /** @var list<string> */
    public const REQUIRED_HEADERS = [
        'sku',
        'product_type',
        'name',
        'regular_price',
    ];

    public function __construct(
        protected SlugService $slugService,
        protected InventoryService $inventoryService,
    ) {}

    public function createJob(UploadedFile $file, AdminUser $admin): ImportJob
    {
        $job = ImportJob::query()->create([
            'type' => 'product_import',
            'filename' => $file->getClientOriginalName(),
            'status' => 'pending',
            'created_by_admin_id' => $admin->getKey(),
        ]);

        $file->storeAs('import-jobs', $job->id.'.csv', 'local');

        return $job;
    }

    public function parseAndValidate(ImportJob $job): ImportJob
    {
        if ($job->type !== 'product_import') {
            throw new InvalidArgumentException('Unsupported import job type.');
        }

        $path = $this->csvPath($job);
        if (! is_readable($path)) {
            $job->forceFill([
                'status' => 'failed',
                'errors' => ['Unable to read uploaded CSV file.'],
                'completed_at' => now(),
            ])->save();

            return $job;
        }

        $job->forceFill(['status' => 'validating', 'errors' => null])->save();
        $job->rows()->delete();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            $job->forceFill([
                'status' => 'failed',
                'errors' => ['Could not open CSV.'],
                'completed_at' => now(),
            ])->save();

            return $job;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            $job->forceFill([
                'status' => 'failed',
                'errors' => ['CSV is empty.'],
                'completed_at' => now(),
            ])->save();

            return $job;
        }

        if (str_starts_with($firstLine, "\xEF\xBB\xBF")) {
            $firstLine = substr($firstLine, 3);
        }

        $headers = str_getcsv($firstLine);
        $headers = array_map(fn (string $h): string => strtolower(trim($h)), $headers);

        $missing = array_values(array_diff(self::REQUIRED_HEADERS, $headers));
        if ($missing !== []) {
            fclose($handle);
            $job->forceFill([
                'status' => 'failed',
                'errors' => ['Missing required columns: '.implode(', ', $missing)],
                'total_rows' => 0,
                'completed_at' => now(),
            ])->save();

            return $job;
        }

        $rowNumber = 1;
        $dataRowIndex = 0;
        $anyInvalid = false;
        $summaryErrors = [];

        while (($cells = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if ($this->csvLineIsEmpty($cells)) {
                continue;
            }
            $dataRowIndex++;

            $assoc = $this->combineRow($headers, $cells);
            $errors = $this->validateRow($assoc, $rowNumber);

            if ($errors !== []) {
                $anyInvalid = true;
                if (count($summaryErrors) < 20) {
                    $summaryErrors[] = 'Row '.$rowNumber.': '.implode('; ', $errors);
                }
            }

            ImportJobRow::query()->create([
                'import_job_id' => $job->getKey(),
                'row_number' => $rowNumber,
                'raw_data' => $assoc,
                'status' => $errors === [] ? 'valid' : 'invalid',
                'errors' => $errors === [] ? null : $errors,
            ]);
        }

        fclose($handle);

        $job->forceFill([
            'total_rows' => $dataRowIndex,
            'success_rows' => 0,
            'failed_rows' => 0,
            'status' => $anyInvalid ? 'failed' : 'validated',
            'errors' => $anyInvalid ? $summaryErrors : null,
            'completed_at' => null,
        ])->save();

        return $job;
    }

    public function process(ImportJob $job): ImportJob
    {
        if (! $job->canProcess()) {
            throw new InvalidArgumentException('Import job cannot be processed in its current state.');
        }

        $job->forceFill([
            'status' => 'processing',
            'started_at' => now(),
            'success_rows' => 0,
            'failed_rows' => 0,
        ])->save();

        $success = 0;
        $failed = 0;

        foreach ($job->rows()->where('status', 'valid')->orderBy('row_number')->cursor() as $row) {
            try {
                DB::transaction(function () use ($row, $job): void {
                    $this->importRow($row, $job);
                });
                $success++;
            } catch (Throwable $e) {
                $failed++;
                Log::error('product.import.row_failed', [
                    'import_job_id' => $job->getKey(),
                    'row_number' => $row->row_number,
                    'message' => $e->getMessage(),
                ]);
                $row->forceFill([
                    'status' => 'failed',
                    'errors' => [$e->getMessage()],
                ])->save();
            }
        }

        $job->forceFill([
            'status' => 'completed',
            'success_rows' => $success,
            'failed_rows' => $failed,
            'completed_at' => now(),
            'errors' => $failed > 0 ? ['Some rows failed during import. Check row details.'] : null,
        ])->save();

        return $job->fresh();
    }

    /**
     * @param  array<string, string|null>  $row
     * @return list<string>
     */
    public function validateRow(array $row, int $rowNumber): array
    {
        $errors = [];

        $type = strtolower(trim((string) ($row['product_type'] ?? '')));
        if ($type === '') {
            $errors[] = 'product_type is required.';
        } elseif (! in_array($type, ['simple', 'variable'], true)) {
            $errors[] = 'product_type must be simple or variable.';
        }

        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            $errors[] = 'name is required.';
        }

        $priceRaw = $row['regular_price'] ?? null;
        if ($priceRaw === null || trim((string) $priceRaw) === '') {
            $errors[] = 'regular_price is required.';
        } elseif (! is_numeric(trim((string) $priceRaw))) {
            $errors[] = 'regular_price must be numeric.';
        }

        $parentSku = trim((string) ($row['sku'] ?? ''));
        $variantSku = trim((string) ($row['variant_sku'] ?? ''));

        if ($type === 'simple') {
            if ($parentSku === '') {
                $errors[] = 'sku is required for simple products.';
            }
            if ($variantSku !== '') {
                $errors[] = 'variant_sku must be empty for simple products.';
            }
        }

        if ($type === 'variable') {
            if ($parentSku === '') {
                $errors[] = 'sku (parent) is required for variable products.';
            }
            if ($variantSku === '') {
                $errors[] = 'variant_sku is required for variable product rows.';
            }
        }

        $status = trim((string) ($row['status'] ?? 'draft'));
        if ($status !== '' && ! in_array($status, ['draft', 'active', 'inactive'], true)) {
            $errors[] = 'Invalid status.';
        }

        $visibility = trim((string) ($row['visibility'] ?? 'visible'));
        if ($visibility !== '' && ! in_array($visibility, ['visible', 'hidden', 'catalog'], true)) {
            $errors[] = 'Invalid visibility.';
        }

        $stockStatus = trim((string) ($row['stock_status'] ?? 'in_stock'));
        if ($stockStatus !== '' && ! in_array($stockStatus, ['in_stock', 'out_of_stock', 'on_backorder'], true)) {
            $errors[] = 'Invalid stock_status.';
        }

        $vss = trim((string) ($row['variant_stock_status'] ?? ''));
        if ($vss !== '' && ! in_array($vss, ['in_stock', 'out_of_stock', 'on_backorder'], true)) {
            $errors[] = 'Invalid variant_stock_status.';
        }

        return $errors;
    }

    /**
     * @throws Throwable
     */
    public function importRow(ImportJobRow $row, ImportJob $job): void
    {
        $data = $row->raw_data ?? [];
        $type = strtolower(trim((string) ($data['product_type'] ?? '')));
        $adminId = $job->created_by_admin_id;

        if ($type === 'simple') {
            $product = $this->upsertSimpleProduct($data, $job);
            $row->forceFill([
                'status' => 'imported',
                'created_record_type' => 'product',
                'created_record_id' => $product->getKey(),
            ])->save();

            return;
        }

        if ($type === 'variable') {
            $product = $this->upsertVariableParent($data);
            $variant = $this->upsertVariant($product, $data, $job);
            $row->forceFill([
                'status' => 'imported',
                'created_record_type' => 'product_variant',
                'created_record_id' => $variant->getKey(),
            ])->save();

            return;
        }

        throw new InvalidArgumentException('Unsupported product_type.');
    }

    /**
     * @param  array<string, string|null>  $data
     */
    protected function upsertSimpleProduct(array $data, ImportJob $job): Product
    {
        $sku = trim((string) ($data['sku'] ?? ''));
        $product = Product::query()->where('sku', $sku)->first();

        $brandId = $this->resolveBrandId($data['brand'] ?? null);
        $taxClassId = $this->resolveTaxClassId($data['tax_class'] ?? null);
        $categoryIds = $this->resolveCategoryIds($data['categories'] ?? null);

        $payload = $this->productPayloadFromRow($data, $product === null);
        $payload['product_type'] = 'simple';
        $payload['sku'] = $sku;
        $payload['brand_id'] = $brandId;
        $payload['tax_class_id'] = $taxClassId;

        if ($product === null) {
            $payload['slug'] = $this->resolveSlug($data, $payload['name']);
            $payload['published_at'] = ($payload['status'] ?? 'draft') === 'active' ? now() : null;
            $product = Product::query()->create($payload);
        } else {
            if (! empty(trim((string) ($data['slug'] ?? '')))) {
                $payload['slug'] = $this->uniqueProductSlug(trim((string) $data['slug']), $product->getKey());
            }
            if (($payload['status'] ?? $product->status) === 'active' && $product->published_at === null) {
                $payload['published_at'] = now();
            }
            $product->update($payload);
            $product = $product->fresh();
        }

        if ($categoryIds !== []) {
            $product->categories()->sync($categoryIds);
        }

        $this->applySimpleStock($product, $data, $job);

        return $product->fresh();
    }

    /**
     * @param  array<string, string|null>  $data
     */
    protected function upsertVariableParent(array $data): Product
    {
        $parentSku = trim((string) ($data['sku'] ?? ''));
        $product = Product::query()->where('sku', $parentSku)->first();

        $brandId = $this->resolveBrandId($data['brand'] ?? null);
        $taxClassId = $this->resolveTaxClassId($data['tax_class'] ?? null);
        $categoryIds = $this->resolveCategoryIds($data['categories'] ?? null);

        $payload = $this->productPayloadFromRow($data, $product === null);
        $payload['product_type'] = 'variable';
        $payload['sku'] = $parentSku;
        $payload['brand_id'] = $brandId;
        $payload['tax_class_id'] = $taxClassId;

        if ($product === null) {
            $payload['slug'] = $this->resolveSlug($data, $payload['name']);
            $payload['published_at'] = ($payload['status'] ?? 'draft') === 'active' ? now() : null;
            $product = Product::query()->create($payload);
        } else {
            if ($product->product_type !== 'variable') {
                throw new InvalidArgumentException('Existing product with parent SKU is not variable.');
            }
            if (! empty(trim((string) ($data['slug'] ?? '')))) {
                $payload['slug'] = $this->uniqueProductSlug(trim((string) $data['slug']), $product->getKey());
            }
            if (($payload['status'] ?? $product->status) === 'active' && $product->published_at === null) {
                $payload['published_at'] = now();
            }
            $product->update($payload);
            $product = $product->fresh();
        }

        if ($categoryIds !== []) {
            $product->categories()->sync($categoryIds);
        }

        return $product->fresh();
    }

    /**
     * @param  array<string, string|null>  $data
     */
    protected function upsertVariant(Product $product, array $data, ImportJob $job): ProductVariant
    {
        $variantSku = trim((string) ($data['variant_sku'] ?? ''));

        $variant = ProductVariant::query()
            ->where('product_id', $product->getKey())
            ->where('sku', $variantSku)
            ->first();

        $vName = trim((string) ($data['variant_name'] ?? '')) ?: null;
        $vRegular = $this->decimalOrDefault($data['variant_regular_price'] ?? null, $data['regular_price'] ?? '0');
        $vSale = $this->nullableDecimal($data['variant_sale_price'] ?? null);
        $vStockQty = $this->nullableDecimal($data['variant_stock_quantity'] ?? null);
        $vStockStatus = trim((string) ($data['variant_stock_status'] ?? $data['stock_status'] ?? 'in_stock')) ?: 'in_stock';

        $variantPayload = [
            'sku' => $variantSku,
            'name' => $vName,
            'regular_price' => $vRegular,
            'sale_price' => $vSale,
            'stock_quantity' => $vStockQty,
            'stock_status' => $vStockStatus,
            'status' => 'active',
        ];

        if ($variant === null) {
            $variantPayload['product_id'] = $product->getKey();
            $variant = ProductVariant::query()->create($variantPayload);
        } else {
            $variant->update($variantPayload);
            $variant = $variant->fresh();
        }

        $warehouse = $this->resolveMainWarehouse();
        if ($warehouse !== null && $vStockQty !== null && $data['variant_stock_quantity'] !== null && trim((string) $data['variant_stock_quantity']) !== '') {
            $product->forceFill(['manage_stock' => true])->save();
            $this->setAbsoluteStock($warehouse, $product, $variant, (float) $vStockQty, $job);
        }

        $this->inventoryService->syncProductStockFromInventory($product->fresh(['variants']));

        return $variant->fresh();
    }

    /**
     * @param  array<string, string|null>  $data
     * @return array<string, mixed>
     */
    protected function productPayloadFromRow(array $data, bool $isCreate): array
    {
        $status = trim((string) ($data['status'] ?? 'draft')) ?: 'draft';
        $visibility = trim((string) ($data['visibility'] ?? 'visible')) ?: 'visible';
        $stockStatus = trim((string) ($data['stock_status'] ?? 'in_stock')) ?: 'in_stock';

        $manageStock = $this->parseBool($data['manage_stock'] ?? null);
        if (isset($data['stock_quantity']) && trim((string) $data['stock_quantity']) !== '') {
            $manageStock = true;
        }

        return [
            'name' => trim((string) ($data['name'] ?? '')),
            'short_description' => $this->nullIfEmpty($data['short_description'] ?? null),
            'description' => $this->nullIfEmpty($data['description'] ?? null),
            'status' => $status,
            'visibility' => $visibility,
            'regular_price' => $this->decimalOrDefault($data['regular_price'] ?? null, '0'),
            'sale_price' => $this->nullableDecimal($data['sale_price'] ?? null),
            'cost_price' => $this->nullableDecimal($data['cost_price'] ?? null),
            'stock_quantity' => $this->nullableDecimal($data['stock_quantity'] ?? null),
            'stock_status' => $stockStatus,
            'manage_stock' => $manageStock,
            'weight' => $this->nullableDecimal($data['weight'] ?? null),
            'length' => $this->nullableDecimal($data['length'] ?? null),
            'width' => $this->nullableDecimal($data['width'] ?? null),
            'height' => $this->nullableDecimal($data['height'] ?? null),
            'meta_title' => $this->nullIfEmpty($data['meta_title'] ?? null),
            'meta_description' => $this->nullIfEmpty($data['meta_description'] ?? null),
        ];
    }

    /**
     * @param  array<string, string|null>  $data
     */
    protected function applySimpleStock(Product $product, array $data, ImportJob $job): void
    {
        $raw = $data['stock_quantity'] ?? null;
        if ($raw === null || trim((string) $raw) === '') {
            $this->inventoryService->syncProductStockFromInventory($product->fresh());

            return;
        }

        $product->forceFill(['manage_stock' => true])->save();
        $warehouse = $this->resolveMainWarehouse();
        if ($warehouse === null) {
            return;
        }

        $this->setAbsoluteStock($warehouse, $product, null, (float) $this->decimalOrDefault($raw, '0'), $job);
        $this->inventoryService->syncProductStockFromInventory($product->fresh());
    }

    protected function setAbsoluteStock(Warehouse $warehouse, Product $product, ?ProductVariant $variant, float $targetQty, ImportJob $job): void
    {
        $this->inventoryService->ensureStockRecord($warehouse, $product, $variant);
        $stock = InventoryStock::query()
            ->where('warehouse_id', $warehouse->getKey())
            ->where('product_id', $product->getKey())
            ->when($variant !== null,
                fn ($q) => $q->where('variant_id', $variant->getKey()),
                fn ($q) => $q->whereNull('variant_id'))
            ->first();

        if ($stock === null) {
            return;
        }

        $current = (float) $stock->quantity;
        $delta = $targetQty - $current;
        if (abs($delta) < 0.00001) {
            return;
        }

        $this->inventoryService->adjustStock(
            $warehouse,
            $product,
            $variant,
            $delta,
            'adjustment',
            'CSV product import (job '.$job->getKey().')',
            $job->created_by_admin_id,
            'import_job',
            $job->getKey(),
        );
    }

    protected function resolveMainWarehouse(): ?Warehouse
    {
        $w = Warehouse::query()
            ->where('status', 'active')
            ->where('name', 'Main Warehouse')
            ->orderBy('id')
            ->first();

        if ($w !== null) {
            return $w;
        }

        return Warehouse::query()->where('status', 'active')->orderBy('id')->first();
    }

    protected function resolveBrandId(?string $name): ?int
    {
        $name = $name !== null ? trim($name) : '';
        if ($name === '') {
            return null;
        }

        $brand = Brand::query()->whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
        if ($brand !== null) {
            return (int) $brand->getKey();
        }

        $slug = $this->slugService->unique('brands', 'slug', $name);
        $brand = Brand::query()->create([
            'name' => $name,
            'slug' => $slug,
            'status' => 'active',
            'sort_order' => 0,
        ]);

        return (int) $brand->getKey();
    }

    /**
     * @return list<int>
     */
    protected function resolveCategoryIds(?string $pipeSeparated): array
    {
        $pipeSeparated = $pipeSeparated !== null ? trim($pipeSeparated) : '';
        if ($pipeSeparated === '') {
            return [];
        }

        $ids = [];
        foreach (explode('|', $pipeSeparated) as $part) {
            $name = trim($part);
            if ($name === '') {
                continue;
            }

            $cat = Category::query()->whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
            if ($cat === null) {
                $slug = $this->slugService->unique('categories', 'slug', $name);
                $cat = Category::query()->create([
                    'parent_id' => null,
                    'name' => $name,
                    'slug' => $slug,
                    'status' => 'active',
                    'sort_order' => 0,
                ]);
            }
            $ids[] = (int) $cat->getKey();
        }

        return array_values(array_unique($ids));
    }

    protected function resolveTaxClassId(?string $label): ?int
    {
        $label = $label !== null ? trim($label) : '';
        if ($label === '') {
            $tax = TaxClass::query()->firstOrCreate(
                ['slug' => 'standard'],
                ['name' => 'Standard', 'description' => null],
            );

            return (int) $tax->getKey();
        }

        $tax = TaxClass::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($label)])
            ->first();

        if ($tax !== null) {
            return (int) $tax->getKey();
        }

        $slug = $this->slugService->unique('tax_classes', 'slug', $label);

        $tax = TaxClass::query()->create([
            'name' => $label,
            'slug' => $slug,
            'description' => null,
        ]);

        return (int) $tax->getKey();
    }

    /**
     * @param  array<string, string|null>  $data
     */
    protected function resolveSlug(array $data, string $name): string
    {
        $slugInput = trim((string) ($data['slug'] ?? ''));
        if ($slugInput !== '') {
            return $this->uniqueProductSlug($slugInput, null);
        }

        return $this->slugService->unique('products', 'slug', $name);
    }

    protected function uniqueProductSlug(string $slug, ?int $ignoreId): string
    {
        $base = Str::slug($slug);
        if ($base === '') {
            $base = 'product';
        }

        $candidate = $base;
        $n = 2;
        while (Product::query()->where('slug', $candidate)->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $candidate = $base.'-'.$n;
            $n++;
        }

        return $candidate;
    }

    protected function nullIfEmpty(?string $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $t = trim($v);

        return $t === '' ? null : $t;
    }

    protected function parseBool(?string $v): bool
    {
        if ($v === null) {
            return false;
        }

        return in_array(strtolower(trim($v)), ['1', 'true', 'yes', 'on'], true);
    }

    protected function decimalOrDefault(?string $v, string $default): string
    {
        if ($v === null || trim($v) === '') {
            return number_format((float) $default, 4, '.', '');
        }

        return number_format((float) $v, 4, '.', '');
    }

    protected function nullableDecimal(?string $v): ?string
    {
        if ($v === null || trim($v) === '') {
            return null;
        }

        return number_format((float) $v, 4, '.', '');
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string|null>  $cells
     * @return array<string, string|null>
     */
    protected function combineRow(array $headers, array $cells): array
    {
        $out = [];
        $count = count($headers);
        for ($i = 0; $i < $count; $i++) {
            $out[$headers[$i]] = isset($cells[$i]) ? (is_string($cells[$i]) ? $cells[$i] : (string) $cells[$i]) : '';
        }

        return $out;
    }

    /**
     * @param  list<string|null>  $cells
     */
    protected function csvLineIsEmpty(array $cells): bool
    {
        foreach ($cells as $c) {
            if ($c !== null && trim((string) $c) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function csvPath(ImportJob $job): string
    {
        return Storage::disk('local')->path('import-jobs/'.$job->id.'.csv');
    }
}
