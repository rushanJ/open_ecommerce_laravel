@extends('customer.layouts.app')

@section('title', $brand->name)

@section('meta_description', $brand->description ?? '')
@section('canonical_url', route('customer.brands.show', $brand, absolute: true))
@if($brand->logo_path)
    @section('og_image_path', $brand->logo_path)
@endif

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-6 border-b border-slate-200 pb-10 md:flex-row md:items-center">
            @if ($brand->logo_path)
                <img src="{{ media_url($brand->logo_path) }}" alt="" class="h-20 w-auto object-contain" />
            @else
                <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-2xl font-bold text-emerald-800">{{ \Illuminate\Support\Str::substr($brand->name, 0, 1) }}</span>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-slate-900">{{ $brand->name }}</h1>
                @if ($brand->description)
                    <p class="mt-3 max-w-3xl text-slate-600">{{ $brand->description }}</p>
                @endif
                <p class="mt-2 text-sm text-slate-500">{{ __('customer.products_count', ['count' => $products->total()]) }}</p>
            </div>
        </div>

        <div class="mt-10 flex flex-col gap-10 lg:flex-row">
            <aside class="w-full shrink-0 lg:w-72">
                <x-customer.filter-sidebar
                    :action="route('customer.brands.show', $brand)"
                    :categories="$filterCategories"
                    :brands="$filterBrands"
                    :lock-brand="true"
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
