@extends('customer.layouts.app')

@php
    $primaryOgPath = $product->primaryImage?->path ?? $product->images->first()?->path;
@endphp

@section('title', $product->name)

@section('meta_title', $product->meta_title ?? '')
@section('meta_description', $product->meta_description ?? $product->short_description ?? '')
@section('canonical_url', route('customer.products.show', $product, absolute: true))
@if($primaryOgPath)
    @section('og_image_path', $primaryOgPath)
@endif

@section('content')
    @php
        $imgs = $product->images->isNotEmpty() ? $product->images : collect();
        if ($imgs->isEmpty() && $product->primaryImage) {
            $imgs = collect([$product->primaryImage]);
        }
        $galleryImages = $imgs
            ->filter(fn ($img) => filled($img->path))
            ->map(fn ($img) => [
                'url' => media_url($img->path),
                'alt' => $img->alt_text ?: $product->name,
                'label' => $img->alt_text ?: $product->name,
            ])
            ->values();
        $fallbackLabel = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 2));
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 text-sm text-slate-500">
            <a href="{{ route('customer.home') }}" class="hover:text-emerald-700">{{ __('customer.home') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('customer.products.index') }}" class="hover:text-emerald-700">{{ __('customer.shop') }}</a>
            <span class="mx-2">/</span>
            <span class="text-slate-800">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2">
            <div
                class="lg:sticky lg:top-28 lg:self-start"
                x-data="{ selected: 0, images: @js($galleryImages->all()), failed: {} }"
                aria-label="{{ __('customer.images') }}"
            >
                @if ($galleryImages->isNotEmpty())
                    <div class="overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white p-3 shadow-xl shadow-slate-200/60">
                        <div class="relative flex aspect-square items-center justify-center overflow-hidden rounded-[1.35rem] bg-gradient-to-br from-slate-50 via-white to-blue-50">
                            <template x-if="images[selected]">
                                <img
                                    :src="images[selected].url"
                                    :alt="images[selected].alt"
                                    class="h-full w-full object-contain p-4 sm:p-6"
                                    :class="{ 'hidden': failed[selected] }"
                                    loading="eager"
                                    @load="failed[selected] = false"
                                    @error="failed[selected] = true"
                                >
                            </template>
                            <div x-show="failed[selected]" x-cloak class="absolute inset-0 flex h-full w-full items-center justify-center text-slate-400">
                                <span class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-white text-2xl font-black text-blue-700 shadow-sm">{{ $fallbackLabel }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($galleryImages->count() > 1)
                        <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5">
                            <template x-for="(image, index) in images" :key="image.url + index">
                                <button
                                    type="button"
                                    class="group overflow-hidden rounded-2xl border bg-white p-1 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-blue-100"
                                    :class="selected === index ? 'border-blue-500 ring-2 ring-blue-100' : 'border-slate-200'"
                                    @click="selected = index"
                                    :aria-label="`View image ${index + 1}`"
                                >
                                    <span class="relative flex aspect-square items-center justify-center overflow-hidden rounded-xl bg-slate-50">
                                        <img
                                            :src="image.url"
                                            :alt="image.alt"
                                            class="h-full w-full object-cover transition group-hover:scale-105"
                                            loading="lazy"
                                            @error="$event.target.classList.add('hidden'); $event.target.nextElementSibling.classList.remove('hidden')"
                                        >
                                        <span class="hidden text-xs font-black text-blue-700">{{ $fallbackLabel }}</span>
                                    </span>
                                </button>
                            </template>
                        </div>
                    @endif
                @else
                    <div class="flex aspect-square items-center justify-center rounded-[1.75rem] border border-dashed border-slate-200 bg-gradient-to-br from-slate-50 via-white to-blue-50 text-slate-400">
                        <span class="flex h-24 w-24 items-center justify-center rounded-[2rem] bg-white text-2xl font-black text-blue-700 shadow-sm">{{ $fallbackLabel }}</span>
                    </div>
                @endif
            </div>

            <div>
                <p class="text-sm font-semibold text-emerald-700">{{ $product->brand?->name }}</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $product->name }}</h1>
                <div class="mt-2 flex flex-wrap gap-2 text-sm text-slate-600">
                    @foreach ($product->categories as $c)
                        <a href="{{ route('customer.categories.show', $c) }}" class="rounded-full bg-slate-100 px-3 py-1 hover:bg-emerald-50 hover:text-emerald-800">{{ $c->name }}</a>
                    @endforeach
                </div>
                <div class="mt-6">
                    @php
                        $hasVariants = $product->variants->isNotEmpty();
                        if ($hasVariants) {
                            $minR = (float) $product->variants->min('regular_price');
                            $minS = $product->variants->filter(fn ($v) => $v->sale_price)->min('sale_price');
                            $minS = $minS ? (float) $minS : null;
                        } else {
                            $minR = (float) $product->regular_price;
                            $minS = $product->sale_price ? (float) $product->sale_price : null;
                        }
                    @endphp
                    <x-customer.price :regular="$minR" :sale="$minS && $minS > 0 && $minS < $minR ? $minS : null" :show-from="$hasVariants" />
                </div>
                <div class="mt-4">
                    <x-customer.stock-badge :status="$product->stock_status" />
                </div>
                @if ($product->short_description)
                    <p class="mt-6 text-slate-600">{{ $product->short_description }}</p>
                @endif

                <div class="mt-6 flex items-center gap-3 text-sm text-slate-600">
                    <x-customer.rating-stars :rating="$product->averageRating()" />
                    <span class="font-semibold text-slate-900">{{ number_format($product->averageRating(), 1) }}</span>
                    <span>·</span>
                    <span>{{ $product->reviewsCount() }} {{ __('customer.reviews') }}</span>
                </div>

                @inject('cartSvc', \App\Services\CartService::class)

                <div class="mt-8 space-y-4">
                    @if ($product->product_type === 'simple')
                        @php $availSimple = $cartSvc->getAvailableStock($product); @endphp
                        @if ($availSimple > 0)
                            <form method="POST" action="{{ route('customer.cart.items.store') }}" class="flex max-w-md flex-col gap-3 sm:flex-row sm:items-end">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}" />
                                <div class="flex-1">
                                    <label for="qty-simple" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.quantity') }}</label>
                                    <input id="qty-simple" type="number" name="quantity" value="1" min="1" step="1" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                                </div>
                                <button type="submit" class="w-full shrink-0 rounded-xl bg-emerald-600 px-6 py-3 text-center text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:w-auto">
                                    {{ __('customer.add_to_cart') }}
                                </button>
                            </form>
                        @else
                            <button type="button" disabled class="w-full max-w-md cursor-not-allowed rounded-xl bg-slate-200 px-6 py-4 text-center font-semibold text-slate-500 sm:w-auto">
                                {{ __('customer.out_of_stock') }}
                            </button>
                        @endif
                    @else
                        @php $firstAssignable = $product->variants->first(fn ($v) => $cartSvc->getAvailableStock($product, $v) > 0); @endphp
                        <form method="POST" action="{{ route('customer.cart.items.store') }}" class="max-w-xl space-y-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}" />
                            <div>
                                <label for="variant_id" class="block text-sm font-semibold text-slate-800">{{ __('customer.select_variant') }}</label>
                                <select id="variant_id" name="variant_id" required class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                    @unless ($firstAssignable)
                                        <option value="" disabled selected>{{ __('customer.select_variant') }}</option>
                                    @endunless
                                    @foreach ($product->variants as $v)
                                        @php
                                            $availV = $cartSvc->getAvailableStock($product, $v);
                                            $linePrice = (float) $v->regular_price;
                                            if ($v->sale_price !== null && (float) $v->sale_price > 0 && (float) $v->sale_price < $linePrice) {
                                                $linePrice = (float) $v->sale_price;
                                            }
                                            $stockLabel = match ($v->stock_status) {
                                                'in_stock' => __('customer.in_stock'),
                                                'out_of_stock' => __('customer.out_of_stock'),
                                                'on_backorder' => __('customer.on_backorder'),
                                                default => (string) $v->stock_status,
                                            };
                                        @endphp
                                        <option value="{{ $v->id }}" @disabled($availV <= 0) @selected($firstAssignable && (int) $firstAssignable->id === (int) $v->id)>
                                            {{ $v->name }}@if ($v->sku) · {{ $v->sku }}@endif · {{ number_format($linePrice, 2) }} LKR · {{ $stockLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="max-w-xs">
                                <label for="qty-var" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('customer.quantity') }}</label>
                                <input id="qty-var" type="number" name="quantity" value="1" min="1" step="1" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                            </div>
                            @if ($firstAssignable)
                                <button type="submit" class="w-full rounded-xl bg-emerald-600 px-6 py-4 text-center text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 sm:w-auto">
                                    {{ __('customer.add_to_cart') }}
                                </button>
                            @else
                                <button type="button" disabled class="w-full cursor-not-allowed rounded-xl bg-slate-200 px-6 py-4 text-center font-semibold text-slate-500 sm:w-auto">
                                    {{ __('customer.out_of_stock') }}
                                </button>
                            @endif
                        </form>
                    @endif
                </div>

                @if ($product->variants->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('customer.variants') }}</h2>
                        <div class="mt-3 overflow-x-auto rounded-xl border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('customer.name') }}</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('customer.sku') }}</th>
                                        <th class="px-4 py-3 text-right font-semibold text-slate-700">{{ __('customer.price') }}</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ __('customer.stock_status_filter') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach ($product->variants as $v)
                                        <tr>
                                            <td class="px-4 py-3 text-slate-900">{{ $v->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $v->sku ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <x-customer.price :regular="$v->regular_price" :sale="$v->sale_price" :show-from="false" />
                                            </td>
                                            <td class="px-4 py-3"><x-customer.stock-badge :status="$v->stock_status" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if ($product->attributeAssignments->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="text-lg font-semibold text-slate-900">{{ __('customer.attributes') }}</h2>
                        <dl class="mt-3 divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
                            @foreach ($product->attributeAssignments as $row)
                                @if ($row->attribute)
                                    <div class="grid grid-cols-2 gap-2 px-4 py-3 text-sm sm:grid-cols-3">
                                        <dt class="font-medium text-slate-700">{{ $row->attribute->name }}</dt>
                                        <dd class="col-span-1 text-slate-900 sm:col-span-2">
                                            {{ $row->value?->value ?? $row->custom_value ?? '—' }}
                                        </dd>
                                    </div>
                                @endif
                            @endforeach
                        </dl>
                    </div>
                @endif

            </div>
        </div>

        @if ($product->description)
            <section class="mt-16 border-t border-slate-200 pt-12">
                <h2 class="text-xl font-bold text-slate-900">{{ __('customer.description') }}</h2>
                <div class="prose prose-slate mt-4 max-w-none text-slate-700">{!! nl2br(e($product->description)) !!}</div>
            </section>
        @endif

        <section class="mt-16 border-t border-slate-200 pt-12">
            @include('customer.products.partials.reviews', ['product' => $product])
        </section>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-16 border-t border-slate-200 pt-12">
                <h2 class="text-xl font-bold text-slate-900">{{ __('customer.related_products') }}</h2>
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $rp)
                        <x-customer.product-card :product="$rp" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
