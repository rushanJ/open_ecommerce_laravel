# open_ecommerce_laravel

> **Alpha software:** open_ecommerce_laravel is in **early open-source release**. Review **security**, **payments**, **tax**, and **legal** requirements before production use. See [SECURITY.md](SECURITY.md) and [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

**open_ecommerce_laravel** is an open-source **Laravel** e-commerce platform (modular monolith): **Blade + Tailwind** storefront, **admin panel** with RBAC, catalog, cart & checkout, **PayHere** payments, orders, CMS, reports, **REST APIs** (customer + admin token API), product **CSV import/export**, and **webhooks**. It targets **Sri Lanka** defaults (e.g. timezone, PayHere) while remaining usable elsewhere.

- **Version:** see [`VERSION`](VERSION) and [`CHANGELOG.md`](CHANGELOG.md)
- **License:** [MIT](LICENSE)
- **Project site:** [open-ecommerce-laravel.test](https://open-ecommerce-laravel.test) (when published)

---

## Features (high level)

| Area | Highlights |
|------|------------|
| **Storefront** | Products, categories, brands, cart, checkout, customer accounts (Breeze) |
| **Admin** | Products, variants, attributes, inventory, orders, payments, coupons, CMS, SEO, settings, RBAC |
| **Payments** | PayHere (sandbox/live), payment records & webhooks |
| **APIs** | Customer API (Sanctum), Admin read API (API tokens), documented in `docs/` |
| **Ops** | Spatie backup, scheduler-ready, installation & release check commands |

---

## Tech stack

- **PHP** 8.3+, **Laravel** 11/12+
- **MySQL** 8+ (or compatible MariaDB)
- **Blade**, **Tailwind CSS**, **Vite**
- **Composer**, **Node.js** 20+ / **npm**
- **Nginx** or **Apache** (production); `php artisan serve` for local

---

## Requirements

| Requirement | Notes |
|-------------|--------|
| **PHP** | 8.3+ with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `json`, `ctype`, `bcmath`, `fileinfo`, GD or Imagick |
| **Composer** | 2.x |
| **Node.js** | 20+ and npm (for Vite assets) |
| **MySQL** | 8+; empty database before first migrate |
| **Web server** | Must point document root to `public/` |

---

## Local installation

```bash
git clone https://github.com/<your-org>/open_ecommerce_laravel.git
cd open_ecommerce_laravel
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create a MySQL database named `open_ecommerce_laravel` (or match `DB_DATABASE` in `.env`), set `DB_USERNAME` / `DB_PASSWORD`, then:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

- **Storefront:** [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Admin:** [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

**Detailed install** (Windows/XAMPP, Linux VPS, shared hosting): [docs/INSTALL.md](docs/INSTALL.md)

---

## Default admin login

After seeding, use the values from `.env`:

| Field | Default (from `.env.example`) |
|-------|--------------------------------|
| **Email** | `OPEN_ECOMMERCE_LARAVEL_ADMIN_EMAIL` → `admin@open-ecommerce-laravel.test` |
| **Password** | `OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD` → `password` |

### Change the default password

1. Log in to **Admin** and change password from your profile (recommended), **or**
2. Use `php artisan tinker` and `Hash::make('new-secret')` to update the `admin_users` row, **or**
3. Set a new `OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD` in `.env` and run `php artisan migrate:fresh --seed` (⚠️ **wipes the database**).

---

## PayHere setup

1. Set `PAYHERE_ENABLED`, `PAYHERE_MODE`, `PAYHERE_MERCHANT_ID`, `PAYHERE_MERCHANT_SECRET` in `.env`, **and/or**
2. Configure **Admin → Settings → Payments** (database settings may override env depending on your build).
3. Use **sandbox** keys until you are ready for live charges.
4. Ensure your **notify URL** is reachable over **HTTPS** in production.

---

## Mail setup

Set `MAIL_MAILER=smtp` and fill `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` (`tls` / `ssl`), plus `MAIL_FROM_ADDRESS` / `MAIL_FROM_NAME`. Test with a password reset or transactional email.

---

## Queue setup

Default `.env.example` uses `QUEUE_CONNECTION=database`. After migrations, run a worker:

```bash
php artisan queue:work
```

For production, use **Supervisor** or systemd (see [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)).

---

## Scheduler setup

Register Laravel’s scheduler once per minute (replace the path):

```cron
* * * * * cd /path/to/open_ecommerce_laravel && php artisan schedule:run >> /dev/null 2>&1
```

Attach backups or other tasks in `routes/console.php` as needed.

---

## Backup command

Backups use **Spatie Laravel Backup**. Configure `config/backup.php` and destinations.

```bash
php artisan backup:run
php artisan backup:clean
```

`.env` is **not** included in archives by default.

---

## Testing

```bash
php artisan test
# or
composer test
```

Code style:

```bash
composer pint
```

---

## Installation & release checks

```bash
php artisan open_ecommerce_laravel:check-installation
php artisan open_ecommerce_laravel:release-check
```

`release-check` validates **APP_KEY**, **storage** writability, **DB**, **admin** presence, **storage link**, **queue table** (when using database queue), and warns on **default admin password**, **PayHere env**, and **mail** placeholders. It does **not** run PHPUnit—run `composer test` separately.

---

## Troubleshooting

| Issue | What to try |
|-------|-------------|
| Database connection errors | Verify `DB_*`, MySQL running, user grants |
| 500 / blank page | `APP_DEBUG=true` temporarily; read `storage/logs/laravel.log` |
| Assets 404 / Vite manifest missing | `npm install && npm run build` |
| Uploads 404 | `php artisan storage:link`; check `public/storage` |
| Permission errors on Linux | `chown`/`chmod` on `storage/` and `bootstrap/cache/` (see [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)) |

---

## Documentation

| Document | Purpose |
|----------|---------|
| [docs/INSTALL.md](docs/INSTALL.md) | Step-by-step install (Windows, Linux, shared hosting) |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Production checklist, Nginx, Supervisor, caching |
| [SECURITY.md](SECURITY.md) | Reporting vulnerabilities, expectations |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Branches, PRs, Pint, tests |
| `docs/` | API, webhooks, samples |

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Issues and PRs welcome.

---

## License

open_ecommerce_laravel is open-source under the [MIT License](LICENSE).
