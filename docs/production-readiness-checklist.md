# Production readiness checklist

Use this list before launching open_ecommerce_laravel in production. Combine with `php artisan open_ecommerce_laravel:check-installation`.

## Environment

- [ ] Copy `.env.example` to `.env`; never commit `.env`.
- [ ] `APP_ENV=production`, `APP_DEBUG=false`.
- [ ] `APP_URL` uses `https://` when TLS terminates at your domain.
- [ ] `APP_KEY` set (`php artisan key:generate` once per environment).
- [ ] Timezone and locale (`APP_TIMEZONE`, `APP_LOCALE`) match your store.

## Database

- [ ] Run all migrations: `php artisan migrate --force`.
- [ ] Seed only when intentional (demo data vs empty catalog).
- [ ] Database user has least privilege (no `SUPER` unless required).
- [ ] Regular backups enabled (see Spatie backup + `php artisan backup:run`).

## Application files

- [ ] `storage/` and `bootstrap/cache/` writable by the PHP/web user.
- [ ] `php artisan storage:link` so `public/storage` exists.
- [ ] `npm run build` for production assets; avoid running `npm run dev` on the server.

## Admin access

- [ ] At least one active admin exists; default seeded password changed.
- [ ] Admin URLs served only over HTTPS in production.

## Queue and scheduler

- [ ] If using queued mail/jobs: `QUEUE_CONNECTION` not `sync`, and a worker runs (`php artisan queue:work` or Supervisor/systemd).
- [ ] Cron for Laravel scheduler (see project `README.md`).
- [ ] Log rotation / disk space for `storage/logs`.

## Mail

- [ ] SMTP or provider credentials in `.env` or Admin → Settings → Mail.
- [ ] Send a test message from staging before go-live.

## Payments (PayHere)

- [ ] Merchant ID and secret set in Admin → Settings → Payments (or env).
- [ ] Sandbox vs live mode matches your rollout plan.
- [ ] Webhook URL `POST /payments/payhere/notify` reachable from PayHere (HTTPS).
- [ ] Never trust customer return URL alone for payment status; rely on notify + signature verification.

## HTTPS and proxies

- [ ] TLS certificate valid and auto-renewed (Let’s Encrypt or managed LB).
- [ ] If behind Cloudflare or another proxy: trust proxies (`TrustProxies` middleware / `APP_URL` scheme correct) so generated URLs and IP logging are accurate.

## Security headers and rate limits

- [ ] Security headers middleware is active (default in this project).
- [ ] Admin and customer login rate limits appropriate for your threat model.

## Monitoring

- [ ] Error tracking (Sentry, Flare, etc.) optional but recommended.
- [ ] Uptime checks on storefront and checkout critical paths.

## Backups

- [ ] `php artisan backup:run` succeeds on a clone of production config.
- [ ] Backup archives stored off-server (S3, another region, etc.).
- [ ] Restore drill performed at least once.
