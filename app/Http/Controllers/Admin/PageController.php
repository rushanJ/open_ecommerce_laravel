<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends BaseAdminController
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');

        $pages = Page::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where('title', 'like', $like)->orWhere('slug', 'like', $like);
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pages.index', [
            'pages' => $pages,
            'filters' => compact('q', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.create', [
            'page' => new Page(),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['title']);
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $page = Page::query()->create($data);
        $this->logAdminActivity('content', 'create_page', $page, [], $page->only(['title', 'slug', 'status']));

        return redirect()->route('admin.pages.edit', $page)->with('success', __('admin.page_created'));
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', [
            'page' => $page,
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['title']);
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $before = $page->only(['title', 'slug', 'status']);
        $page->fill($data)->save();
        $updated = $page->fresh();
        $this->logAdminActivity('content', 'update_page', $updated, $before, $updated?->only(['title', 'slug', 'status']) ?? []);

        return redirect()->route('admin.pages.edit', $page)->with('success', __('admin.page_updated'));
    }

    public function destroy(Page $page): RedirectResponse
    {
        $snapshot = $page->only(['id', 'title', 'slug']);
        $page->delete();
        $this->logAdminActivity('content', 'delete_page', null, $snapshot, []);

        return redirect()->route('admin.pages.index')->with('success', __('admin.page_deleted'));
    }
}

