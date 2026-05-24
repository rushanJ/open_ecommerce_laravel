@props([
    'product',
])

@inject('cartSvc', \App\Services\CartService::class)

@php
    /** @var \App\Models\Product $product */
    $hasVariants = $product->relationLoaded('variants') ? $product->variants->isNotEmpty() : false;
    if ($hasVariants) {
        $activeVariants = $product->variants->where('status', 'active');
        $minRegular = (float) $activeVariants->min('regular_price');
        $anySale = $activeVariants->first(fn ($v) => $v->sale_price !== null && (float) $v->sale_price > 0);
        $minSale = $anySale ? (float) $activeVariants->filter(fn ($v) => $v->sale_price)->min('sale_price') : null;
    } else {
        $minRegular = (float) $product->regular_price;
        $minSale = $product->sale_price !== null ? (float) $product->sale_price : null;
    }
    $href = route('customer.products.show', $product);
    $isSimple = $product->product_type === 'simple';
    $availableSimple = $isSimple ? $cartSvc->getAvailableStock($product) : 0.0;
    $canAddSimple = $isSimple && $availableSimple > 0;
    $hasSaleBadge = $minSale !== null && $minSale > 0 && $minSale < $minRegular;
    $eyebrow = $product->brand?->name
        ?? ($product->relationLoaded('categories') ? $product->categories->first()?->name : null)
        ?? __('customer.shop');
    $imagePath = $product->primaryImage?->path;
    $imageAvailable = $imagePath
        && (\Illuminate\Support\Str::startsWith($imagePath, ['http://', 'https://']) || media_exists($imagePath));
    $fallbackLabel = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($product->name, 0, 2));
@endphp

<article class="group flex min-h-full flex-col overflow-hidden rounded-3xl border border-slate-200/70 bg-white shadow-sm ring-1 ring-slate-900/[0.02] transition duration-[180ms] ease-in-out hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100/50">
    <a href="{{ $href }}" class="relative block aspect-[4/5] overflow-hidden bg-gradient-to-br from-slate-50 to-slate-100">
        @if ($imageAvailable)
            <img src="{{ media_url($imagePath) }}" alt="{{ $product->primaryImage->alt_text ?? $product->name }}" class="h-full w-full object-contain p-3 transition duration-[180ms] ease-in-out group-hover:scale-[1.03]" loading="lazy" />
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-50 via-white to-violet-50 text-slate-400">
                <span class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-lg font-black text-blue-700 shadow-sm">{{ $fallbackLabel }}</span>
            </div>
        @endif
        <span class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-sm font-black text-slate-400 shadow-sm backdrop-blur" aria-hidden="true">♡</span>
        @if ($hasSaleBadge)
            <span class="absolute left-3 top-3 rounded-full bg-rose-500 px-2.5 py-1 text-[0.65rem] font-black uppercase tracking-wide text-white shadow">
                {{ __('customer.sale') }}
            </span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <p class="mb-1 truncate text-[0.68rem] font-black uppercase tracking-[0.16em] text-blue-600">{{ $eyebrow }}</p>
        <h3 class="line-clamp-2 text-base font-black leading-6 text-slate-950">
            <a href="{{ $href }}" class="hover:text-violet-700">{{ $product->name }}</a>
        </h3>
        <div class="mt-3">
            <x-customer.price :regular="$minRegular" :sale="$minSale && $minSale > 0 ? $minSale : null" :show-from="$hasVariants" />
        </div>
        <div class="mt-2">
            <x-customer.stock-badge :status="$product->stock_status" />
        </div>
        <div class="mt-5 flex flex-1 flex-col justify-end gap-2">
            <a href="{{ $href }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-3 py-2.5 text-center text-sm font-black text-white transition duration-[180ms] ease-in-out hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                {{ __('customer.view_product') }}
            </a>
            @if ($canAddSimple)
                <form method="POST" action="{{ route('customer.cart.items.store') }}" class="flex flex-1">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                    <input type="hidden" name="quantity" value="1" />
                    <button type="submit" class="inline-flex w-full flex-1 items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-800 transition duration-[180ms] ease-in-out hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        {{ __('customer.add_to_cart') }}
                    </button>
                </form>
            @elseif ($product->product_type === 'variable')
                <a href="{{ $href }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-center text-sm font-bold text-slate-800 transition duration-[180ms] ease-in-out hover:border-blue-200 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                    {{ __('customer.select_options') }}
                </a>
            @else
                <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-400">
                    {{ __('customer.out_of_stock') }}
                </button>
            @endif
        </div>
    </div>
</article>
