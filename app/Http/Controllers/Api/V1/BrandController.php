<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends ApiController
{
    public function index(): JsonResponse
    {
        $brands = Brand::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success(BrandResource::collection($brands)->resolve());
    }

    public function show(string $slug): JsonResponse
    {
        $brand = Brand::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->success((new BrandResource($brand))->resolve());
    }
}
