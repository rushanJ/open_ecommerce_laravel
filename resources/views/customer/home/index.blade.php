@extends('customer.layouts.app')

@section('title', __('customer.home'))

@section('canonical_url', url('/'))

@section('content')
    @php
        $heroCategories = $parentCategories->take(4)->values();
        $heroFallbacks = collect([
            ['name' => 'Electronics', 'count' => '5k+'],
            ['name' => 'Fashion', 'count' => '3k+'],
            ['name' => 'Home & Living', 'count' => '2k+'],
            ['name' => 'Sports', 'count' => '4k+'],
        ]);
        $featuredIds = $featuredProducts->pluck('id');
        $latestVisible = $latestProducts->reject(fn ($product) => $featuredIds->contains($product->id))->take(8);
        if ($latestVisible->isEmpty()) {
            $latestVisible = $latestProducts->take(8);
        }
        $trustItems = [
            ['title' => __('customer.fast_checkout'), 'text' => __('customer.fast_checkout_text'), 'tone' => 'text-blue-600 bg-blue-50 ring-blue-100', 'icon' => ''],
            ['title' => __('customer.buyer_protection'), 'text' => __('customer.buyer_protection_text'), 'tone' => 'text-violet-600 bg-violet-50 ring-violet-100', 'icon' => ''],
            ['title' => __('customer.trusted_sellers'), 'text' => __('customer.trusted_sellers_text'), 'tone' => 'text-amber-600 bg-amber-50 ring-amber-100', 'icon' => ''],
            ['title' => __('customer.easy_returns'), 'text' => __('customer.easy_returns_text'), 'tone' => 'text-indigo-600 bg-indigo-50 ring-indigo-100', 'icon' => ''],
        ];
    @endphp

    <div class="mx-auto max-w-[1320px] px-4 py-5 sm:px-6 lg:px-8">
        <section class="relative isolate overflow-hidden rounded-[1.75rem] bg-[#050B1E] px-5 py-8 text-white shadow-2xl shadow-slate-300/60 sm:px-8 lg:px-12 lg:py-12">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_88%_20%,rgba(124,58,237,0.45),transparent_24%),radial-gradient(circle_at_78%_88%,rgba(37,99,235,0.45),transparent_28%),linear-gradient(135deg,#020617_0%,#050B1E_52%,#0F172A_100%)]"></div>
            <div class="absolute inset-0 -z-10 opacity-[0.08] [background-image:linear-gradient(to_right,#fff_1px,transparent_1px),linear-gradient(to_bottom,#fff_1px,transparent_1px)] [background-size:34px_34px]"></div>
            <div class="absolute -bottom-28 right-8 -z-10 h-72 w-72 rounded-full bg-violet-500/30 blur-3xl"></div>

            <div class="relative grid items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="max-w-2xl">
                    <span class="inline-flex rounded-full border border-white/10 bg-white/10 px-3 py-1 text-[0.7rem] font-bold uppercase tracking-[0.18em] text-blue-100 backdrop-blur">
                        {{ __('customer.marketplace') }}
                    </span>
                    <h1 class="mt-5 text-4xl font-black tracking-tight sm:text-5xl lg:text-6xl">
                        {{ __('customer.hero_title') }}
                    </h1>
                    <p class="mt-4 max-w-xl text-base leading-7 text-slate-300">
                        {{ __('customer.hero_subtitle') }}
                    </p>

                    <form action="{{ route('customer.products.index') }}" method="GET" class="mt-8 flex max-w-xl flex-col gap-2 rounded-2xl border border-white/10 bg-white/10 p-2 shadow-2xl shadow-black/30 backdrop-blur sm:flex-row" role="search">
                        <label for="hero-search" class="sr-only">{{ __('customer.search') }}</label>
                        <input id="hero-search" type="search" name="q" value="" placeholder="{{ __('customer.search_placeholder') }}" class="min-h-12 min-w-0 flex-1 rounded-xl border-0 bg-white px-4 text-sm text-slate-900 outline-none ring-blue-300/40 placeholder:text-slate-400 focus:ring-4" />
                        <button type="submit" class="rounded-xl bg-gradient-to-r from-violet-600 to-blue-600 px-7 py-3 text-sm font-black text-white shadow-lg shadow-blue-950/30 transition duration-[180ms] ease-in-out hover:-translate-y-0.5 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-blue-300/40">
                            {{ __('customer.search') }}
                        </button>
                    </form>

                    <a href="{{ route('customer.categories.index') }}" class="mt-4 inline-flex rounded-xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-bold text-white transition duration-[180ms] ease-in-out hover:-translate-y-0.5 hover:bg-white/10 focus:outline-none focus:ring-4 focus:ring-white/20">
                        {{ __('customer.browse_all_categories') }}
                    </a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($heroFallbacks as $index => $fallback)
                        @php
                            $category = $heroCategories->get($index);
                            $name = $category?->name ?? $fallback['name'];
                            $count = $category ? number_format((int) ($category->storefront_products_count ?? 0)).'+' : $fallback['count'];
                            $href = $category ? route('customer.categories.show', $category) : route('customer.categories.index');
                        @endphp
                        <a href="{{ $href }}" class="group rounded-2xl border border-white/10 bg-white/10 p-4 shadow-lg shadow-black/5 backdrop-blur transition duration-[180ms] ease-in-out hover:-translate-y-1 hover:bg-white/15 hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-white/20">
                            <div class="flex items-center gap-4">
                                <span class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-white/10 ring-1 ring-white/10">
                                    @if ($category?->image_path)
                                        <img src="{{ media_url($category->image_path) }}" alt="" class="h-full w-full object-cover" loading="lazy" />
                                    @else
                                        <span class="text-xl font-black text-blue-100">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($name, 0, 1)) }}</span>
                                    @endif
                                </span>
                                <span>
                                    <span class="block text-sm font-bold text-white group-hover:text-blue-100">{{ $name }}</span>
                                    <span class="mt-1 block text-xs text-slate-300">{{ __('customer.items_count', ['count' => $count]) }}</span>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-3 rounded-3xl border border-slate-200/70 bg-white p-3 shadow-sm sm:grid-cols-2 lg:grid-cols-4" aria-label="{{ __('customer.trust_strip') }}">
            @foreach ($trustItems as $item)
                <div class="flex items-center gap-3 rounded-2xl px-3 py-3 transition duration-[180ms] ease-in-out hover:bg-slate-50">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full ring-1 {{ $item['tone'] }}" aria-hidden="true">
                        <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                    </span>
                    <span>
                        <span class="block text-sm font-bold text-slate-950">{{ $item['title'] }}</span>
                        <span class="mt-0.5 block text-xs text-slate-500">{{ $item['text'] }}</span>
                    </span>
                </div>
            @endforeach
        </section>

        <div class="space-y-16 py-14">
            <section>
                <div class="mb-6 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black tracking-tight text-slate-950">{{ __('customer.popular_categories') }}</h2>
                        <p class="mt-1 max-w-xl text-sm text-slate-500">{{ __('customer.browse_categories_subtitle') }}</p>
                    </div>
                    <a href="{{ route('customer.categories.index') }}" class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700 sm:inline-flex">{{ __('customer.view_all_categories') }}</a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @forelse ($parentCategories->take(4) as $cat)
                        <x-customer.category-card :category="$cat" />
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-6 text-sm text-slate-500">{{ __('customer.no_categories_found') }}</div>
                    @endforelse
                </div>
            </section>

            @if ($featuredProducts->isNotEmpty())
                <section>
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black tracking-tight text-slate-950">{{ __('customer.featured_products') }}</h2>
                            <p class="mt-1 max-w-xl text-sm text-slate-500">{{ __('customer.featured_products_subtitle') }}</p>
                        </div>
                        <a href="{{ route('customer.products.index') }}" class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700 sm:inline-flex">{{ __('customer.view_all') }}</a>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($featuredProducts->take(8) as $product)
                            <x-customer.product-card :product="$product" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($latestVisible->isNotEmpty())
                <section>
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black tracking-tight text-slate-950">{{ __('customer.latest_products') }}</h2>
                            <p class="mt-1 max-w-xl text-sm text-slate-500">{{ __('customer.latest_products_subtitle') }}</p>
                        </div>
                        <a href="{{ route('customer.products.index') }}" class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700 sm:inline-flex">{{ __('customer.view_all') }}</a>
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($latestVisible as $product)
                            <x-customer.product-card :product="$product" />
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($brands->isNotEmpty())
                <section>
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black tracking-tight text-slate-950">{{ __('customer.shop_by_brands') }}</h2>
                            <p class="mt-1 max-w-xl text-sm text-slate-500">{{ __('customer.browse_brands_subtitle') }}</p>
                        </div>
                        <a href="{{ route('customer.brands.index') }}" class="hidden rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700 sm:inline-flex">{{ __('customer.view_all_brands') }}</a>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                        @foreach ($brands->take(10) as $brand)
                            <x-customer.brand-card :brand="$brand" />
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="relative isolate overflow-hidden rounded-[1.75rem] bg-[#050B1E] p-7 text-white shadow-2xl shadow-slate-300/60 sm:p-9">
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_88%_90%,rgba(124,58,237,0.7),transparent_26%),radial-gradient(circle_at_74%_70%,rgba(37,99,235,0.35),transparent_20%)]"></div>
                <div class="absolute inset-0 -z-10 opacity-[0.08] [background-image:linear-gradient(to_right,#fff_1px,transparent_1px)] [background-size:28px_28px]"></div>
                <div class="relative grid gap-6 sm:grid-cols-[1fr_auto] sm:items-center">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200">{{ __('customer.deals') }}</p>
                        <h2 class="mt-3 text-2xl font-black tracking-tight sm:text-3xl">{{ __('customer.promo_title') }}</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">{{ __('customer.promo_subtitle') }}</p>
                    </div>
                    <a href="{{ route('customer.products.index') }}" class="inline-flex justify-center rounded-2xl bg-white px-6 py-3 text-sm font-black text-slate-950 shadow-lg transition duration-[180ms] ease-in-out hover:-translate-y-0.5 hover:bg-blue-50 focus:outline-none focus:ring-4 focus:ring-white/30">
                        {{ __('customer.explore_deals') }}
                    </a>
                </div>
            </section>
        </div>
    </div>

@endsection
