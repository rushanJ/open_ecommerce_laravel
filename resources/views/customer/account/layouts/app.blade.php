@extends('customer.layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <aside class="lg:col-span-3">
                @include('customer.account.partials.sidebar')
            </aside>

            <section class="lg:col-span-9">
                @yield('account_content')
            </section>
        </div>
    </div>
@endsection

