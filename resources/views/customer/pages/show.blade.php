@extends('customer.layouts.app')

@section('title', $page->title)

@section('meta_title', $page->meta_title ?? '')
@section('meta_description', $page->meta_description ?? '')
@section('canonical_url', route('customer.pages.show', $page, absolute: true))

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $page->title }}</h1>
            @if($page->published_at)
                <p class="mt-2 text-sm text-slate-500">{{ $page->published_at->format('Y-m-d') }}</p>
            @endif

            <div class="prose prose-slate mt-6 max-w-none">
                {!! nl2br(e($page->content)) !!}
            </div>
        </div>
    </div>
@endsection

