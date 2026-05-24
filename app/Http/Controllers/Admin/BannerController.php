<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreBannerRequest;
use App\Http\Requests\Admin\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends BaseAdminController
{
    public function __construct(
        protected MediaService $mediaService
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $position = (string) $request->get('position', '');
        $status = (string) $request->get('status', '');

        $banners = Banner::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where('title', 'like', $like)->orWhere('subtitle', 'like', $like);
            })
            ->when($position !== '', fn ($query) => $query->where('position', $position))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.banners.index', [
            'banners' => $banners,
            'filters' => compact('q', 'position', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.create', [
            'banner' => new Banner(),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('image_file')) {
            $media = $this->mediaService->uploadImage($request->file('image_file'), auth('admin')->user(), 'banners');
            $data['image_path'] = $media->path;
        }

        $banner = Banner::query()->create($data);
        $this->logAdminActivity('content', 'create_banner', $banner, [], $banner->only(['title', 'position', 'status']));

        return redirect()->route('admin.banners.edit', $banner)->with('success', __('admin.banner_created'));
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', [
            'banner' => $banner,
        ]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = $request->validated();
        if ($request->hasFile('image_file')) {
            $media = $this->mediaService->uploadImage($request->file('image_file'), auth('admin')->user(), 'banners');
            $data['image_path'] = $media->path;
        }

        $before = $banner->only(['title', 'position', 'status']);
        $banner->fill($data)->save();
        $updated = $banner->fresh();
        $this->logAdminActivity('content', 'update_banner', $updated, $before, $updated?->only(['title', 'position', 'status']) ?? []);

        return redirect()->route('admin.banners.edit', $banner)->with('success', __('admin.banner_updated'));
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $snapshot = $banner->only(['id', 'title']);
        $banner->delete();
        $this->logAdminActivity('content', 'delete_banner', null, $snapshot, []);

        return redirect()->route('admin.banners.index')->with('success', __('admin.banner_deleted'));
    }
}

