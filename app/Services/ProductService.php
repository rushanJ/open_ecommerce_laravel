<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAttributeAssignment;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantValue;
use App\Models\Media;
use App\Services\WebhookService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductService
{
    public function __construct(
        protected SlugService $slugService,
        protected MediaService $mediaService,
        protected WebhookService $webhooks,
    ) {}

    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $payload = $this->extractFillable($data);

            if ($this->isEmptySlug($payload['slug'] ?? null)) {
                $payload['slug'] = $this->slugService->unique('products', 'slug', $payload['name']);
            }

            $product = Product::query()->create($payload);
            $this->syncRelations($product, $data);

            $productId = $product->getKey();
            DB::afterCommit(function () use ($productId): void {
                $this->webhooks->dispatchSafe('product.created', ['product_id' => $productId]);
            });

            return $product->fresh();
        });
    }

    public function updateProduct(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $payload = $this->extractFillable($data);

            if ($this->isEmptySlug($payload['slug'] ?? null)) {
                $payload['slug'] = $this->slugService->unique('products', 'slug', $payload['name'], $product->getKey());
            }

            $product->update($payload);
            $this->syncRelations($product, $data);

            $productId = $product->getKey();
            DB::afterCommit(function () use ($productId): void {
                $this->webhooks->dispatchSafe('product.updated', ['product_id' => $productId]);
            });

            return $product->fresh();
        });
    }

    public function deleteProduct(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->delete();
        });
    }

    public function deleteProductImage(Product $product, ProductImage $image): void
    {
        if ((int) $image->product_id !== (int) $product->getKey()) {
            throw new RuntimeException('Image does not belong to this product.');
        }

        $path = $this->normalizePublicDiskPath($image->path);
        $wasPrimary = (bool) $image->is_primary;

        DB::transaction(function () use ($product, $image, $wasPrimary): void {
            $image->delete();

            if (! $wasPrimary) {
                return;
            }

            $replacement = ProductImage::query()
                ->where('product_id', $product->getKey())
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            if ($replacement !== null) {
                $replacement->forceFill(['is_primary' => true])->save();
            }
        });

        $this->deleteUnreferencedUploadedMedia($path);
    }

    public function syncCategories(Product $product, array $categoryIds): void
    {
        $ids = array_values(array_unique(array_filter($categoryIds, fn ($id) => $id !== null && $id !== '')));
        $product->categories()->sync($ids);
    }

    public function syncAttributeAssignments(Product $product, array $rows): void
    {
        ProductAttributeAssignment::query()->where('product_id', $product->getKey())->delete();

        foreach ($rows as $row) {
            $attributeId = $row['attribute_id'] ?? null;
            if (! $attributeId) {
                continue;
            }

            $valueId = $row['attribute_value_id'] ?? null;
            $custom = $row['custom_value'] ?? null;
            if ($valueId !== null && $valueId !== '') {
                $value = ProductAttributeValue::query()->find($valueId);
                if (! $value || (int) $value->attribute_id !== (int) $attributeId) {
                    continue;
                }
            } else {
                $valueId = null;
            }

            if ($valueId === null && ($custom === null || $custom === '')) {
                continue;
            }

            ProductAttributeAssignment::query()->create([
                'product_id' => $product->getKey(),
                'attribute_id' => $attributeId,
                'attribute_value_id' => $valueId ?: null,
                'custom_value' => ($custom !== null && $custom !== '') ? (string) $custom : null,
            ]);
        }
    }

    public function syncImages(Product $product, array $rows): void
    {
        ProductImage::query()->where('product_id', $product->getKey())->delete();

        $normalized = [];
        foreach ($rows as $row) {
            $path = $row['path'] ?? null;
            if ($path === null || trim((string) $path) === '') {
                continue;
            }
            $normalized[] = [
                'path' => trim((string) $path),
                'alt_text' => isset($row['alt_text']) && $row['alt_text'] !== '' ? (string) $row['alt_text'] : null,
                'sort_order' => (int) ($row['sort_order'] ?? 0),
                'is_primary' => filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        if ($normalized === []) {
            return;
        }

        $primaryCount = 0;
        foreach ($normalized as $img) {
            if ($img['is_primary']) {
                $primaryCount++;
            }
        }
        if ($primaryCount === 0) {
            $normalized[0]['is_primary'] = true;
        }
        if ($primaryCount > 1) {
            $first = true;
            foreach ($normalized as $k => $img) {
                if ($img['is_primary'] && $first) {
                    $first = false;
                } elseif ($img['is_primary']) {
                    $normalized[$k]['is_primary'] = false;
                }
            }
        }

        $order = 0;
        foreach ($normalized as $img) {
            ProductImage::query()->create([
                'product_id' => $product->getKey(),
                'variant_id' => null,
                'path' => $img['path'],
                'alt_text' => $img['alt_text'],
                'sort_order' => $img['sort_order'] ?? $order,
                'is_primary' => $img['is_primary'],
            ]);
            $order++;
        }
    }

    public function syncVariants(Product $product, array $rows): void
    {
        ProductVariant::query()->where('product_id', $product->getKey())->orderBy('id')->get()->each->forceDelete();

        foreach ($rows as $row) {
            if (! $this->variantRowIsMeaningful($row)) {
                continue;
            }

            $variant = ProductVariant::query()->create([
                'product_id' => $product->getKey(),
                'sku' => $this->emptyToNull($row['sku'] ?? null),
                'barcode' => $this->emptyToNull($row['barcode'] ?? null),
                'name' => $this->emptyToNull($row['name'] ?? null),
                'regular_price' => $row['regular_price'] ?? 0,
                'sale_price' => $this->emptyToNull($row['sale_price'] ?? null),
                'cost_price' => $this->emptyToNull($row['cost_price'] ?? null),
                'stock_quantity' => $this->emptyToNull($row['stock_quantity'] ?? null),
                'stock_status' => $row['stock_status'] ?? 'in_stock',
                'weight' => $this->emptyToNull($row['weight'] ?? null),
                'image_path' => $this->emptyToNull($row['image_path'] ?? null),
                'status' => $row['status'] ?? 'active',
                'metadata' => null,
            ]);

            foreach ($row['attribute_values'] ?? [] as $pivot) {
                $aid = $pivot['attribute_id'] ?? null;
                $vid = $pivot['attribute_value_id'] ?? null;
                if (! $aid || ! $vid) {
                    continue;
                }
                $value = ProductAttributeValue::query()->find($vid);
                if (! $value || (int) $value->attribute_id !== (int) $aid) {
                    continue;
                }
                ProductVariantValue::query()->create([
                    'variant_id' => $variant->getKey(),
                    'attribute_id' => $aid,
                    'attribute_value_id' => $vid,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function extractFillable(array $data): array
    {
        $product = new Product;

        return Arr::only($data, $product->getFillable());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncRelations(Product $product, array $data): void
    {
        $this->syncCategories($product, $data['category_ids'] ?? []);
        $this->syncAttributeAssignments($product, $data['attributes'] ?? []);
        $this->syncImages($product, $data['images'] ?? []);
        $this->appendUploadedImages($product, $data['uploaded_images'] ?? []);
        $this->syncVariants($product, $data['variants'] ?? []);
    }

    /**
     * Append uploaded images (do not delete existing images in this stage).
     *
     * @param  array<int, mixed>  $files
     */
    private function appendUploadedImages(Product $product, array $files): void
    {
        $uploads = array_values(array_filter($files, fn ($f) => $f instanceof \Illuminate\Http\UploadedFile));
        if ($uploads === []) {
            return;
        }

        $primary = ProductImage::query()
            ->where('product_id', $product->getKey())
            ->where('is_primary', true)
            ->first();
        $hasPrimary = $primary !== null
            && ! $this->isLegacyCatalogPath($primary->path)
            && $this->publicDiskFileExists($primary->path);

        if (! $hasPrimary) {
            ProductImage::query()
                ->where('product_id', $product->getKey())
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        $maxSort = (int) (ProductImage::query()->where('product_id', $product->getKey())->max('sort_order') ?? 0);
        $sort = $maxSort + 1;

        foreach ($uploads as $i => $file) {
            $media = $this->mediaService->uploadImage($file, auth('admin')->user(), 'products');
            ProductImage::query()->create([
                'product_id' => $product->getKey(),
                'variant_id' => null,
                'path' => $media->path,
                'alt_text' => null,
                'sort_order' => $sort++,
                'is_primary' => ! $hasPrimary && $i === 0,
            ]);
        }
    }

    private function publicDiskFileExists(?string $path): bool
    {
        $path = $this->normalizePublicDiskPath($path);

        return $path !== null && Storage::disk('public')->exists($path);
    }

    private function normalizePublicDiskPath(?string $path): ?string
    {
        $path = $path !== null ? trim(str_replace('\\', '/', $path)) : null;
        if ($path === null || $path === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'public/storage/')) {
            return Str::after($path, 'public/storage/');
        }

        if (Str::startsWith($path, 'storage/')) {
            return Str::after($path, 'storage/');
        }

        return $path;
    }

    private function isLegacyCatalogPath(?string $path): bool
    {
        $path = $this->normalizePublicDiskPath($path);

        return $path !== null && Str::startsWith($path, 'catalog/');
    }

    private function deleteUnreferencedUploadedMedia(?string $path): void
    {
        if ($path === null || $path === '' || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        if (ProductImage::query()->where('path', $path)->exists()) {
            return;
        }

        $media = Media::query()
            ->where('disk', 'public')
            ->where('path', $path)
            ->first();

        if ($media !== null) {
            try {
                $this->mediaService->deleteMedia($media);
            } catch (RuntimeException) {
                // If another content type still references it, only detach it from this product.
            }

            return;
        }

        if (Str::startsWith($path, 'open_ecommerce_laravel/products/')) {
            Storage::disk('public')->delete($path);
        }
    }

    private function variantRowIsMeaningful(array $row): bool
    {
        if ($this->filledString($row['sku'] ?? null) || $this->filledString($row['name'] ?? null)) {
            return true;
        }
        $rp = $row['regular_price'] ?? null;
        if ($rp !== null && $rp !== '' && is_numeric($rp)) {
            return true;
        }
        foreach ($row['attribute_values'] ?? [] as $p) {
            if (! empty($p['attribute_id']) && ! empty($p['attribute_value_id'])) {
                return true;
            }
        }

        return false;
    }

    private function filledString(mixed $v): bool
    {
        return is_string($v) && trim($v) !== '';
    }

    private function emptyToNull(mixed $v): mixed
    {
        if ($v === null || $v === '') {
            return null;
        }

        return $v;
    }

    private function isEmptySlug(mixed $slug): bool
    {
        return $slug === null || $slug === '';
    }
}
