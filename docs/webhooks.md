# open_ecommerce_laravel outbound webhooks

open_ecommerce_laravel can POST signed JSON payloads to URLs you configure in **Admin → Webhooks**.

## Supported events

Configure one or more of:

- `order.created`
- `order.paid`
- `order.status_changed`
- `payment.paid`
- `payment.failed`
- `customer.created`
- `product.created`
- `product.updated`
- `inventory.low_stock`

Use `*` in the stored events list to receive **all** events (use with care).

## Payload format

Each delivery sends a JSON body (UTF-8) that mirrors the `payload` array stored in `webhook_deliveries.payload`. Typical keys include:

- `order_id`, `payment_id`, `product_id`, `customer_id`
- `from_status`, `to_status` (for `order.status_changed`)
- `sku`, `stock_quantity`, `low_stock_threshold`, `variant_id` (for `inventory.low_stock`)

## HTTP headers

| Header | Description |
|--------|-------------|
| `Content-Type` | `application/json` |
| `X-Open-Ecommerce-Laravel-Event` | Event type string |
| `X-Open-Ecommerce-Laravel-Signature` | Present only when the endpoint has a **secret**: HMAC-SHA256 of the **raw JSON body** using the endpoint secret |

## Signature verification (receiver)

```text
expected = HMAC_SHA256( raw_request_body_bytes , endpoint_secret )
compare timing-safe to X-Open-Ecommerce-Laravel-Signature header
```

Use the **raw body** string exactly as received (before JSON parsing) so the signature matches.

## Retries and delivery log

- Each firing creates a `webhook_deliveries` row and attempts an immediate HTTP POST (10s timeout).
- **Attempts** increments on each try; **2xx** sets `delivered_at`; non-2xx or transport errors set `failed_at` and store response details (truncated).
- From **Admin → Webhooks → Deliveries** you can **retry** a row; retries call the same URL again and increment attempts.

Queue-based backoff may be added later; the schema is **retry-ready**.

## Commerce safety

Webhook failures are logged and must **never** block checkout, payments, inventory, or admin product saves. Integration code uses `dispatchSafe` around outbound calls.

## Sample receiver (pseudocode)

```text
read raw body as string B
event = header "X-Open-Ecommerce-Laravel-Event"
sig = header "X-Open-Ecommerce-Laravel-Signature" or empty

if secret is configured:
   expected = HMAC_SHA256(B, secret)
   if not timing_safe_equal(expected, sig):
       return HTTP 401

data = JSON.parse(B)
switch event:
   case "order.created":
      handle_new_order(data.order_id)
...
return HTTP 200
```
