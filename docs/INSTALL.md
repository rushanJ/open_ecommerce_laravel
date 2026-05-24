# open_ecommerce_laravel — Installation guide

This guide helps you install open_ecommerce_laravel from a Git clone on **Windows (XAMPP/WAMP)** or a **Linux VPS** (Ubuntu + Nginx + MySQL). For production hardening, see [DEPLOYMENT.md](DEPLOYMENT.md).

## Prerequisites

- **PHP** 8.3+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` or `imagick` (recommended for images)
- **Composer** 2.x
- **Node.js** 20+ and **npm**
- **MySQL** 8+ (or MariaDB 10.6+)
- **Git**

---

## Quick install (any OS, local)

```bash
git clone https://github.com/<your-org>/open_ecommerce_laravel.git
cd open_ecommerce_laravel
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database (example name `open_ecommerce_laravel`), then set `DB_*` in `.env`.

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

- **Storefront:** `http://127.0.0.1:8000`
- **Admin:** `http://127.0.0.1:8000/admin`

### Default admin login

After `php artisan migrate --seed`, sign in with the credentials from `.env`:

- **Email:** value of `OPEN_ECOMMERCE_LARAVEL_ADMIN_EMAIL` (default `admin@open-ecommerce-laravel.test`)
- **Password:** value of `OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD` (default `password`)

**Change the default password immediately:** Admin panel → your profile / password, or use `php artisan tinker` to set a new hash. Update `OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD` in `.env` before re-seeding if you rely on seeders for fresh installs.

---

## Windows (XAMPP / WAMP)

1. Install **XAMPP** (or WAMP) with PHP 8.3+, Apache, MySQL, and enable `openssl`, `pdo_mysql` in `php.ini`.
2. Clone the repo into `htdocs\open_ecommerce_laravel` (or your vhost directory).
3. Open **Git Bash** or **PowerShell** in the project folder and run the [Quick install](#quick-install-any-os-local) commands.
4. **Composer / PHP path:** if `php` or `composer` are not in PATH, use full paths, e.g. `C:\xampp\php\php.exe artisan ...`
5. **Node:** install Node 20 LTS from nodejs.org; run `npm install` and `npm run build` from the project root.
6. **Database:** use phpMyAdmin or MySQL CLI to create database `open_ecommerce_laravel`, user with privileges, then fill `DB_*` in `.env`.
7. **Apache virtual host (optional):** point `DocumentRoot` to `open_ecommerce_laravel/public` (not the repo root).

### File permissions (Windows)

Usually not an issue. Ensure the web server user can write to:

- `storage/`
- `bootstrap/cache/`

---

## Linux VPS (Ubuntu + Nginx + MySQL)

### 1. System packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.3-fpm php8.3-mysql php8.3-xml php8.3-mbstring php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd unzip git
```

Install **Composer** and **Node** (e.g. via NodeSource for Node 20).

### 2. Database

```bash
sudo mysql -e "CREATE DATABASE open_ecommerce_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER 'open_ecommerce_laravel'@'localhost' IDENTIFIED BY 'strong-password-here';"
sudo mysql -e "GRANT ALL PRIVILEGES ON open_ecommerce_laravel.* TO 'open_ecommerce_laravel'@'localhost'; FLUSH PRIVILEGES;"
```

### 3. Deploy code

```bash
sudo mkdir -p /var/www/open_ecommerce_laravel
sudo chown -R $USER:www-data /var/www/open_ecommerce_laravel
cd /var/www/open_ecommerce_laravel
git clone <repository-url> .
composer install --no-dev --optimize-autoloader
cp .env.example .env
nano .env   # set APP_URL, DB_*, mail, etc.
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm ci && npm run build
```

### 4. Permissions

```bash
cd /var/www/open_ecommerce_laravel
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

### 5. Nginx

Point the site root to `.../open_ecommerce_laravel/public`. See [DEPLOYMENT.md](DEPLOYMENT.md) for a sample server block.

### 6. PHP-FPM

Ensure `php8.3-fpm` is running and the Nginx `fastcgi_pass` socket matches your distro (`/run/php/php8.3-fpm.sock`).

### 7. Queue worker

If `QUEUE_CONNECTION=database` in `.env`, run a worker (Supervisor recommended):

```bash
php artisan queue:work --sleep=3 --tries=3
```

See [DEPLOYMENT.md](DEPLOYMENT.md) for a Supervisor example.

### 8. Scheduler (cron)

```cron
* * * * * cd /var/www/open_ecommerce_laravel && php artisan schedule:run >> /dev/null 2>&1
```

---

## Shared hosting notes

- Prefer hosts that offer **SSH**, **PHP 8.3+**, and **MySQL**.
- Document root must be the `public/` directory (not the repository root).
- If you cannot run `npm run build` on the server, build assets locally and upload `public/build/` (not ideal; SSH is strongly preferred).
- `php artisan storage:link` may be disallowed; some panels offer a “storage link” toggle—create the symlink from `public/storage` → `storage/app/public`.
- Set `memory_limit` and `max_execution_time` high enough for migrations and image processing.

---

## Storage link

Required so uploaded media and public files resolve:

```bash
php artisan storage:link
```

Creates `public/storage` → `storage/app/public`.

---

## PayHere (payments)

1. Set `PAYHERE_*` in `.env` **or** configure via **Admin → Settings → Payments** (database settings override env in many installs).
2. Use **sandbox** keys until you go live; then switch mode and live credentials.
3. Ensure the PayHere notify URL is reachable over HTTPS in production.

---

## Mail

For real email, set `MAIL_MAILER=smtp` and `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` (e.g. `tls`), and `MAIL_FROM_*`. Test with a password reset or order email.

---

## Common errors

| Symptom | Fix |
|--------|-----|
| `SQLSTATE[HY000] [1045] Access denied` | Check `DB_USERNAME` / `DB_PASSWORD` / host |
| `Base table or view not found` | Run `php artisan migrate` |
| `Please provide a valid cache path` | `chmod` on `storage/` and `bootstrap/cache` |
| `Vite manifest not found` | Run `npm install && npm run build` |
| 500 with blank page | Set `APP_DEBUG=true` temporarily; check `storage/logs/laravel.log` |
| Mixed content / wrong URLs | Set `APP_URL` to your canonical HTTPS URL |

---

## Next steps

- Run `php artisan open_ecommerce_laravel:check-installation` after first setup.
- Before production: `php artisan open_ecommerce_laravel:release-check` and read [DEPLOYMENT.md](DEPLOYMENT.md).
