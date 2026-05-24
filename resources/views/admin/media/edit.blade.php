@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.edit_media')" :subtitle="$mediaItem->filename" />

    <x-admin.card class="mt-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50">
                    @if(str_starts_with($mediaItem->mime_type ?? '', 'image/') && $mediaItem->url)
                        <img src="{{ $mediaItem->url }}" alt="{{ $mediaItem->alt_text ?? '' }}" class="aspect-square w-full object-cover" />
                    @else
                        <div class="flex aspect-square items-center justify-center text-sm text-slate-500">—</div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <dl class="grid gap-3 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.disk') }}</dt>
                            <dd class="mt-1 font-medium text-slate-900">{{ $mediaItem->disk }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.mime_type') }}</dt>
                            <dd class="mt-1 font-medium text-slate-900">{{ $mediaItem->mime_type }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('admin.path') }}</dt>
                            <dd class="mt-1 flex items-center justify-between gap-2">
                                <code class="truncate rounded bg-slate-50 px-2 py-1 text-xs text-slate-700">{{ $mediaItem->path }}</code>
                                <button type="button" class="text-sm font-semibold text-[color:var(--mk-admin-primary)]"
                                        onclick="navigator.clipboard.writeText('{{ $mediaItem->path }}')">
                                    {{ __('admin.copy_path') }}
                                </button>
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">URL</dt>
                            <dd class="mt-1">
                                <code class="break-all rounded bg-slate-50 px-2 py-1 text-xs text-slate-700">{{ $mediaItem->url ?? '—' }}</code>
                            </dd>
                        </div>
                    </dl>
                </div>

                <form method="POST" action="{{ route('admin.media.update', $mediaItem) }}" class="space-y-4">
            @csrf
            @method('PUT')
                    <x-admin.input name="alt_text" :label="__('admin.alt_text')" :value="old('alt_text', $mediaItem->alt_text)" />

                    <div class="flex items-center gap-3">
                        <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                        <a href="{{ route('admin.media.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
                    </div>
                </form>

                <form method="POST" action="{{ route('admin.media.destroy', $mediaItem) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}');">
                    @csrf
                    @method('DELETE')
                    <x-admin.button type="submit" variant="danger">{{ __('admin.delete') }}</x-admin.button>
                </div>
            </div>
        </div>
    </x-admin.card>
@endsection

