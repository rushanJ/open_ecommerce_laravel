@props([
    'taxClass',
])

@php /** @var \App\Models\TaxClass $taxClass */ @endphp

<div class="space-y-4">
    <x-admin.input name="name" :label="__('admin.name')" :value="old('name', $taxClass->name)" required />
    <x-admin.input name="slug" :label="__('admin.slug')" :value="old('slug', $taxClass->slug)" />
    <x-admin.textarea name="description" :label="__('admin.description')" :value="old('description', $taxClass->description)" />
</div>

