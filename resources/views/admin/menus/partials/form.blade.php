@php /** @var \App\Models\Menu $menu */ @endphp

<div class="grid gap-4 sm:grid-cols-2">
    <x-admin.input name="name" :label="__('admin.name')" :value="old('name', $menu->name)" required />
    <x-admin.select name="location" :label="__('admin.location')">
        @foreach(['header','footer','account','mobile'] as $loc)
            <option value="{{ $loc }}" @selected(old('location', $menu->location) === $loc)>{{ $loc }}</option>
        @endforeach
    </x-admin.select>
</div>

<x-admin.select name="status" :label="__('admin.status')">
    @foreach(['active','inactive'] as $s)
        <option value="{{ $s }}" @selected(old('status', $menu->status ?? 'active') === $s)>{{ ucfirst($s) }}</option>
    @endforeach
</x-admin.select>

