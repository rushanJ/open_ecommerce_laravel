@extends('admin.layouts.app')

@section('title', __('admin.import_products').' — '.config('app.name'))

@section('breadcrumb', __('admin.import_products'))

@section('content')
    <x-admin.page-header :title="__('admin.import_products')" />

    <x-admin.card class="mt-6 max-w-2xl">
        <p class="text-sm text-slate-600">{{ __('admin.import_products_format_note') }}</p>

        <form method="POST" action="{{ route('admin.products.import.upload') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('admin.csv_file') }}</label>
                <input type="file" name="file" accept=".csv,.txt,text/csv" required class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-800 hover:file:bg-slate-200">
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3">
                <x-admin.button type="submit">{{ __('admin.upload') }}</x-admin.button>
                <a href="{{ route('admin.products.index') }}" class="text-sm text-slate-600 hover:text-slate-900">{{ __('admin.back') }}</a>
            </div>
        </form>
    </x-admin.card>
@endsection
