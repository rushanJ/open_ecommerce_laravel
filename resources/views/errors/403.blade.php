@extends('layouts.error-simple')

@section('title', __('Forbidden'))

@section('message')
    <h1>{{ __('Forbidden') }}</h1>
    <p>{{ __('You do not have permission to access this page.') }}</p>
@endsection
