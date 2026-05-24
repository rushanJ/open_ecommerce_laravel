<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductReviewImage;
use App\Services\CartService;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        /** @var CartService $cart */
        $cart = app(CartService::class);
        /** @var InventoryService $inventory */
        $inventory = app(InventoryService::class);

        $customer = Customer::query()->first()
            ?? Customer::query()->create([
                'first_name' => 'Demo',
                'last_name' => 'Reviewer',
                'email' => 'reviewer@open-ecommerce-laravel.test',
                'phone' => '0771111111',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]);

        $payhere = PaymentMethod::query()->firstOrCreate(
            ['code' => 'payhere'],
            ['name' => 'PayHere', 'provider' => 'payhere', 'status' => 'active', 'config' => ['enabled' => false, 'mode' => 'sandbox']]
        );

        /** @var Collection<int, Product> $products */
        $products = Product::query()->forStorefront()->with(['variants' => fn ($q) => $q->active()->orderBy('id')])->limit(5)->get();
        if ($products->isEmpty()) {
            return;
        }

        // Ensure we have at least one real paid order containing a product for verified reviews.
        $verifiedOrder = Order::query()
            ->where('customer_id', $customer->getKey())
            ->where('payment_status', 'paid')
            ->whereIn('status', ['processing', 'confirmed', 'shipped', 'delivered'])
            ->whereHas('items')
            ->latest('id')
            ->first();

        if ($verifiedOrder === null) {
            // Reuse the deterministic customer account demo seeder if available, otherwise create a minimal paid order.
            if (class_exists(\Database\Seeders\CustomerAccountDemoSeeder::class)) {
                $this->call(CustomerAccountDemoSeeder::class);
            }

            $verifiedOrder = Order::query()
                ->where('customer_id', $customer->getKey())
                ->where('payment_status', 'paid')
                ->whereHas('items')
                ->latest('id')
                ->first();
        }

        DB::transaction(function () use ($customer, $products, $verifiedOrder, $payhere, $cart, $inventory): void {
            $titles = [
                'Great quality',
                'Good value',
                'Works as expected',
                'Not bad',
                'Could be better',
            ];

            foreach ($products as $idx => $product) {
                $title = $titles[$idx % count($titles)];

                $status = match ($idx) {
                    0, 1 => 'approved',
                    2 => 'pending',
                    3 => 'rejected',
                    default => 'approved',
                };

                $rating = match ($idx) {
                    0 => 5,
                    1 => 4,
                    2 => 5,
                    3 => 2,
                    default => 3,
                };

                $orderId = null;
                $isVerified = false;

                if ($verifiedOrder) {
                    $contains = $verifiedOrder->items()->where('product_id', $product->getKey())->exists();
                    if ($contains) {
                        $orderId = $verifiedOrder->getKey();
                        $isVerified = true;
                    }
                }

                $review = ProductReview::query()->updateOrCreate(
                    [
                        'product_id' => $product->getKey(),
                        'customer_id' => $customer->getKey(),
                        'title' => $title,
                    ],
                    [
                        'order_id' => $orderId,
                        'rating' => $rating,
                        'review' => "Seeded review for {$product->name}. This is realistic demo content for moderation and storefront display.",
                        'status' => $status,
                        'is_verified_purchase' => $isVerified,
                        'approved_by_admin_id' => null,
                        'approved_at' => $status === 'approved' ? now() : null,
                    ]
                );

                ProductReviewImage::query()->where('review_id', $review->getKey())->delete();
                if ($idx % 2 === 0) {
                    $review->images()->create(['path' => '/storage/reviews/demo-'.$product->slug.'-1.jpg']);
                }
                if ($idx === 0) {
                    $review->images()->create(['path' => '/storage/reviews/demo-'.$product->slug.'-2.jpg']);
                }
            }

            // If we still don't have any verified purchase review, try to tie one to a real order item.
            $anyVerified = ProductReview::query()->where('customer_id', $customer->getKey())->where('is_verified_purchase', true)->exists();
            if (! $anyVerified && $verifiedOrder) {
                $item = $verifiedOrder->items()->first();
                if ($item && $item->product_id) {
                    $product = Product::query()->find($item->product_id);
                    if ($product) {
                        ProductReview::query()->updateOrCreate(
                            [
                                'product_id' => $product->getKey(),
                                'customer_id' => $customer->getKey(),
                                'title' => 'Verified purchase review',
                            ],
                            [
                                'order_id' => $verifiedOrder->getKey(),
                                'rating' => 5,
                                'review' => 'Verified purchase seeded review.',
                                'status' => 'approved',
                                'is_verified_purchase' => true,
                                'approved_by_admin_id' => null,
                                'approved_at' => now(),
                            ]
                        );
                    }
                }
            }
        });
    }
}

