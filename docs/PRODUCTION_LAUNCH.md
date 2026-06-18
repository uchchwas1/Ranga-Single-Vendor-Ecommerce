# Ranga — Production Launch Checklist

## 1. Environment & secrets
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, real `APP_KEY` generated
- [ ] `APP_URL` set to the production domain (HTTPS)
- [ ] DB (MySQL 8 / PostgreSQL 15) credentials set; Redis 7 reachable
- [ ] `SESSION_SECURE_COOKIE=true`, `SESSION_DRIVER=redis`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`
- [ ] Mail, SMS, WhatsApp, push sender credentials configured (bindings swapped from log drivers)
- [ ] Payment gateways live (SSLCommerz/bKash/Stripe) with `*_SANDBOX=false`
- [ ] AI provider key (`OPENAI_API_KEY` or `GEMINI_API_KEY`) set
- [ ] Meilisearch host/key set; `SCOUT_DRIVER=meilisearch`
- [ ] imgproxy URL + key/salt set for signed image URLs

## 2. Build & migrate
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `npm ci && npm run build`
- [ ] `php artisan migrate --force`
- [ ] `php artisan db:seed --class=RoleSeeder` (+ `LoyaltyTierSeeder`, `CommerceSeeder` as needed)
- [ ] `php artisan scout:import "App\\Models\\Product"`
- [ ] `php artisan config:cache route:cache event:cache view:cache`
- [ ] `php artisan storage:link`

## 3. Workers & schedule
- [ ] Horizon running under Supervisor (`docker/supervisor/horizon.conf`)
- [ ] Scheduler cron active (`* * * * * php artisan schedule:run`) — abandoned-cart + subscription renewal
- [ ] `php artisan horizon:install` assets published; `/horizon` gated to admins

## 4. Security (see docs/SECURITY.md)
- [ ] reCAPTCHA + honeypot on public forms
- [ ] `composer audit` clean; Dependabot enabled
- [ ] HTTPS enforced (HSTS preload), CSP reviewed for CDN/asset origins
- [ ] Rate limits verified; admin routes role-gated

## 5. Quality gates (CI must be green)
- [ ] `php artisan test` (Feature + Unit) green
- [ ] `vendor/bin/phpstan analyse --memory-limit=1G` (level 6) clean
- [ ] `php artisan dusk` browser smoke tests green (staging)
- [ ] k6 load tests within thresholds: `k6 run k6/catalogue.js` / `search.js` / `checkout.js`

## 6. Observability & ops
- [ ] Error tracking (Sentry/Flare) wired
- [ ] Uptime + queue depth (Horizon) monitoring/alerts
- [ ] Database + media (S3) backups scheduled and restore-tested
- [ ] CDN (CloudFront) in front of static + media with cache headers

## 7. SEO & PWA
- [ ] `/sitemap.xml` reachable and submitted to Google Search Console
- [ ] `/robots.txt` correct for environment
- [ ] `/manifest.json` + `/sw.js` served; install prompt verified
- [ ] Structured data validated (Rich Results Test)

## 8. Go-live
- [ ] DNS cutover; SSL certificate valid
- [ ] Smoke test: register → browse → cart → COD checkout → order visible
- [ ] `php artisan up`; monitor logs/Horizon for the first hour
