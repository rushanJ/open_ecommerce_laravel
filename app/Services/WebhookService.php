<?php

namespace App\Services;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatchSafe(string $eventType, array $payload): void
    {
        try {
            $this->dispatch($eventType, $payload);
        } catch (Throwable $e) {
            Log::warning('webhook.dispatch_safe.failed', [
                'event' => $eventType,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function dispatch(string $eventType, array $payload): void
    {
        $endpoints = WebhookEndpoint::query()
            ->where('status', 'active')
            ->get();

        foreach ($endpoints as $endpoint) {
            if (! $endpoint->isSubscribedTo($eventType)) {
                continue;
            }

            try {
                $delivery = WebhookDelivery::query()->create([
                    'webhook_endpoint_id' => $endpoint->getKey(),
                    'event_type' => $eventType,
                    'payload' => $payload,
                    'attempts' => 0,
                ]);

                $this->deliver($delivery);
            } catch (Throwable $e) {
                Log::error('webhook.dispatch.endpoint_failed', [
                    'event' => $eventType,
                    'endpoint_id' => $endpoint->getKey(),
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    public function deliver(WebhookDelivery $delivery): void
    {
        $delivery->loadMissing('endpoint');
        $endpoint = $delivery->endpoint;

        if ($endpoint === null || $endpoint->status !== 'active') {
            return;
        }

        $delivery->attempts = (int) $delivery->attempts + 1;
        $delivery->delivered_at = null;
        $delivery->failed_at = null;
        $delivery->save();

        $payload = $delivery->payload ?? [];
        $body = $this->encodePayload($payload);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Open-Ecommerce-Laravel-Event' => $delivery->event_type,
        ];

        $secret = $endpoint->secret;
        if (is_string($secret) && $secret !== '') {
            $headers['X-Open-Ecommerce-Laravel-Signature'] = $this->signPayload($payload, $secret);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->send('POST', $endpoint->url, [
                    'body' => $body,
                ]);

            $status = $response->status();
            $text = $response->body();
            if (strlen($text) > 10000) {
                $text = substr($text, 0, 10000).'…';
            }

            $delivery->response_status = $status;
            $delivery->response_body = $text;

            if ($status >= 200 && $status < 300) {
                $delivery->delivered_at = now();
                $delivery->failed_at = null;
            } else {
                $delivery->failed_at = now();
            }

            $delivery->save();
        } catch (Throwable $e) {
            Log::warning('webhook.deliver.exception', [
                'delivery_id' => $delivery->getKey(),
                'url' => $endpoint->url,
                'message' => $e->getMessage(),
            ]);

            $delivery->response_status = null;
            $delivery->response_body = substr($e->getMessage(), 0, 10000);
            $delivery->failed_at = now();
            $delivery->save();
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function signPayload(array $payload, string $secret): string
    {
        return hash_hmac('sha256', $this->encodePayload($payload), $secret);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function encodePayload(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
