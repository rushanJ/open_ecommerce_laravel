@php
    /** @var \App\Models\Product $product */
    $reviews = $product->approvedReviews()
        ->with(['customer', 'images'])
        ->latest('id')
        ->get();
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ __('customer.reviews') }}</h2>
            <div class="mt-2 flex items-center gap-3 text-sm text-slate-600">
                <x-customer.rating-stars :rating="$product->averageRating()" />
                <span class="font-semibold text-slate-900">{{ number_format($product->averageRating(), 1) }}</span>
                <span>·</span>
                <span>{{ $product->reviewsCount() }} {{ __('customer.reviews') }}</span>
            </div>
        </div>
    </div>

    @if($reviews->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <p class="text-sm text-slate-600">{{ __('customer.no_reviews_yet') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($reviews as $r)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <x-customer.rating-stars :rating="$r->rating" />
                                @if($r->is_verified_purchase)
                                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                        {{ __('customer.verified_purchase') }}
                                    </span>
                                @endif
                            </div>
                            @if($r->title)
                                <p class="mt-2 text-sm font-semibold text-slate-900">{{ $r->title }}</p>
                            @endif
                            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $r->review }}</p>
                        </div>
                        <div class="text-sm text-slate-500">
                            <p class="font-semibold text-slate-700">
                                {{ $r->customer?->first_name ?? __('customer.account') }}
                            </p>
                            <p>{{ optional($r->created_at)->format('Y-m-d') }}</p>
                        </div>
                    </div>

                    @if($r->images->isNotEmpty())
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach($r->images as $img)
                                <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <img src="{{ $img->path }}" alt="" class="aspect-square w-full object-cover" loading="lazy" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @include('customer.products.partials.review-form', ['product' => $product])
</div>

