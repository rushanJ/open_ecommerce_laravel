<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    /**
     * For this version: logged-in customers can review, but we block duplicates
     * when there's already a pending/approved review for the same product.
     */
    public function canCustomerReviewProduct(Customer $customer, Product $product): bool
    {
        return ! ProductReview::query()
            ->where('product_id', $product->getKey())
            ->where('customer_id', $customer->getKey())
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    public function findVerifiedPurchaseOrder(Customer $customer, Product $product): ?Order
    {
        return Order::query()
            ->where('customer_id', $customer->getKey())
            ->whereIn('status', ['delivered', 'processing', 'confirmed', 'shipped'])
            ->where('payment_status', 'paid')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->getKey());
            })
            ->latest('id')
            ->first();
    }

    /**
     * @param  array{
     *   rating:int,
     *   title?:string|null,
     *   review:string,
     *   images?:array<int,array{path:string}>
     * }  $data
     */
    public function createReview(Customer $customer, Product $product, array $data): ProductReview
    {
        return DB::transaction(function () use ($customer, $product, $data): ProductReview {
            $order = $this->findVerifiedPurchaseOrder($customer, $product);

            $review = ProductReview::query()->create([
                'product_id' => $product->getKey(),
                'customer_id' => $customer->getKey(),
                'order_id' => $order?->getKey(),
                'rating' => (int) $data['rating'],
                'title' => $data['title'] ?? null,
                'review' => $data['review'],
                'status' => 'pending',
                'is_verified_purchase' => $order !== null,
                'approved_by_admin_id' => null,
                'approved_at' => null,
            ]);

            $images = $data['images'] ?? [];
            foreach ($images as $img) {
                $path = trim((string) ($img['path'] ?? ''));
                if ($path === '') {
                    continue;
                }
                $review->images()->create(['path' => $path]);
            }

            return $review->fresh(['images']);
        });
    }

    public function approveReview(ProductReview $review, AdminUser $admin): ProductReview
    {
        $review->forceFill([
            'status' => 'approved',
            'approved_by_admin_id' => $admin->getKey(),
            'approved_at' => now(),
        ])->save();

        return $review->fresh();
    }

    public function rejectReview(ProductReview $review, AdminUser $admin, ?string $reason = null): ProductReview
    {
        $review->forceFill([
            'status' => 'rejected',
            'approved_by_admin_id' => $admin->getKey(),
            'approved_at' => now(),
        ])->save();

        // Optional: store $reason in metadata later (not required in this stage).
        return $review->fresh();
    }

    public function markSpam(ProductReview $review): ProductReview
    {
        $review->forceFill([
            'status' => 'spam',
            'approved_by_admin_id' => null,
            'approved_at' => null,
        ])->save();

        return $review->fresh();
    }
}

