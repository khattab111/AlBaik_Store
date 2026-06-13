# Mobile API

Base path:

```text
/api/mobile
```

Send language with:

```text
Accept-Language: ar
Accept-Language: en
```

Authenticated endpoints use Laravel Sanctum:

```text
Authorization: Bearer {token}
Accept: application/json
```

Responses use this shape:

```json
{
  "success": true,
  "message": null,
  "data": {}
}
```

## Authentication

```text
POST /auth/register
POST /auth/login
POST /auth/logout
GET  /auth/me
POST /auth/forgot-password
POST /auth/reset-password
```

Register body:

```json
{
  "name": "Customer",
  "email": "customer@example.com",
  "phone": "+963900000000",
  "password": "password",
  "password_confirmation": "password",
  "locale": "ar"
}
```

Login body:

```json
{
  "login": "customer@example.com",
  "password": "password",
  "device_name": "iphone"
}
```

Auth responses include:

```text
token
user
customer_type
wholesale_status
wallet_balance
```

## Profile

```text
GET  /profile
PUT  /profile
POST /profile/change-password
```

## Home

```text
GET /home
```

Returns banners, featured categories, featured brands, featured products, active offers, featured services, wallet balance when authenticated, and unread notifications count.

## Categories

```text
GET /categories
GET /categories/{slug}
```

## Brands

```text
GET /brands
GET /brands/{slug}
```

## Products

```text
GET /products
GET /products/{slug}
```

Supported filters:

```text
search
category
brand
min_price
max_price
sort=latest|best_selling|rating|price_asc|price_desc
only_offers=1
page
per_page
```

Product prices are calculated on the backend. The app must not send product prices as trusted values.

## Favorites

Authenticated:

```text
GET    /favorites
POST   /favorites/{product_id}
DELETE /favorites/{product_id}
```

## Addresses

Authenticated:

```text
GET    /addresses
POST   /addresses
GET    /addresses/{id}
PUT    /addresses/{id}
DELETE /addresses/{id}
POST   /addresses/{id}/set-default
```

Address body:

```json
{
  "label": "Home",
  "full_name": "Customer Name",
  "phone": "+963900000000",
  "city_id": 1,
  "area": "Area",
  "street": "Street details",
  "building": "12",
  "floor": "3",
  "apartment": "8",
  "notes": "Near landmark",
  "is_default": true
}
```

## Cart

Authenticated:

```text
GET    /cart
POST   /cart/items
PUT    /cart/items/{item}
DELETE /cart/items/{item}
DELETE /cart/clear
```

Add product:

```json
{
  "product_id": 1,
  "quantity": 2
}
```

Add offer:

```json
{
  "offer_id": 1,
  "quantity": 1
}
```

The API rejects trusted price fields such as `price`, `unit_price`, `total`, and `price_type`.

## Checkout

Authenticated:

```text
GET  /checkout/summary
POST /checkout/place-order
```

Summary query:

```text
address_id
shipping_company_id
payment_method
coupon_code
```

Place order body:

```json
{
  "address_id": 1,
  "shipping_company_id": 1,
  "payment_method": "wallet",
  "coupon_code": null,
  "notes": "Optional notes"
}
```

Order creation uses the existing backend transaction and recalculates prices, discounts, shipping, payment fees, and stock.

## Orders

Authenticated:

```text
GET /orders
GET /orders/{id}
```

## Wallet

Authenticated:

```text
GET /wallet
GET /wallet/transactions
GET /wallet/deposits
POST /wallet/deposits
```

Deposit request body:

```json
{
  "amount": 100,
  "payment_method": "bank_transfer"
}
```

Optional multipart field:

```text
proof_image
```

Deposit requests are saved as `pending`. Admin approval in Filament credits the wallet.

Paginated wallet endpoints return:

```json
{
  "items": [],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 0
  },
  "links": {
    "prev": null,
    "next": null
  }
}
```

## Electronic Services

Public:

```text
GET /services/categories
GET /services
GET /services/{slug}
```

Authenticated:

```text
POST /services/{slug}/order
GET  /service-orders
GET  /service-orders/{id}
```

Service order body:

```json
{
  "fields": {
    "playerId": "123456"
  }
}
```

The service price and provider cost are always calculated by the backend. The provider token is never exposed to the mobile app.

## Product Reviews

Authenticated:

```text
POST /products/{product_id}/reviews
```

Multipart or JSON body:

```json
{
  "rating": 5,
  "title": "Great product",
  "comment": "Original and delivered in good condition"
}
```

Optional multipart field:

```text
images[]
```

Rules:

- The user must be logged in.
- The user must have a delivered or completed order containing the product.
- One review per user per product.
- New reviews are saved as `pending` until approved by admin.

## Notifications

Authenticated:

```text
GET  /notifications
GET  /notifications/unread-count
POST /notifications/{id}/read
POST /notifications/read-all
```

Notification response includes:

```text
id
type
title
message
data
action_url
is_read
read_at
created_at
```

## Testing Notes

Implemented and syntax-checked API areas:

- Sanctum authentication and profile.
- Home, categories, brands, products.
- Favorites and product reviews.
- Cart, checkout summary, place order endpoint.
- Orders.
- Wallet, wallet transactions, and wallet deposit requests.
- Electronic services and service orders.
- Notifications.

Sensitive operations such as final order creation and real provider service orders should be tested on disposable demo data to avoid unintended stock deduction, wallet debit, or provider order submission.
