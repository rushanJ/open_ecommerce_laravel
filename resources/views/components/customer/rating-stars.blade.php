@props([
    'rating' => 0,
    'max' => 5,
])

@include('customer.components.rating-stars', [
    'rating' => $rating,
    'max' => $max,
    'attributes' => $attributes,
])

