# Ranga — রঙ্গা

> Enterprise, white-label, single-vendor e-commerce platform for the Bangladesh fashion market.
> **Stack:** Laravel 12 · PHP 8.3 · MySQL 8 · Redis 7 · Blade + Livewire 3 + Alpine.js + Tailwind CSS · Docker · GitHub Actions

Dresses that speak your colour — রঙিন পোশাক, রঙিন জীবন

---

## Project status — Phase 1 (Foundation) ✅

| Deliverable | Status |
| --- | --- |
| Laravel 12 / PHP 8.3 project scaffold (`strict_types=1` everywhere) | ✅ |
| Docker (multi-stage Dockerfile, full compose stack) | ✅ |
| GitHub Actions CI (tests + PHPStan level 6) & deploy workflow | ✅ |
| Auth: register, login, email verification, TOTP 2FA, Google/Facebook OAuth, password reset | ✅ |
| ULID primary keys, soft deletes, indexed migrations | ✅ |
| Repository Pattern + Service Layer + Form Requests + Policies | ✅ |
| Events, Listeners, Queue Jobs (retry/backoff) | ✅ |
| Admin guard + role/permission matrix (Spatie) | ✅ |
| Settings module (white-label, cached) | ✅ |
| Feature + Unit test suite — **61 tests, 178 assertions, green** | ✅ |
| Localisation: `bn` (default) + `en`, BDT currency, Asia/Dhaka TZ | ✅ |

## Quick start (local, Docker)

```bash
cp .env.example .env

# Build and boot the full stack
docker compose up -d --build

# First-time setup
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

| Service | URL |
| --- | --- |
| Storefront | http://localhost:8000 |
| Mailpit (local email) | http://localhost:8025 |
| Soketi (websockets) | ws://localhost:6001 |

## Quick start (without Docker)

Requires PHP 8.3+, Composer 2, Node 22+, SQLite/MySQL.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # or point .env at MySQL
php artisan migrate --seed
npm install && npm run dev
php artisan serve
```

## Running tests & static analysis

```bash
php artisan test                       # 61 tests, 178 assertions
vendor/bin/phpstan analyse             # level 6, zero errors
```

## Architecture

```
app/
├── Events/Auth/            UserRegistered, UserLoggedIn, LoginFailed, PasswordResetRequested
├── Http/
│   ├── Controllers/Api/V1/ Thin controllers — call Services only
│   ├── Requests/           Form Request validation (never in controllers)
│   └── Resources/          API transformers
├── Jobs/Auth/              SendEmailVerification, SendPasswordResetEmail, LogLoginActivity
├── Listeners/Auth/         Bridge events → queue jobs
├── Models/                 User, SocialAccount, LoginActivity, TwoFactorChallenge, Setting
├── Notifications/Auth/     Queued verify-email & reset-password notifications
├── Policies/               UserPolicy, SettingPolicy
├── Repositories/
│   ├── Contracts/          UserRepositoryContract, SettingRepositoryContract
│   └── Eloquent/           Eloquent implementations (bound in RepositoryServiceProvider)
├── Services/
│   ├── Auth/               AuthService, TwoFactorService, SocialAuthService
│   └── Settings/           SettingsService (cached)
└── Support/
    ├── Dto/                AuthResult (readonly)
    └── Enums/              LoginStatus, SocialProvider
```

**Non-negotiable rules implemented:** SOLID, Repository Pattern for all DB access, Service Layer
for all business logic, Form Requests for validation, Policies for authorization, Events/Listeners
for side effects, Queue Jobs for async work, Sanctum API auth, PHPDoc on every public method,
PHP 8.3 enums for status fields, zero raw SQL, all config via `.env`.

## API surface (v1)

All routes are prefixed with `/api/v1`. Auth: `Authorization: Bearer <token>` (Sanctum).

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/auth/register` | Register (queues verification email) |
| POST | `/auth/login` | Login → token, or 2FA challenge |
| POST | `/auth/social/{provider}` | Google / Facebook token exchange |
| POST | `/auth/2fa/verify` | Complete 2FA challenge (TOTP or recovery code) |
| POST | `/auth/2fa/enable` 🔒 | Begin TOTP enrolment (secret + QR + recovery codes) |
| POST | `/auth/2fa/confirm` 🔒 | Confirm enrolment with TOTP |
| POST | `/auth/2fa/disable` 🔒 | Disable 2FA |
| POST | `/auth/forgot-password` | Queue reset link (enumeration-safe) |
| POST | `/auth/reset-password` | Reset with broker token |
| GET | `/auth/verify-email/{id}/{hash}` | Signed verification link |
| POST | `/auth/verify-email/resend` 🔒 | Re-send verification |
| POST | `/auth/logout` 🔒 | Revoke current token |
| GET/PUT | `/profile` 🔒 | Read / update profile |
| GET | `/settings` | Public white-label settings |
| GET/PUT | `/admin/settings` 🔒👑 | Manage settings (admin role) |

Rate limits: auth 5/min · API 60/min · password reset 3/15 min · search 30/min.

## White-label configuration

Brand identity lives in `config/ranga.php` + the `settings` table — **no code changes per deployment**:

```dotenv
RANGA_BRAND_NAME="Ranga"
RANGA_BRAND_TAGLINE="Dresses that speak your colour"
RANGA_BRAND_COLOR="#e11d48"
RANGA_DEFAULT_LOCALE=bn
RANGA_CURRENCY=BDT
RANGA_CURRENCY_SYMBOL="৳"
RANGA_DEFAULT_TIMEZONE=Asia/Dhaka
```

## Environment variables

See `.env.example` for the full list. Key groups: app/brand (`RANGA_*`), database (`DB_*`),
Redis/queues (`REDIS_*`), mail (`MAIL_*`), OAuth (`GOOGLE_*`, `FACEBOOK_*`),
Sanctum (`SANCTUM_TOKEN_EXPIRATION` — default 30 days).

## Deployment

Pushing to `main` triggers `.github/workflows/deploy.yml`: SSH to the server, `git pull`,
`composer install --no-dev`, `npm run build`, `php artisan migrate --force`, cache warm-up,
`horizon:terminate` (zero-downtime restart). Configure the `DEPLOY_*` repository secrets.

## Roadmap

Phase 1 (this) → **Phase 2:** Catalogue (categories, brands, products, variants, inventory, Scout/Meilisearch)
→ Phase 3: Cart & checkout (SSLCommerz, bKash, Stripe, COD) → Phase 4: Orders → Phase 5: Marketing
→ Phase 6: Admin panel → Phase 7: AI & notifications → Phase 8: Performance/SEO → Phase 9: PWA & bonus
→ Phase 10: QA & launch.
