<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaService
{
    private const array ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    private const int MAX_BYTES = 5 * 1024 * 1024;

    /** @var list<string> */
    private const array ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function uploadImage(UploadedFile $file, ?AdminUser $admin = null, string $directory = 'media'): Media
    {
        $mime = (string) ($file->getMimeType() ?? '');
        if (! in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new RuntimeException('Unsupported file type.');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        if ($ext === '' || ! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('Unsupported file extension.');
        }

        $size = (int) $file->getSize();
        if ($size <= 0 || $size > self::MAX_BYTES) {
            throw new RuntimeException('File too large.');
        }

        $directory = trim($directory);
        if ($directory === '') {
            $directory = 'media';
        }
        $directory = Str::of($directory)->lower()->replaceMatches('/[^a-z0-9\-_]/', '')->toString();
        if ($directory === '') {
            $directory = 'media';
        }

        $now = now();
        $folder = 'open_ecommerce_laravel/'.$directory.'/'.$now->format('Y').'/'.$now->format('m');

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($originalName);
        if ($slug === '') {
            $slug = 'image';
        }

        $filename = $slug.'-'.Str::lower(Str::random(10)).'.'.$ext;

        $path = $file->storeAs($folder, $filename, ['disk' => 'public']);
        if (! is_string($path) || $path === '') {
            throw new RuntimeException('Failed to store file.');
        }

        return DB::transaction(function () use ($file, $admin, $mime, $size, $path): Media {
            return Media::query()->create([
                'disk' => 'public',
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size' => $size,
                'alt_text' => null,
                'uploaded_by_admin_id' => $admin?->getKey(),
            ]);
        });
    }

    public function deleteMedia(Media $media): void
    {
        if ($this->isReferenced($media->path)) {
            throw new RuntimeException(__('admin.media_delete_blocked'));
        }

        $disk = $media->disk ?: 'public';
        $path = $media->path;

        DB::transaction(function () use ($media): void {
            $media->delete();
        });

        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }

    public function getUrl(Media|string|null $mediaOrPath): ?string
    {
        if ($mediaOrPath === null) {
            return null;
        }

        if ($mediaOrPath instanceof Media) {
            return $mediaOrPath->url;
        }

        $path = trim((string) $mediaOrPath);
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function isReferenced(?string $path): bool
    {
        if ($path === null || trim($path) === '') {
            return false;
        }

        return Product::query()->where('digital_file_path', $path)->exists()
            || \App\Models\ProductImage::query()->where('path', $path)->exists()
            || Category::query()->where('image_path', $path)->orWhere('banner_path', $path)->exists()
            || Brand::query()->where('logo_path', $path)->exists()
            || Banner::query()->where('image_path', $path)->exists();
    }
}

