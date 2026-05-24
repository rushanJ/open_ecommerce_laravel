@php /** @var \App\Models\Page $page */ @endphp

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="title" :label="__('admin.title')" :value="old('title', $page->title)" required />
    <x-admin.input name="slug" :label="__('admin.slug')" :value="old('slug', $page->slug)" />
</div>

<x-admin.select name="status" :label="__('admin.status')">
    @foreach(['draft','published','archived'] as $s)
        <option value="{{ $s }}" @selected(old('status', $page->status ?? 'draft') === $s)>{{ __('admin.'.$s) }}</option>
    @endforeach
</x-admin.select>

<x-admin.input name="published_at" type="datetime-local" :label="__('admin.published_at')" :value="old('published_at', optional($page->published_at)->format('Y-m-d\TH:i'))" />

<x-admin.input name="meta_title" :label="__('admin.meta_title')" :value="old('meta_title', $page->meta_title)" />
<x-admin.textarea name="meta_description" :label="__('admin.meta_description')">{{ old('meta_description', $page->meta_description) }}</x-admin.textarea>

<x-admin.textarea name="content" :label="__('admin.content')" rows="12">{{ old('content', $page->content) }}</x-admin.textarea>

