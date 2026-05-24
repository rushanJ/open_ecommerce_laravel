@php
    /** @var \App\Models\Menu $menu */
    /** @var \App\Models\MenuItem $menuItem */
@endphp

<input type="hidden" name="menu_id" value="{{ $menu->id }}" />

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="title" :label="__('admin.title')" :value="old('title', $menuItem->title)" required />
    <x-admin.input name="sort_order" type="number" step="1" :label="__('admin.sort_order')" :value="old('sort_order', $menuItem->sort_order ?? 0)" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.select name="parent_id" :label="__('admin.parent')">
        <option value="">{{ __('admin.none') }}</option>
        @foreach($parents as $p)
            <option value="{{ $p->id }}" @selected((string) old('parent_id', $menuItem->parent_id) === (string) $p->id)>{{ $p->title }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.select name="target" :label="__('admin.target')">
        <option value="self" @selected(old('target', $menuItem->target ?? 'self') === 'self')>self</option>
        <option value="blank" @selected(old('target', $menuItem->target ?? 'self') === 'blank')>blank</option>
    </x-admin.select>
</div>

<x-admin.select name="type" :label="__('admin.type')">
    @foreach(['page','category','product','custom'] as $t)
        <option value="{{ $t }}" @selected(old('type', $menuItem->type) === $t)>{{ $t }}</option>
    @endforeach
</x-admin.select>

<x-admin.input name="reference_id" type="number" step="1" :label="__('admin.reference')" :value="old('reference_id', $menuItem->reference_id)" />
<x-admin.input name="url" :label="__('admin.custom_url')" :value="old('url', $menuItem->url)" />

<div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
    <p class="font-semibold text-slate-900">{{ __('admin.reference_help') }}</p>
    <ul class="mt-2 list-disc pl-5 text-slate-700">
        <li><strong>page</strong>: use Page ID from Pages list</li>
        <li><strong>category</strong>: use Category ID</li>
        <li><strong>product</strong>: use Product ID</li>
        <li><strong>custom</strong>: URL required</li>
    </ul>
</div>

