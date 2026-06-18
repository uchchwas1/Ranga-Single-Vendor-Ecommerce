# Ranga — Security Audit

This document audits the platform against the Section 2.11 security checklist
and records where each control is implemented.

| # | Control | Status | Where |
|---|---------|--------|-------|
| 1 | Two-Factor Authentication (TOTP) | ✅ | `TwoFactorService`, `pragmarx/google2fa`; enforced for admin policy, optional for customers |
| 2 | Google reCAPTCHA v3 on login/register/checkout | ⚠️ Pending | Hook in `RegisterRequest`/`LoginRequest`/`PlaceOrderRequest` + a `recaptcha` rule; keys in `.env` |
| 3 | Rate limiting (login 5/min, API 60/min, search 30/min, reset 3/15min) | ✅ | `AppServiceProvider::configureRateLimiting` + `throttle:*` on routes |
| 4 | Admin routes behind guard + role check | ✅ | `/api/v1/admin/*` group `auth:sanctum` + per-request `can('*.manage')`; `admin` guard configured |
| 5 | CSRF protection on non-API forms | ✅ | Laravel web middleware group (stateless API uses Sanctum tokens) |
| 6 | SQL-injection prevention (parameterised) | ✅ | Eloquent/query-builder only — no raw SQL anywhere (`SecurityAuditTest::test_search_is_injection_safe`) |
| 7 | XSS prevention (Blade escaping + CSP) | ✅ | Blade `{{ }}` + `SecurityHeaders` CSP |
| 8 | File-upload validation (mime + size, private ACL) | ⚠️ Partial | Image fields stored as paths; enforce mime/size rules on upload endpoints when added; S3 private ACL via `config/filesystems` |
| 9 | Audit logs on admin/sensitive actions | ⚠️ Partial | `inventory_logs`, `order_status_histories`, `payment_gateways_log`; extend with a generic `activity_logs` writer |
| 10 | Login activity monitoring (device/IP/geo) | ✅ | `login_activities` + `LogLoginActivity` job + `UserAgentParser` |
| 11 | Encrypted gateway credentials (AES-256) | ✅ | `PaymentMethod::$casts` `config => encrypted:array`; `two_factor_secret` encrypted |
| 12 | Sanctum token expiry (30 days), rotate on sensitive ops | ✅ | `SANCTUM_TOKEN_EXPIRATION`; tokens revoked on logout |
| 13 | Device/session management (list/revoke) | ⚠️ Partial | Sanctum tokens revocable; expose a token-list/revoke endpoint for full coverage |
| 14 | Honeypot fields on public forms | ⚠️ Pending | Add a honeypot rule to register/contact requests |
| 15 | Security headers (HSTS, X-Frame-Options, CSP) | ✅ | `SecurityHeaders` middleware (global) |

## Automated checks

- `tests/Feature/Qa/SecurityAuditTest` — headers, rate-limit headers, injection-safe search, password hashing, secret hiding.
- `tests/Feature/Qa/RouteGuardTest` — every protected admin/customer endpoint rejects guests.
- `tests/Feature/Performance/SecurityHeadersTest` — header presence.

## Recommended pre-launch hardening

1. Add reCAPTCHA v3 and honeypot to public forms (#2, #14).
2. Add a generic audit-log writer for all admin mutations (#9).
3. Add a Sanctum device-management endpoint (#13).
4. Run `composer audit` and enable Dependabot.
5. Set `APP_DEBUG=false`, real `APP_KEY`, HTTPS-only cookies, `SESSION_SECURE_COOKIE=true` in production.
6. Run `vendor/bin/phpstan analyse` (level 6) and resolve all findings in CI.
