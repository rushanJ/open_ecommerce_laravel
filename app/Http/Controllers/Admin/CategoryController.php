<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\MediaService;
use App\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseAdminController
{
    public function __construct(
        protected SlugService $slugService,
        protected MediaService $mediaService,
    ) {}

    public function index(Request $request): View
    {
        $query = Category::query()
            ->with('parent')
            ->orderBy('parent_id')
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

        $categories = $query->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parentChoices = Category::query()->orderBy('name')->get();

        return view('admin.categories.create', [
            'category' => new Category,
            'parentChoices' => $parentChoices,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('image_file')) {
            $media = $this->mediaService->uploadImage($request->file('image_file'), auth('admin')->user(), 'categories');
            $data['image_path'] = $media->path;
        }
        if ($request->hasFile('banner_file')) {
            $media = $this->mediaService->uploadImage($request->file('banner_file'), auth('admin')->user(), 'categories');
            $data['banner_path'] = $media->path;
        }

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('categories', 'slug', $data['name']);
        }

        $category = Category::query()->create($data);
        $this->logAdminActivity('categories', 'create', $category, [], $category->only(['name', 'slug', 'status']));

        return $this->success(__('admin.category_created'), 'admin.categories.index');
    }

    public function edit(Category $category): View
    {
        $excludeIds = array_merge([(int) $category->getKey()], Category::descendantIdsFor($category));
        $parentChoices = Category::query()
            ->orderBy('name')
            ->get()
            ->reject(fn (Category $c) => in_array((int) $c->getKey(), $excludeIds, true))
            ->values();

        return view('admin.categories.edit', compact('category', 'parentChoices'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('image_file')) {
            $media = $this->mediaService->uploadImage($request->file('image_file'), auth('admin')->user(), 'categories');
            $data['image_path'] = $media->path;
        }
        if ($request->hasFile('banner_file')) {
            $media = $this->mediaService->uploadImage($request->file('banner_file'), auth('admin')->user(), 'categories');
            $data['banner_path'] = $media->path;
        }

        if ($data['slug'] === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('categories', 'slug', $data['name'], $category->getKey());
        }

        $before = $category->only(['name', 'slug', 'status']);
        $category->update($data);
        $updated = $category->fresh();
        $this->logAdminActivity('categories', 'update', $updated, $before, $updated?->only(['name', 'slug', 'status']) ?? []);

        return $this->success(__('admin.category_updated'), 'admin.categories.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return $this->backWithError(__('admin.category_has_children'));
        }

        $snapshot = $category->only(['id', 'name', 'slug']);
        $category->delete();
        $this->logAdminActivity('categories', 'delete', null, $snapshot, []);

        return $this->success(__('admin.category_deleted'), 'admin.categories.index');
    }
}
