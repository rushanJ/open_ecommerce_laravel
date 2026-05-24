<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Models\Brand;
use App\Services\MediaService;
use App\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BrandController extends BaseAdminController
{
    public function __construct(
        protected SlugService $slugService,
        protected MediaService $mediaService,
    ) {}

    public function index(Request $request): View
    {
        $query = Brand::query()
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('q')) {
            $term = $request->input('q');
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('slug', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $brands = $query->paginate(15)->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.create', ['brand' => new Brand]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('logo_file')) {
            $media = $this->mediaService->uploadImage($request->file('logo_file'), auth('admin')->user(), 'brands');
            $data['logo_path'] = $media->path;
        }

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('brands', 'slug', $data['name']);
        }

        $brand = Brand::query()->create($data);
        $this->logAdminActivity('brands', 'create', $brand, [], $brand->only(['name', 'slug', 'status']));

        return $this->success(__('admin.brand_created'), 'admin.brands.index');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('logo_file')) {
            $media = $this->mediaService->uploadImage($request->file('logo_file'), auth('admin')->user(), 'brands');
            $data['logo_path'] = $media->path;
        }

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('brands', 'slug', $data['name'], $brand->getKey());
        }

        $before = $brand->only(['name', 'slug', 'status']);
        $brand->update($data);
        $updated = $brand->fresh();
        $this->logAdminActivity('brands', 'update', $updated, $before, $updated?->only(['name', 'slug', 'status']) ?? []);

        return $this->success(__('admin.brand_updated'), 'admin.brands.index');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $snapshot = $brand->only(['id', 'name', 'slug']);
        $brand->delete();
        $this->logAdminActivity('brands', 'delete', null, $snapshot, []);

        return $this->success(__('admin.brand_deleted'), 'admin.brands.index');
    }
}
