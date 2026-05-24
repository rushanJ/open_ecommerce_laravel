@extends('layouts.error-simple')

@section('title', __('Service Unavailable'))

@section('message')
    <h1>{{ __('Service unavailable') }}</h1>
    <p>{{ __('We are temporarily unable to handle your request. Please try again shortly.') }}</p>
@endsection
