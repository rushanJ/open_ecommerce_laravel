@php
    /** @var \App\Models\Product $product */
    $customer = auth('customer')->user();
    /** @var \App\Services\ReviewService $reviewSvc */
    $reviewSvc = app(\App\Services\ReviewService::class);
    $canReview = $customer ? $reviewSvc->canCustomerReviewProduct($customer, $product) : false;
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-bold text-slate-900">{{ __('customer.write_review') }}</h3>

    @if(!auth('customer')->check())
        <p class="mt-2 text-sm text-slate-600">
            {{ __('customer.login_to_review') }}
            <a href="{{ route('customer.login') }}" class="font-semibold text-emerald-700 hover:text-emerald-800">{{ __('customer.login') }}</a>
        </p>
    @elseif(!$canReview)
        <p class="mt-2 text-sm text-slate-600">{{ __('customer.already_reviewed') }}</p>
    @else
        <form method="POST" action="{{ route('customer.products.reviews.store', $product) }}" class="mt-4 space-y-4">
            @csrf

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('customer.rating') }}</label>
                <select name="rating" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2">
                    <option value="" disabled @selected(old('rating') === null)>{{ __('customer.select_optional') }}</option>
                    @for($i=5; $i>=1; $i--)
                        <option value="{{ $i }}" @selected((int) old('rating') === $i)>{{ $i }}</option>
                    @endfor
                </select>
                @error('rating') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('customer.review_title') }}</label>
                <input name="title" type="text" value="{{ old('title') }}"
                       class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                @error('title') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('customer.review_text') }}</label>
                <textarea name="review" rows="5" required
                          class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2">{{ old('review') }}</textarea>
                @error('review') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">{{ __('customer.images') }}</label>
                <p class="mt-1 text-xs text-slate-500">{{ __('customer.images_paths_help') }}</p>
                <div class="mt-2 space-y-2">
                    @for($i=0; $i<2; $i++)
                        <input name="images[{{ $i }}][path]" type="text" value="{{ old("images.$i.path") }}"
                               placeholder="/storage/reviews/example.jpg"
                               class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-emerald-600/20 focus:border-emerald-500 focus:ring-2" />
                    @endfor
                </div>
                @error('images') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                @error('images.*.path') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                {{ __('customer.submit_review') }}
            </button>
        </form>
    @endif
</div>

