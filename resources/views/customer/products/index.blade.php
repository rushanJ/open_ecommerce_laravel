@extends('customer.layouts.app')

@section('title', __('customer.shop'))

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-slate-900">{{ __('customer.shop') }}</h1>
        <p class="mt-2 text-slate-600">{{ __('customer.hero_subtitle') }}</p>

        <div class="mt-10 flex flex-col gap-10 lg:flex-row">
            <aside class="w-full shrink-0 lg:w-72">
                <x-customer.filter-sidebar
                    :action="route('customer.products.index')"
                    :categories="$filterCategories"
                    :brands="$filterBrands"
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
