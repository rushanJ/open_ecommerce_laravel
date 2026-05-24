@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.review_details')" :subtitle="$review->product?->name ?? __('admin.review')" />

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-admin.card class="lg:col-span-2">
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.product') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $review->product?->name ?? '—' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.customer') }}</p>
                    <p class="mt-1 text-sm text-slate-700">{{ $review->customer?->email ?? '—' }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.rating') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $review->rating }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.verified') }}</p>
                        <x-admin.badge class="mt-1" :variant="$review->is_verified_purchase ? 'success' : 'muted'">
                            {{ $review->is_verified_purchase ? __('admin.verified') : __('admin.not_verified') }}
                        </x-admin.badge>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.status') }}</p>
                        <x-admin.badge class="mt-1" :variant="match($review->status){'approved'=>'success','pending'=>'warning','rejected'=>'danger','spam'=>'muted',default=>'muted'}">
                            {{ ucfirst($review->status) }}
                        </x-admin.badge>
                    </div>
                </div>

                @if($review->title)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.title') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $review->title }}</p>
                    </div>
                @endif

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.review') }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $review->review }}</p>
                </div>

                @if($review->images->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.images') }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach($review->images as $img)
                                <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                                    <img src="{{ $img->path }}" alt="" class="aspect-square w-full object-cover" loading="lazy" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-admin.card>

        <div class="space-y-6">
            <x-admin.card>
                <h3 class="text-sm font-semibold text-slate-900">{{ __('admin.update_status') }}</h3>

                <form method="POST" action="{{ route('admin.reviews.status.update', $review) }}" class="mt-4 space-y-3">
                    @csrf
                    @method('PATCH')

                    <x-admin.select name="status" :label="__('admin.status')">
                        @foreach(['pending','approved','rejected','spam'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $review->status) === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </x-admin.select>

                    <x-admin.textarea name="reason" :label="__('admin.reason_optional')">{{ old('reason') }}</x-admin.textarea>

                    <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card>
                <h3 class="text-sm font-semibold text-slate-900">{{ __('admin.danger_zone') }}</h3>
                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="mt-4"
                      onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
                    @csrf
                    @method('DELETE')
                    <x-admin.button type="submit" variant="danger">{{ __('admin.delete') }}</x-admin.button>
                </form>
            </x-admin.card>
        </div>
    </div>
@endsection

