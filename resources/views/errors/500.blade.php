@extends('layouts.error-simple')

@section('title', __('Server Error'))

@section('message')
    <h1>{{ __('Something went wrong') }}</h1>
    <p>{{ __('We could not complete your request. Please try again later.') }}</p>
@endsection
