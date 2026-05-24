@extends('admin.layouts.app')

@section('title', __('admin.seo_settings').' — '.config('app.name'))

@section('breadcrumb', __('admin.seo_settings'))

@section('content')
    <x-admin.page-header
        :title="__('admin.seo_settings')"
        :subtitle="__('admin.seo_settings_subtitle')"
    />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.settings.seo.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <x-admin.input name="seo_default_title" :label="__('admin.seo_default_title')" :value="old('seo_default_title', $seoTitle)" required />

            <x-admin.textarea name="seo_default_description" :label="__('admin.seo_default_description')" rows="3" :value="old('seo_default_description', $seoDescription)" />

            <x-admin.textarea name="seo_default_keywords" :label="__('admin.seo_default_keywords')" rows="2" :value="old('seo_default_keywords', $seoKeywords)" />

            <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
        </form>
    </x-admin.card>
@endsection
