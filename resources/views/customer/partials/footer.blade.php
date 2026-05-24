<footer class="border-t border-slate-200/70 bg-white">
    <div class="mx-auto max-w-[1320px] px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.2fr_1fr_1fr]">
            <div>
                <a href="{{ route('customer.home') }}" class="inline-flex items-center gap-2 text-base font-black text-slate-950">
                    @if($storefrontStore?->logo_path)
                        <img src="{{ media_url($storefrontStore->logo_path) }}" alt="{{ $storefrontName }}" class="h-8 w-auto object-contain" />
                    @else
                        <span class="flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-600 to-blue-600 text-xs font-black text-white">M</span>
                    @endif
                    {{ $storefrontName }}
                </a>
                <p class="mt-4 max-w-xs text-sm leading-6 text-slate-500">{{ __('customer.footer_tagline') }}</p>
                <div class="mt-5 flex gap-2 text-xs font-bold text-slate-500" aria-label="{{ __('customer.social_links') }}">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-slate-50">IG</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-slate-50">FB</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-slate-50">X</span>
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-slate-50">YT</span>
                </div>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-950">{{ __('customer.quick_links') }}</p>
                <ul class="mt-4 space-y-2 text-sm text-slate-500">
                    <li><a href="{{ url('/pages/privacy-policy') }}" class="hover:text-blue-700">{{ __('customer.privacy_policy') }}</a></li>
                    <li><a href="{{ url('/pages/terms-and-conditions') }}" class="hover:text-blue-700">{{ __('customer.terms_conditions') }}</a></li>
                    <li><a href="{{ url('/pages/refund-policy') }}" class="hover:text-blue-700">{{ __('customer.refund_policy') }}</a></li>
                    <li><a href="{{ url('/pages/contact-us') }}" class="hover:text-blue-700">{{ __('customer.contact_us') }}</a></li>
                </ul>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-950">{{ __('customer.contact') }}</p>
                <p class="mt-4 text-sm leading-6 text-slate-500">{{ __('customer.contact_placeholder') }}</p>
                <a href="mailto:support@open-ecommerce-laravel.test" class="mt-3 inline-flex text-sm font-bold text-blue-700 hover:text-blue-900">support@open-ecommerce-laravel.test</a>
            </div>
        </div>
        <div class="mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-slate-100 pt-6 text-xs text-slate-400">
            <p>© {{ date('Y') }} {{ $storefrontName }}. {{ __('customer.rights_reserved') }}</p>
            <div class="flex flex-wrap gap-2 text-[0.65rem] font-bold text-slate-600" aria-label="{{ __('customer.payment_methods') }}">
                <span class="rounded-md border border-slate-200 bg-white px-2 py-1">Visa</span>
                <span class="rounded-md border border-slate-200 bg-white px-2 py-1">Mastercard</span>
                <span class="rounded-md border border-slate-200 bg-white px-2 py-1">PayPal</span>
                <span class="rounded-md border border-slate-200 bg-white px-2 py-1">Apple Pay</span>
                <span class="rounded-md border border-slate-200 bg-white px-2 py-1">Google Pay</span>
            </div>
        </div>
    </div>
</footer>
