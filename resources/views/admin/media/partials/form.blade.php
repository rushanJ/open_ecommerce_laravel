@php /** @var \App\Models\Media $mediaItem */ @endphp

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="disk" :label="__('admin.disk')" :value="old('disk', $mediaItem->disk ?? 'public')" required />
    <x-admin.input name="mime_type" :label="__('admin.mime_type')" :value="old('mime_type', $mediaItem->mime_type)" required />
</div>

<x-admin.input name="filename" :label="__('admin.filename')" :value="old('filename', $mediaItem->filename)" required />
<x-admin.input name="path" :label="__('admin.path')" :value="old('path', $mediaItem->path)" required />

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="size" type="number" step="1" :label="__('admin.size')" :value="old('size', $mediaItem->size ?? 0)" />
    <x-admin.input name="alt_text" :label="__('admin.alt_text')" :value="old('alt_text', $mediaItem->alt_text)" />
</div>

