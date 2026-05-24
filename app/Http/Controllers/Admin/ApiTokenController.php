<?php

namespace App\Http\Controllers\Admin;

use App\Models\ApiToken;
use App\Services\ApiTokenService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiTokenController extends BaseAdminController
{
    public function __construct(
        protected ApiTokenService $apiTokens,
    ) {}

    public function index(): View
    {
        $this->abortUnlessCan('settings.view');

        $tokens = ApiToken::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.api_tokens.index', ['tokens' => $tokens]);
    }

    public function create(): View
    {
        $this->abortUnlessCan('settings.view');

        return view('admin.api_tokens.create', [
            'abilityOptions' => $this->abilityOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->abortUnlessCan('settings.update');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', Rule::in(array_keys($this->abilityOptions()))],
            'expires_at' => ['nullable', 'date'],
        ]);

        $abilities = array_values(array_unique($validated['abilities'] ?? []));

        $expiresAt = isset($validated['expires_at']) && $validated['expires_at'] !== ''
            ? Carbon::parse($validated['expires_at'])
            : null;

        $created = $this->apiTokens->createToken($validated['name'], $abilities, $expiresAt);

        $this->logAdminActivity('settings', 'create_api_token', $created['token'], [], ['name' => $validated['name']]);

        return redirect()
            ->route('admin.api-tokens.show-token')
            ->with('new_api_token_plain', $created['plain']);
    }

    public function showToken(Request $request): View|RedirectResponse
    {
        $this->abortUnlessCan('settings.view');

        $plain = $request->session()->pull('new_api_token_plain');
        if ($plain === null) {
            return redirect()->route('admin.api-tokens.index');
        }

        return view('admin.api_tokens.show-token', ['plain' => $plain]);
    }

    public function revoke(ApiToken $apiToken): RedirectResponse
    {
        $this->abortUnlessCan('settings.update');

        $this->apiTokens->revoke($apiToken);
        $this->logAdminActivity('settings', 'revoke_api_token', $apiToken, [], []);

        return redirect()
            ->route('admin.api-tokens.index')
            ->with('success', __('admin.api_token_revoked'));
    }

    /**
     * @return array<string, string>
     */
    protected function abilityOptions(): array
    {
        return [
            'products.read' => 'products.read',
            'orders.read' => 'orders.read',
            'customers.read' => 'customers.read',
            'payments.read' => 'payments.read',
            'webhooks.manage' => 'webhooks.manage',
            'products.cost' => 'products.cost',
        ];
    }
}
