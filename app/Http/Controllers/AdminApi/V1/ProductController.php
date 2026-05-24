<?php

namespace App\Http\Controllers\AdminApi\V1;

use App\Http\Resources\AdminApi\V1\AdminProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends AdminApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $paginator = Product::query()
            ->with(['brand', 'categories', 'primaryImage'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('name', 'like', '%'.$q.'%')
                        ->orWhere('sku', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate((int) $request->query('per_page', 25))
            ->withQueryString();

        return $this->paginated($paginator, AdminProductResource::class);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $product->load(['brand', 'categories', 'images', 'variants', 'primaryImage']);

        return $this->success((new AdminProductResource($product))->resolve($request));
    }
}
