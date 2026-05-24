<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProductReviewStatusRequest;
use App\Models\ProductReview;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status');
        $rating = $request->get('rating');
        $verified = $request->get('verified');

        $reviews = ProductReview::query()
            ->with(['product', 'customer'])
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('title', 'like', $like)
                        ->orWhere('review', 'like', $like)
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $like))
                        ->orWhereHas('customer', fn ($c) => $c->where('email', 'like', $like));
                });
            })
            ->when(is_string($status) && $status !== '', fn ($query) => $query->where('status', $status))
            ->when(is_string($rating) && $rating !== '', fn ($query) => $query->where('rating', (int) $rating))
            ->when(is_string($verified) && $verified !== '', function ($query) use ($verified) {
                if ($verified === '1') {
                    $query->where('is_verified_purchase', true);
                } elseif ($verified === '0') {
                    $query->where('is_verified_purchase', false);
                }
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filters' => [
                'q' => $q,
                'status' => $status,
                'rating' => $rating,
                'verified' => $verified,
            ],
        ]);
    }

    public function show(ProductReview $review): View
    {
        $review->load(['product', 'customer', 'order', 'images', 'approvedByAdmin']);

        return view('admin.reviews.show', [
            'review' => $review,
        ]);
    }

    public function updateStatus(UpdateProductReviewStatusRequest $request, ProductReview $review, ReviewService $reviews): RedirectResponse
    {
        $status = (string) $request->validated('status');
        $reason = $request->validated('reason');
        $admin = auth('admin')->user();

        if (! $admin) {
            abort(403);
        }

        if ($status === 'approved') {
            $reviews->approveReview($review, $admin);
        } elseif ($status === 'rejected') {
            $reviews->rejectReview($review, $admin, $reason);
        } elseif ($status === 'spam') {
            $reviews->markSpam($review);
        } else {
            $review->forceFill([
                'status' => 'pending',
                'approved_by_admin_id' => null,
                'approved_at' => null,
            ])->save();
        }

        return redirect()
            ->route('admin.reviews.show', $review)
            ->with('success', __('admin.review_status_updated'));
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', __('admin.review_deleted'));
    }
}

