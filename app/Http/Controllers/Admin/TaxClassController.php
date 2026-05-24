<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreTaxClassRequest;
use App\Http\Requests\Admin\UpdateTaxClassRequest;
use App\Models\TaxClass;
use App\Services\SlugService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaxClassController extends BaseAdminController
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $classes = TaxClass::query()
            ->withCount(['products', 'rates'])
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
                $query->where('name', 'like', $like)->orWhere('slug', 'like', $like);
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.tax.classes.index', [
            'classes' => $classes,
            'filters' => compact('q'),
        ]);
    }

    public function create(): View
    {
        return view('admin.tax.classes.create', [
            'taxClass' => new TaxClass(),
        ]);
    }

    public function store(StoreTaxClassRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (($data['slug'] ?? null) === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('tax_classes', 'slug', $data['name']);
        }

        $taxClass = TaxClass::query()->create($data);
        $this->logAdminActivity('tax', 'create_class', $taxClass, [], $taxClass->only(['name', 'slug']));

        return redirect()->route('admin.tax.classes.edit', $taxClass)->with('success', __('admin.tax_class_created'));
    }

    public function edit(TaxClass $taxClass): View
    {
        return view('admin.tax.classes.edit', compact('taxClass'));
    }

    public function update(UpdateTaxClassRequest $request, TaxClass $taxClass): RedirectResponse
    {
        $data = $request->validated();

        if (($data['slug'] ?? null) === null || $data['slug'] === '') {
            $data['slug'] = $this->slugService->unique('tax_classes', 'slug', $data['name'], (int) $taxClass->getKey());
        }

        $before = $taxClass->only(['name', 'slug']);
        $taxClass->fill($data)->save();
        $updated = $taxClass->fresh();
        $this->logAdminActivity('tax', 'update_class', $updated, $before, $updated?->only(['name', 'slug']) ?? []);

        return redirect()->route('admin.tax.classes.edit', $taxClass)->with('success', __('admin.tax_class_updated'));
    }

    public function destroy(TaxClass $taxClass): RedirectResponse
    {
        if ($taxClass->products()->exists() || $taxClass->rates()->exists()) {
            return $this->backWithError(__('admin.tax_delete_blocked'));
        }

        $snapshot = $taxClass->only(['id', 'name', 'slug']);
        $taxClass->delete();
        $this->logAdminActivity('tax', 'delete_class', null, $snapshot, []);

        return redirect()->route('admin.tax.classes.index')->with('success', __('admin.tax_class_deleted'));
    }
}

