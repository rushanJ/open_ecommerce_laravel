# open_ecommerce_laravel Admin API v1

Base URL prefix: `/api/admin/v1` (append to your app URL, e.g. `https://your-store.example/api/admin/v1`).

## Authentication

All endpoints require a **Bearer token** in the `Authorization` header. There is **no** session or customer Sanctum access for this API.

```http
Authorization: Bearer mk_live_xxxxxxxx...
```

### Token format

- **Production** (`APP_ENV=production`): tokens start with `mk_live_`.
- **Non-production**: tokens start with `mk_test_`.

Each token is followed by 40 random characters. The application stores only a **SHA-256 hash** of the full secret. The plain token is shown **once** when created in the admin panel (`Admin → API Tokens`).

### Abilities

Tokens carry a JSON list of abilities (or `*` for all, if you add that manually). Built-in abilities:

| Ability | Access |
|--------|--------|
| `products.read` | `GET /products`, `GET /products/{id}` |
| `orders.read` | `GET /orders`, `GET /orders/{id}` |
| `customers.read` | `GET /customers`, `GET /customers/{id}` |
| `payments.read` | `GET /payments`, `GET /payments/{id}` |
| `products.cost` | Includes `cost_price` on products/variants in admin API responses |
| `webhooks.manage` | Reserved for future admin API webhook management (UI uses admin RBAC today) |

`GET /health` requires a **valid** token but **no specific ability**.

### Error responses

- **401** — missing/invalid/revoked/expired token.
- **403** — token valid but missing required ability.

JSON shape matches the storefront API:

```json
{
  "success": false,
  "message": "Unauthenticated.",
  "errors": {}
}
```

## Pagination

List endpoints return:

```json
{
  "success": true,
  "message": null,
  "data": [ ... ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 25,
      "total": 60
    }
  }
}
```

Use query string `page` and optional `per_page` (capped by framework defaults).

## Endpoints

| Method | Path | Ability |
|--------|------|---------|
| GET | `/health` | any valid token |
| GET | `/products` | `products.read` |
| GET | `/products/{product}` | `products.read` |
| GET | `/orders` | `orders.read` |
| GET | `/orders/{order}` | `orders.read` |
| GET | `/customers` | `customers.read` |
| GET | `/customers/{customer}` | `customers.read` |
| GET | `/payments` | `payments.read` |
| GET | `/payments/{payment}` | `payments.read` |

### Filters (query)

- **Products:** `q` (name/sku), `status`
- **Orders:** `q` (order number / email), `status`, `date_from`, `date_to` (placed date)
- **Customers:** `q` (email / name), `status`
- **Payments:** `q` (reference / provider id), `status`, `date_from`, `date_to` (created date)

## Sample success response

`GET /api/admin/v1/health`

```json
{
  "success": true,
  "message": null,
  "data": {
    "status": "ok",
    "version": "1"
  },
  "meta": {}
}
```

## Security notes

- Never commit or log admin API tokens.
- Tokens are independent from **customer** Sanctum tokens.
- Admin API responses **exclude** payment gateway raw payloads, setting secrets, passwords, and `token_hash`.
- Use HTTPS in production and rotate tokens periodically.
