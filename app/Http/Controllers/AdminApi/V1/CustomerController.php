<?php

namespace App\Http\Controllers\AdminApi\V1;

use App\Http\Resources\AdminApi\V1\AdminCustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends AdminApiController
{
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $paginator = Customer::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('email', 'like', '%'.$q.'%')
                        ->orWhere('first_name', 'like', '%'.$q.'%')
                        ->orWhere('last_name', 'like', '%'.$q.'%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate((int) $request->query('per_page', 25))
            ->withQueryString();

        return $this->paginated($paginator, AdminCustomerResource::class);
    }

    public function show(Request $request, Customer $customer): JsonResponse
    {
        $customer->loadCount(['orders']);

        return $this->success((new AdminCustomerResource($customer))->resolve($request));
    }
}
