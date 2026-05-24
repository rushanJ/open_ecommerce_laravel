@php
    /** @var \App\Models\Product $product */
    $imageRows = old('images', null);
    if ($imageRows === null && $product->exists && $product->relationLoaded('images')) {
        $imageRows = $product->images->map(fn ($img) => [
            'id' => $img->getKey(),
            'path' => $img->path,
            'alt_text' => $img->alt_text,
            'sort_order' => $img->sort_order,
            'is_primary' => $img->is_primary,
        ])->values()->all();
    }
    if (! is_array($imageRows)) {
        $imageRows = [];
    }
    while (count($imageRows) < 5) {
        $imageRows[] = ['id' => null, 'path' => '', 'alt_text' => '', 'sort_order' => count($imageRows), 'is_primary' => false];
    }
    $imageRows = array_slice($imageRows, 0, 5);
@endphp

<div class="space-y-6">
    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('admin.upload_images') }}</p>
        <input type="file" name="uploaded_images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif"
               class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-950" />
        @error('uploaded_images') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
        @error('uploaded_images.*') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
        <p class="mt-2 text-xs text-gray-500">{{ __('admin.manual_image_paths') }}</p>
    </div>

    @foreach ($imageRows as $i => $row)
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('admin.images') }} #{{ $i + 1 }}</p>
                @if (! empty($row['id']))
                    <x-admin.button
                        type="submit"
                        variant="danger"
                        size="sm"
                        form="delete-product-image-{{ $row['id'] }}"
                        onclick="return confirm(@js(__('admin.delete_warning')))"
                    >
                        {{ __('admin.delete') }}
                    </x-admin.button>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-[10rem_1fr]">
                <div>
                    @if (! empty($row['path']))
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                            <img
                                src="{{ media_url($row['path']) }}"
                                alt="{{ $row['alt_text'] ?? '' }}"
                                class="aspect-square w-full object-cover"
                                loading="lazy"
                            >
                        </div>
                        <p class="mt-2 break-all text-xs text-gray-500">{{ media_public_path($row['path']) }}</p>
                    @else
                        <div class="flex aspect-square items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-400 dark:border-gray-700 dark:bg-gray-800">
                            {{ __('admin.no_image') }}
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    <x-admin.input name="images[{{ $i }}][path]" :label="__('admin.image_path')" :value="$row['path'] ?? ''" />
                    <x-admin.input name="images[{{ $i }}][alt_text]" :label="__('admin.alt_text')" :value="$row['alt_text'] ?? ''" />
                    <x-admin.input name="images[{{ $i }}][sort_order]" type="number" :label="__('admin.sort_order')" :value="$row['sort_order'] ?? $i" />
                    <div>
                        <input type="hidden" name="images[{{ $i }}][is_primary]" value="0">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="images[{{ $i }}][is_primary]" value="1" class="rounded border-gray-300 text-[color:var(--mk-admin-primary)] dark:border-gray-600 dark:bg-gray-900"
                                @checked(!empty($row['is_primary']))>
                            {{ __('admin.primary_image') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
