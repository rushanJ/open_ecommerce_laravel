<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreTaxRateRequest;
use App\Http\Requests\Admin\UpdateTaxRateRequest;
use App\Models\TaxClass;
use App\Models\TaxRate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaxRateController extends BaseAdminController
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $taxClassId = $request->get('tax_class_id');
        $status = (string) $request->get('status', '');

        $rates = TaxRate::query()
            ->with('taxClass')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where('name', 'like', $like)
                    ->orWhere('country_code', 'like', $like)
                    ->orWhere('province', 'like', $like)
                    ->orWhere('district', 'like', $like);
            })
            ->when($taxClassId !== null && $taxClassId !== '', fn ($query) => $query->where('tax_class_id', (int) $taxClassId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('tax_class_id')
            ->orderBy('priority')
            ->orderBy('country_code')
            ->orderBy('province')
            ->orderBy('district')
            ->paginate(20)
            ->withQueryString();

        $taxClasses = TaxClass::query()->orderBy('name')->get();

        return view('admin.tax.rates.index', [
            'rates' => $rates,
            'taxClasses' => $taxClasses,
            'filters' => [
                'q' => $q,
                'tax_class_id' => $taxClassId,
                'status' => $status,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.tax.rates.create', [
            'taxRate' => new TaxRate([
                'country_code' => 'LK',
                'priority' => 0,
                'is_compound' => false,
                'status' => 'active',
            ]),
            'taxClasses' => TaxClass::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTaxRateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['country_code'] = strtoupper((string) $data['country_code']);
        $data['priority'] = (int) ($data['priority'] ?? 0);
        $data['is_compound'] = (bool) ($data['is_compound'] ?? false);

        $taxRate = TaxRate::query()->create($data);
        $this->logAdminActivity('tax', 'create_rate', $taxRate, [], [
            'tax_class_id' => $taxRate->tax_class_id,
            'country_code' => $taxRate->country_code,
            'rate' => $taxRate->rate,
        ]);

        return redirect()->route('admin.tax.rates.edit', $taxRate)->with('success', __('admin.tax_rate_created'));
    }

    public function edit(TaxRate $taxRate): View
    {
        return view('admin.tax.rates.edit', [
            'taxRate' => $taxRate,
            'taxClasses' => TaxClass::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTaxRateRequest $request, TaxRate $taxRate): RedirectResponse
    {
        $data = $request->validated();
        $data['country_code'] = strtoupper((string) $data['country_code']);
        $data['priority'] = (int) ($data['priority'] ?? 0);
        $data['is_compound'] = (bool) ($data['is_compound'] ?? false);

        $before = $taxRate->only(['country_code', 'rate', 'status', 'tax_class_id']);
        $taxRate->fill($data)->save();
        $updated = $taxRate->fresh();
        $this->logAdminActivity('tax', 'update_rate', $updated, $before, $updated?->only(['country_code', 'rate', 'status', 'tax_class_id']) ?? []);

        return redirect()->route('admin.tax.rates.edit', $taxRate)->with('success', __('admin.tax_rate_updated'));
    }

    public function destroy(TaxRate $taxRate): RedirectResponse
    {
        $snapshot = $taxRate->only(['id', 'country_code', 'tax_class_id']);
        $taxRate->delete();
        $this->logAdminActivity('tax', 'delete_rate', null, $snapshot, []);

        return redirect()->route('admin.tax.rates.index')->with('success', __('admin.tax_rate_deleted'));
    }
}

