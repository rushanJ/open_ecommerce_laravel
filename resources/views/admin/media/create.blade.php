@extends('admin.layouts.app')

@section('content')
    <x-admin.page-header :title="__('admin.upload_media')" />

    <x-admin.card class="mt-6">
        <form method="POST" action="{{ route('admin.media.store') }}" class="space-y-6" enctype="multipart/form-data">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold text-slate-900">{{ __('admin.file') }}</label>
                    <input type="file" name="file" accept="image/jpeg,image/png,image/webp,image/gif" required
                           class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm" />
                    @error('file') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-900">{{ __('admin.directory') }}</label>
                    <select name="directory" class="mt-2 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                        @php($dir = old('directory', 'media'))
                        @foreach(['media','products','categories','brands','banners'] as $d)
                            <option value="{{ $d }}" @selected($dir === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('directory') <p class="mt-2 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>
            </div>

            <x-admin.input name="alt_text" :label="__('admin.alt_text')" :value="old('alt_text')" />

            <div class="flex items-center gap-3">
                <x-admin.button type="submit">{{ __('admin.save') }}</x-admin.button>
                <a href="{{ route('admin.media.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection

