<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreSeoRedirectRequest;
use App\Http\Requests\Admin\UpdateSeoRedirectRequest;
use App\Models\SeoRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeoRedirectController extends BaseAdminController
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $redirects = SeoRedirect::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('from_url', 'like', '%'.$q.'%')
                        ->orWhere('to_url', 'like', '%'.$q.'%');
                });
            })
            ->orderBy('from_url')
            ->paginate(25)
            ->withQueryString();

        return view('admin.seo.redirects.index', [
            'redirects' => $redirects,
            'filters' => ['q' => $q],
        ]);
    }

    public function create(): View
    {
        return view('admin.seo.redirects.create', ['redirect' => new SeoRedirect([
            'status_code' => 301,
            'status' => 'active',
        ])]);
    }

    public function store(StoreSeoRedirectRequest $request): RedirectResponse
    {
        $redirect = SeoRedirect::query()->create([
            'from_url' => $this->normalizeFromUrl($request->validated('from_url')),
            'to_url' => $this->normalizeToUrl($request->validated('to_url')),
            'status_code' => (int) $request->validated('status_code'),
            'status' => $request->validated('status'),
        ]);
        $this->logAdminActivity('settings', 'create_seo_redirect', $redirect, [], $redirect->only(['from_url', 'status_code']));

        return redirect()
            ->route('admin.seo.redirects.index')
            ->with('success', __('admin.redirect_created'));
    }

    public function edit(SeoRedirect $seo_redirect): View
    {
        return view('admin.seo.redirects.edit', ['redirect' => $seo_redirect]);
    }

    public function update(UpdateSeoRedirectRequest $request, SeoRedirect $seo_redirect): RedirectResponse
    {
        $before = $seo_redirect->only(['from_url', 'to_url', 'status_code', 'status']);
        $seo_redirect->update([
            'from_url' => $this->normalizeFromUrl($request->validated('from_url')),
            'to_url' => $this->normalizeToUrl($request->validated('to_url')),
            'status_code' => (int) $request->validated('status_code'),
            'status' => $request->validated('status'),
        ]);
        $updated = $seo_redirect->fresh();
        $this->logAdminActivity('settings', 'update_seo_redirect', $updated, $before, $updated?->only(['from_url', 'to_url', 'status_code', 'status']) ?? []);

        return redirect()
            ->route('admin.seo.redirects.index')
            ->with('success', __('admin.redirect_updated'));
    }

    public function destroy(SeoRedirect $seo_redirect): RedirectResponse
    {
        $snapshot = $seo_redirect->only(['id', 'from_url']);
        $seo_redirect->delete();
        $this->logAdminActivity('settings', 'delete_seo_redirect', null, $snapshot, []);

        return redirect()
            ->route('admin.seo.redirects.index')
            ->with('success', __('admin.redirect_deleted'));
    }

    private function normalizeFromUrl(string $url): string
    {
        $u = trim($url);
        $u = '/'.ltrim($u, '/');
        if ($u !== '/') {
            $u = rtrim($u, '/') ?: '/';
        }

        return $u;
    }

    private function normalizeToUrl(string $url): string
    {
        $u = trim($url);
        if (preg_match('#^https?://#i', $u) === 1) {
            return $u;
        }

        return '/'.ltrim($u, '/');
    }
}
