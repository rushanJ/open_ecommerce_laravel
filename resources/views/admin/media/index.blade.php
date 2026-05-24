@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.media_library')" :subtitle="__('admin.media_subtitle')" />

    <x-admin.card class="mt-6">
        <form method="GET" action="{{ route('admin.media.index') }}" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <x-admin.input name="q" :label="__('admin.search')" :value="$filters['q'] ?? ''" />
            <x-admin.input name="mime_type" :label="__('admin.mime_type')" :value="$filters['mime'] ?? ''" />
            <div class="flex items-end gap-2">
                <x-admin.button type="submit">{{ __('admin.filter') }}</x-admin.button>
                <a href="{{ route('admin.media.index') }}" class="text-sm text-slate-500 hover:text-slate-700">{{ __('admin.reset') }}</a>
            </div>
            <div class="flex items-end justify-end">
                <a href="{{ route('admin.media.create') }}" class="inline-flex rounded-lg bg-[color:var(--mk-admin-primary)] px-4 py-2 text-sm font-semibold text-white hover:opacity-95">
                    {{ __('admin.upload_media') }}
                </a>
            </div>
        </form>
    </x-admin.card>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse($media as $m)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                    @if(str_starts_with($m->mime_type ?? '', 'image/') && $m->url)
                        <img src="{{ $m->url }}" alt="{{ $m->alt_text ?? '' }}" class="aspect-square w-full object-cover" loading="lazy" />
                    @else
                        <div class="flex aspect-square items-center justify-center text-sm text-slate-500">—</div>
                    @endif
                </div>
                <div class="mt-3">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ $m->filename }}</p>
                    <p class="mt-1 truncate text-xs text-slate-600">{{ $m->mime_type }} · {{ number_format((float) $m->size / 1024, 0) }} KB</p>
                    <div class="mt-2 flex items-center justify-between gap-2">
                        <button type="button" class="text-xs font-semibold text-slate-700 hover:text-slate-900"
                                onclick="navigator.clipboard.writeText('{{ $m->path }}')">
                            {{ __('admin.copy_path') }}
                        </button>
                        <a href="{{ route('admin.media.edit', $m) }}" class="text-xs font-semibold text-[color:var(--mk-admin-primary)] hover:underline">
                            {{ __('admin.edit') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-4">
                <x-admin.card>
                    <x-admin.empty-state :title="__('admin.no_results')" :message="__('admin.no_results_message')" />
                </x-admin.card>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $media->links() }}
    </div>
@endsection

