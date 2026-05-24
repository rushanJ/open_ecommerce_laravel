# open_ecommerce_laravel API v1

Base URL path: `/api/v1` (full URL example: `https://your-domain.test/api/v1`).

All JSON responses use this envelope:

**Success**

```json
{
  "success": true,
  "message": null,
  "data": {},
  "meta": {}
}
```

**Error**

```json
{
  "success": false,
  "message": "Human-readable message",
  "errors": {}
}
```

Validation failures return HTTP `422` with `errors` as a field-keyed object (Laravel validation format).

---

## Authentication (customers)

Customer API auth uses **Laravel Sanctum** personal access tokens.

1. `POST /api/v1/auth/register` or `POST /api/v1/auth/login`
2. Read `data.token` from the response
3. Send `Authorization: Bearer <token>` on protected routes

Protected routes:

- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`
- `GET /api/v1/orders`
- `GET /api/v1/orders/{order}`

`POST /api/v1/auth/logout` revokes the **current** token only.

Register body (JSON):

- `first_name` (required)
- `last_name` (optional)
- `email` (required, unique)
- `phone` (optional, unique when set)
- `password` / `password_confirmation` (required, min 8)

Login body:

- `email`, `password`

Optional: send header `X-Cart-Token: <guest-cart-token>` on register/login to merge an anonymous API cart into the customer account after signup/login.

---

## Guest cart token

For unauthenticated cart operations, the API uses a **guest cart token** carried in the `X-Cart-Token` header (8–128 characters: letters, numbers, `.`, `_`, `-`).

- On the first guest `GET /api/v1/cart` or cart mutation without a token, the response `meta.cart_token` contains a new token. Clients should store it and send it on subsequent requests.
- Internally the token is stored on the cart row’s `session_id` field (same pattern as web session id).

Cart routes (rate limited `api-cart`, work for guest **or** authenticated customer):

- `GET /api/v1/cart`
- `POST /api/v1/cart/items`
- `PUT /api/v1/cart/items/{cartItem}`
- `DELETE /api/v1/cart/items/{cartItem}`
- `DELETE /api/v1/cart`
- `POST /api/v1/cart/coupon`
- `DELETE /api/v1/cart/coupon`

---

## Public catalog

Rate limit: `api-public` (120 requests/minute per IP by default).

| Method | Path | Description |
|--------|------|--------------|
| GET | `/settings` | Store name, currency, logo URLs, public SEO defaults (no secrets) |
| GET | `/products` | Paginated product list (`per_page` max 50). Filters: `q`, `category`, `brand`, `min_price`, `max_price`, `stock_status`, `sort`, `page` |
| GET | `/products/{slug}` | Product detail (descriptions, images, variants, attributes, review summary, related) |
| GET | `/categories` | Active categories |
| GET | `/categories/{slug}` | Category by slug |
| GET | `/brands` | Active brands |
| GET | `/brands/{slug}` | Brand by slug |

---

## Guest order lookup

`POST /api/v1/orders/lookup`

Body:

```json
{
  "order_number": "MEK-...",
  "contact": "buyer@example.com"
}
```

`contact` must match the order’s stored email (case-insensitive) or phone (normalized spaces). Returns `404` with the standard error envelope when not found.

---

## Customer orders (authenticated)

| Method | Path | Description |
|--------|------|-------------|
| GET | `/orders` | Paginated list for the authenticated customer |
| GET | `/orders/{order}` | Order detail (items + addresses). **403/404** if the order does not belong to the customer |

---

## Auth endpoints (throttled)

`POST /auth/register` and `POST /auth/login` use the `api-auth` limiter (10/minute per IP + email by default).

---

## Sample requests

**List products**

```http
GET /api/v1/products?page=1&per_page=12 HTTP/1.1
Host: localhost
Accept: application/json
```

**Bearer request**

```http
GET /api/v1/auth/me HTTP/1.1
Host: localhost
Accept: application/json
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

**Guest cart add**

```http
POST /api/v1/cart/items HTTP/1.1
Host: localhost
Accept: application/json
Content-Type: application/json

{"product_id": 1, "quantity": 1}
```

Response includes `meta.cart_token` for guests; reuse that value as `X-Cart-Token` on later cart calls.

---

## Notes

- Admin APIs are **not** exposed under `/api/v1`.
- Payment secrets and non-public settings are **not** included in `/settings` or catalog payloads.
- Blade storefront routes are unchanged; this API is additive.
