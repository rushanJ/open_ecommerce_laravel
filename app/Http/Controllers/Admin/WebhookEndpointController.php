<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreWebhookEndpointRequest;
use App\Http\Requests\Admin\UpdateWebhookEndpointRequest;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebhookEndpointController extends BaseAdminController
{
    /**
     * @return list<string>
     */
    public static function availableEvents(): array
    {
        return [
            'order.created',
            'order.paid',
            'order.status_changed',
            'payment.paid',
            'payment.failed',
            'customer.created',
            'product.created',
            'product.updated',
            'inventory.low_stock',
        ];
    }

    public function index(): View
    {
        $this->abortUnlessCan('settings.view');

        $endpoints = WebhookEndpoint::query()
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.webhooks.index', ['endpoints' => $endpoints]);
    }

    public function create(): View
    {
        $this->abortUnlessCan('settings.view');

        return view('admin.webhooks.create', [
            'endpoint' => new WebhookEndpoint([
                'status' => 'active',
                'events' => [],
            ]),
            'availableEvents' => self::availableEvents(),
        ]);
    }

    public function store(StoreWebhookEndpointRequest $request): RedirectResponse
    {
        $this->abortUnlessCan('settings.update');

        $data = $request->validated();
        $endpoint = WebhookEndpoint::query()->create([
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => array_values(array_unique($data['events'])),
            'secret' => isset($data['secret']) && $data['secret'] !== '' ? (string) $data['secret'] : null,
            'status' => $data['status'],
        ]);

        $this->logAdminActivity('settings', 'create_webhook', $endpoint, [], $endpoint->only(['name', 'url', 'status']));

        return redirect()
            ->route('admin.webhooks.index')
            ->with('success', __('admin.webhook_created'));
    }

    public function edit(WebhookEndpoint $webhookEndpoint): View
    {
        $this->abortUnlessCan('settings.view');

        return view('admin.webhooks.edit', [
            'endpoint' => $webhookEndpoint,
            'availableEvents' => self::availableEvents(),
        ]);
    }

    public function update(UpdateWebhookEndpointRequest $request, WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        $this->abortUnlessCan('settings.update');

        $before = $webhookEndpoint->only(['name', 'url', 'events', 'status']);
        $data = $request->validated();
        $webhookEndpoint->update([
            'name' => $data['name'],
            'url' => $data['url'],
            'events' => array_values(array_unique($data['events'])),
            'secret' => $request->filled('secret') ? (string) $data['secret'] : $webhookEndpoint->secret,
            'status' => $data['status'],
        ]);

        $fresh = $webhookEndpoint->fresh();
        $this->logAdminActivity('settings', 'update_webhook', $fresh, $before, $fresh?->only(['name', 'url', 'events', 'status']) ?? []);

        return redirect()
            ->route('admin.webhooks.index')
            ->with('success', __('admin.webhook_updated'));
    }

    public function destroy(WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        $this->abortUnlessCan('settings.update');

        $this->logAdminActivity('settings', 'delete_webhook', $webhookEndpoint, $webhookEndpoint->only(['name', 'url']), []);
        $webhookEndpoint->delete();

        return redirect()
            ->route('admin.webhooks.index')
            ->with('success', __('admin.webhook_deleted'));
    }

    public function deliveries(Request $request, WebhookEndpoint $webhookEndpoint): View
    {
        $this->abortUnlessCan('settings.view');

        $deliveries = $webhookEndpoint->deliveries()
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.webhooks.deliveries', [
            'endpoint' => $webhookEndpoint,
            'deliveries' => $deliveries,
        ]);
    }

    public function retry(WebhookDelivery $webhookDelivery): RedirectResponse
    {
        $this->abortUnlessCan('settings.update');

        $webhookDelivery->load('endpoint');
        app(WebhookService::class)->deliver($webhookDelivery);
        $this->logAdminActivity('settings', 'retry_webhook_delivery', $webhookDelivery, [], []);

        return redirect()
            ->back()
            ->with('success', __('admin.delivery_retried'));
    }
}
