<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadMediaRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $mime = (string) $request->get('mime_type', '');

        $media = Media::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where('filename', 'like', $like)
                    ->orWhere('path', 'like', $like)
                    ->orWhere('alt_text', 'like', $like);
            })
            ->when($mime !== '', fn ($query) => $query->where('mime_type', $mime))
            ->latest('id')
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'filters' => compact('q', 'mime'),
        ]);
    }

    public function create(): View
    {
        return view('admin.media.create');
    }

    public function store(UploadMediaRequest $request, MediaService $mediaService): RedirectResponse
    {
        $admin = auth('admin')->user();
        $directory = (string) ($request->validated('directory') ?? 'media');

        $media = $mediaService->uploadImage($request->file('file'), $admin, $directory);
        if ($request->filled('alt_text')) {
            $media->alt_text = (string) $request->validated('alt_text');
            $media->save();
        }

        return redirect()->route('admin.media.edit', $media)->with('success', __('admin.media_uploaded'));
    }

    public function edit(Media $media): View
    {
        return view('admin.media.edit', [
            'mediaItem' => $media,
        ]);
    }

    public function update(UpdateMediaRequest $request, Media $media): RedirectResponse
    {
        $media->fill($request->validated())->save();

        return redirect()->route('admin.media.edit', $media)->with('success', __('admin.media_updated'));
    }

    public function destroy(Media $media, MediaService $mediaService): RedirectResponse
    {
        try {
            $mediaService->deleteMedia($media);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.media.index')->withErrors(['media' => $e->getMessage()]);
        }

        return redirect()->route('admin.media.index')->with('success', __('admin.media_deleted'));
    }
}

