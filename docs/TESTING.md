# Ranga — Manual Testing Guide

Base URL (local): `http://127.0.0.1:8000`  ·  API prefix: `/api/v1`

## 0. One-time local setup

```bash
composer update
cp .env.example .env            # if not already present
php83 artisan key:generate

# Make local testing dependency-free (no Meilisearch / Redis / gateways needed):
#   .env →
#     DB_CONNECTION=sqlite           (and: touch database/database.sqlite)
#     SCOUT_DRIVER=database
#     QUEUE_CONNECTION=sync
#     CACHE_STORE=database
#     MAIL_MAILER=log
#     SMS_DRIVER=log  WHATSAPP_DRIVER=log  PUSH_DRIVER=log

php83 artisan migrate:fresh --seed
php83 artisan serve            # http://127.0.0.1:8000
```

Seeded admin (from `DatabaseSeeder`): **admin@ranga.test / ChangeMe!123** (role super-admin).
Seeders also create sample categories, brands, products+variants+inventory, shipping
methods (`inside-dhaka`, `outside-dhaka`), payment methods, loyalty tiers, CMS content.

All API requests need: `Accept: application/json`. Authenticated requests need
`Authorization: Bearer <token>`. Guest carts use an `X-Cart-Token: <any-uuid>` header.

---

## 1. Browser-openable URLs (GET in any browser)

| URL | What |
|-----|------|
| `/` | Storefront home (Blade shell) |
| `/sitemap.xml` | Dynamic XML sitemap |
| `/robots.txt` | Robots file |
| `/manifest.json` | PWA manifest |
| `/sw.js` | Service worker |
| `/offline` | PWA offline page |
| `/horizon` | Queue dashboard (admin only, after `horizon:install`) |
| `/api/v1/settings` | Public white-label settings (JSON) |
| `/api/v1/products` | Product listing |
| `/api/v1/products/{slug}` | Product detail (+ JSON-LD, SEO) |
| `/api/v1/categories` | Category tree |
| `/api/v1/brands` | Brands |
| `/api/v1/search?q=saree` | Search |
| `/api/v1/flash-sales/active` · `/api/v1/bundles` | Promotions |
| `/api/v1/blog` · `/api/v1/blog/{slug}` | Blog |
| `/api/v1/i18n/locales` | Supported locales |
| `/api/v1/products/{slug}/qr` | QR PNG (image) |

---

## 2. Endpoint reference

### Auth — `/api/v1/auth/*`
`POST /register` · `POST /login` · `POST /social/{provider}` · `POST /forgot-password` ·
`POST /reset-password` · `GET /verify-email/{id}/{hash}` · `POST /logout` (auth) ·
`POST /verify-email/resend` (auth) · `POST /2fa/enable|confirm|disable` (auth) · `POST /2fa/verify`

### Catalogue (public)
`GET /products` · `/products/{slug}` · `/products/{slug}/variants` ·
`/products/{slug}/recommendations` · `/products/{slug}/qr` · `/products/{slug}/share` ·
`GET /categories` · `/categories/{slug}/products` · `GET /brands` · `/brands/{slug}/products` ·
`GET /search` · `/search/suggestions` ·
`GET /products/compare` · `POST|DELETE /products/compare/{product}`

### Cart & checkout (guest via `X-Cart-Token`, or auth)
`GET /cart` · `DELETE /cart` · `POST /cart/items` · `PUT|DELETE /cart/items/{item}` ·
`POST /cart/items/{item}/save-for-later` · `POST /cart/saved/{saved}/move` ·
`POST|DELETE /cart/coupon` · `POST /cart/gift-card` ·
`GET /checkout/shipping-methods` · `POST /checkout` ·
`GET|POST /checkout/payment/{gateway}/callback`

### Marketing / AI / bonus (public)
`POST /chat` · `GET /recommendations` · `GET /flash-sales/active` · `GET /bundles` ·
`GET /bundles/{slug}` · `GET /aff/{code}` · `GET /i18n/locales` · `POST /back-in-stock` ·
`GET /blog` · `/blog/categories` · `/blog/{slug}` · `GET /banners` · `/popups` ·
`/menus/{location}` · `/pages/{slug}`

### Authenticated customer (`Bearer`)
`GET|PUT /profile` · `GET /profile/loyalty` · `GET /profile/orders` ·
`GET /profile/wishlist` · `POST|DELETE /profile/wishlist/{product}` ·
`GET /notifications` · `POST /notifications/read-all` · `POST /notifications/{id}/read` ·
`POST|DELETE /profile/push-subscriptions` ·
`GET /profile/subscriptions` · `POST /subscriptions` · `POST /subscriptions/{id}/pause|resume` · `DELETE /subscriptions/{id}` ·
`GET /orders/{number}` · `POST /orders/{number}/cancel` · `POST /orders/{number}/return` ·
`GET /orders/{number}/tracking` · `GET /orders/{number}/invoice`

