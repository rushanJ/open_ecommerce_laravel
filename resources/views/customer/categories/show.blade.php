@extends('customer.layouts.app')

@section('title', $category->name)

@section('meta_title', $category->meta_title ?? '')
@section('meta_description', $category->meta_description ?? '')
@section('canonical_url', route('customer.categories.show', $category, absolute: true))
@if($category->banner_path)
    @section('og_image_path', $category->banner_path)
@elseif($category->image_path)
    @section('og_image_path', $category->image_path)
@endif

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="border-b border-slate-200 pb-10">
            <h1 class="text-3xl font-bold text-slate-900">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="mt-3 max-w-3xl text-slate-600">{{ $category->description }}</p>
            @endif
            @if ($category->banner_path)
                <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                    <img src="{{ media_url($category->banner_path) }}" alt="" class="max-h-64 w-full object-cover" />
                </div>
            @endif
        </div>

        <div class="mt-10 flex flex-col gap-10 lg:flex-row">
            <aside class="w-full shrink-0 lg:w-72">
                <x-customer.filter-sidebar
                    :action="route('customer.categories.show', $category)"
                    :categories="$filterCategories"
                    :brands="$filterBrands"
                    :lock-category="true"
                />
            </aside>
            <div class="min-w-0 flex-1">
                @if ($products->isEmpty())
                    <p class="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center text-slate-600">{{ __('customer.no_products_found') }}</p>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-customer.product-card :product="$product" />
                        @endforeach
                    </div>
                    <x-customer.pagination :paginator="$products" />
                @endif
            </div>
        </div>
    </div>
@endsection
