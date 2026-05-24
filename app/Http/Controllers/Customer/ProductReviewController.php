<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreProductReviewRequest;
use App\Models\Product;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function store(StoreProductReviewRequest $request, Product $product, ReviewService $reviews): RedirectResponse
    {
        if (! Product::query()->whereKey($product->getKey())->forStorefront()->exists()) {
            abort(404);
        }

        $customer = auth('customer')->user();
        if (! $customer) {
            abort(403);
        }

        if (! $reviews->canCustomerReviewProduct($customer, $product)) {
            return back()->withErrors([
                'review' => __('customer.already_reviewed'),
            ])->withInput();
        }

        $reviews->createReview($customer, $product, $request->validated());

        return back()->with('success', __('customer.review_submitted'));
    }
}

