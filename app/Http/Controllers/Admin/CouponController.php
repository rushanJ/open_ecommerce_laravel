<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreCouponRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CouponController extends BaseAdminController
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $type = (string) $request->get('type', '');
        $status = (string) $request->get('status', '');

        $coupons = Coupon::query()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where('code', 'like', $like)->orWhere('name', 'like', $like);
            })
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'filters' => compact('q', 'type', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'coupon' => new Coupon(),
            'products' => Product::query()->forStorefront()->orderBy('name')->limit(200)->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('email')->limit(200)->get(),
            'selected' => [
                'product_ids' => [],
                'category_ids' => [],
                'customer_ids' => [],
            ],
        ]);
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper(trim($data['code']));

        /** @var Coupon $coupon */
        $coupon = DB::transaction(function () use ($data): Coupon {
            $coupon = Coupon::query()->create($this->couponPayload($data));
            $coupon->products()->sync($data['product_ids'] ?? []);
            $coupon->categories()->sync($data['category_ids'] ?? []);
            $coupon->customers()->sync($data['customer_ids'] ?? []);

            return $coupon;
        });

        $this->logAdminActivity('coupons', 'create', $coupon, [], $coupon->only(['code', 'type', 'status']));

        return redirect()
            ->route('admin.coupons.edit', $coupon)
            ->with('success', __('admin.coupon_created'));
    }

    public function edit(Coupon $coupon): View
    {
        $coupon->load(['products:id', 'categories:id', 'customers:id']);

        return view('admin.coupons.edit', [
            'coupon' => $coupon,
            'products' => Product::query()->forStorefront()->orderBy('name')->limit(200)->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('email')->limit(200)->get(),
            'selected' => [
                'product_ids' => $coupon->products->pluck('id')->all(),
                'category_ids' => $coupon->categories->pluck('id')->all(),
                'customer_ids' => $coupon->customers->pluck('id')->all(),
            ],
        ]);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $data = $request->validated();
        $data['code'] = strtoupper(trim($data['code']));

        $before = $coupon->only(['code', 'type', 'status', 'value']);
        DB::transaction(function () use ($coupon, $data): void {
            $coupon->fill($this->couponPayload($data));
            $coupon->save();
            $coupon->products()->sync($data['product_ids'] ?? []);
            $coupon->categories()->sync($data['category_ids'] ?? []);
            $coupon->customers()->sync($data['customer_ids'] ?? []);
        });
        $coupon->refresh();
        $this->logAdminActivity('coupons', 'update', $coupon, $before, $coupon->only(['code', 'type', 'status', 'value']));

        return redirect()
            ->route('admin.coupons.edit', $coupon)
            ->with('success', __('admin.coupon_updated'));
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $snapshot = $coupon->only(['id', 'code']);
        $coupon->delete();
        $this->logAdminActivity('coupons', 'delete', null, $snapshot, []);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', __('admin.coupon_deleted'));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function couponPayload(array $data): array
    {
        return [
            'code' => $data['code'],
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'minimum_order_amount' => $data['minimum_order_amount'] ?? null,
            'maximum_discount_amount' => $data['maximum_discount_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_limit_per_customer' => $data['usage_limit_per_customer'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'],
        ];
    }
}