### Admin (`Bearer` admin + permission) — `/api/v1/admin/*`
`GET|PUT /settings` ·
`PUT /orders/{number}/status` · `POST /orders/{number}/shipments` ·
`GET /returns` · `POST /returns/{return}/approve|reject` ·
`GET|POST /coupons` · `POST /gift-cards` · `POST /flash-sales` · `POST /bundles` ·
`GET|POST /pages` · `DELETE /pages/{page}` · `GET|POST /banners` · `DELETE /banners/{banner}` ·
`GET /blog/posts` · `POST /blog/categories` · `POST /blog/posts` · `DELETE /blog/posts/{post}` ·
`POST /menus` · `GET /menus/{menu}` · `GET|POST /popups` · `DELETE /popups/{popup}` ·
`PUT /seo` ·
`GET /reports/dashboard|sales|customers|inventory` ·
`GET /customers` · `GET /customers/{user}` · `POST /customers/{user}/loyalty` ·
`POST /ai/product-description|seo-meta|tags`

---

## 3. Happy-path walkthrough (copy/paste curl)

```bash
BASE=http://127.0.0.1:8000/api/v1

# 1. Register a customer
curl -s -X POST $BASE/auth/register -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Anika","email":"anika@example.com","phone":"01712345678",
       "password":"secret-password","password_confirmation":"secret-password"}'

# 2. Login -> capture token
TOKEN=$(curl -s -X POST $BASE/auth/login -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"anika@example.com","password":"secret-password"}' | php -r 'echo json_decode(file_get_contents("php://stdin"))->token;')
echo "TOKEN=$TOKEN"

# 3. Browse — grab a product slug + variant id
curl -s "$BASE/products?per_page=3" -H 'Accept: application/json'
#    note a "slug" then:
curl -s "$BASE/products/<slug>" -H 'Accept: application/json'   # variants[].id

# 4. Add to cart (authenticated)
curl -s -X POST $BASE/cart/items -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"product_id":"<product_id>","variant_id":"<variant_id>","quantity":1}'

# 5. Shipping options
curl -s $BASE/checkout/shipping-methods -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json'

# 6. Place a COD order
curl -s -X POST $BASE/checkout -H "Authorization: Bearer $TOKEN" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"shipping":{"name":"Anika","phone":"01712345678","address_line_1":"12 Rd","city":"Dhaka"},
       "shipping_method":"inside-dhaka","payment_gateway":"cod"}'
#    -> returns data.order_number

# 7. View order + history
curl -s $BASE/profile/orders -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json'
curl -s $BASE/orders/<order_number> -H "Authorization: Bearer $TOKEN" -H 'Accept: application/json'
```

### Admin flow

```bash
ATOKEN=$(curl -s -X POST $BASE/auth/login -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"email":"admin@ranga.test","password":"ChangeMe!123"}' | php -r 'echo json_decode(file_get_contents("php://stdin"))->token;')

# Dashboard KPIs
curl -s $BASE/admin/reports/dashboard -H "Authorization: Bearer $ATOKEN" -H 'Accept: application/json'

# Advance an order + ship it (triggers SMS/WhatsApp logs + notification)
curl -s -X PUT $BASE/admin/orders/<order_number>/status -H "Authorization: Bearer $ATOKEN" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"status":"processing","comment":"Packing","notify_customer":true}'

curl -s -X POST $BASE/admin/orders/<order_number>/shipments -H "Authorization: Bearer $ATOKEN" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"tracking_number":"TRACK123","carrier":"Pathao"}'

# AI (needs OPENAI_API_KEY or GEMINI_API_KEY in .env)
curl -s -X POST $BASE/admin/ai/product-description -H "Authorization: Bearer $ATOKEN" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' \
  -d '{"name":"Crimson Silk Saree","category":"Sarees"}'
```

### Online payment (SSLCommerz sandbox) — optional
Set `SSLCOMMERZ_*` in `.env`, checkout with `"payment_gateway":"sslcommerz"` →
response `payment.redirect_url`. After paying, the gateway hits
`POST /checkout/payment/sslcommerz/callback` which marks the order paid.

### Guest cart (no login)
Send `X-Cart-Token: my-guest-123` on every `/cart*` and `/checkout` call, and include
`"guest_email":"guest@example.com"` in the checkout body.

---

## 4. Tips

- Inspect every route + name: `php83 artisan route:list`
- Localise responses: add header `X-Locale: bn` (or `en`).
- Emails/SMS/WhatsApp use the **log** driver locally → see `storage/logs/laravel.log`.
- Background jobs run inline with `QUEUE_CONNECTION=sync`; for real queues use Redis + `php83 artisan horizon`.
- Scheduled jobs (abandoned cart, subscription renewal): `php83 artisan schedule:run` or run the command directly, e.g. `php83 artisan ranga:renew-subscriptions`.
