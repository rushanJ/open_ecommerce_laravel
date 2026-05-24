@extends('layouts.error-simple')

@section('title', __('Not Found'))

@section('message')
    <h1>{{ __('Page not found') }}</h1>
    <p>{{ __('The page you are looking for does not exist or has been moved.') }}</p>
@endsection
