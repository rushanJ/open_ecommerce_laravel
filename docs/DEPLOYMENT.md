# open_ecommerce_laravel — Production deployment

Use this checklist when deploying open_ecommerce_laravel to a **production** server. Pair with [INSTALL.md](INSTALL.md) for first-time server setup.

---

## Pre-deploy backup

- Dump the database and archive `storage/app/public` (or your whole `storage/` if you store private files).
- Tag the Git commit you are deploying: `git tag -a v0.1.0 -m "Release"`.

---

## Environment (.env)

| Variable | Production value |
|----------|------------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://your-domain.com` (no trailing slash issues—use canonical URL) |
| `APP_KEY` | From `php artisan key:generate` (never commit `.env`) |
| `DB_*` | Strong password; least-privilege DB user |
| `SESSION_*` | Prefer `database` or `redis` at scale; `secure` cookies on HTTPS |
| `CACHE_STORE` | `redis` or `database` for multi-node; `file` is OK for single server |
| `QUEUE_CONNECTION` | `database` or `redis` with a **worker** process |
| `MAIL_*` | Real SMTP / provider; test outbound mail |

---

## HTTPS

- Terminate TLS at Nginx, Caddy, or a load balancer.
- Redirect HTTP → HTTPS.
- Set `APP_URL` to `https://...`.

---

## File permissions

```bash
cd /var/www/open_ecommerce_laravel
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

Ensure `php artisan storage:link` has been run once.

---

## Nginx sample (PHP-FPM)

Replace paths and `server_name` with your values.

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name shop.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name shop.example.com;
    root /var/www/open_ecommerce_laravel/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # ssl_certificate /etc/letsencrypt/live/shop.example.com/fullchain.pem;
    # ssl_certificate_key /etc/letsencrypt/live/shop.example.com/privkey.pem;
}
```

---

## PHP-FPM notes

- Match **PHP version** to your CLI (`php -v`).
- Tune `pm.max_children` based on RAM.
- Set `upload_max_filesize` and `post_max_size` for admin media uploads.

---

## Supervisor — queue worker

`/etc/supervisor/conf.d/open_ecommerce_laravel-worker.conf`:

```ini
[program:open_ecommerce_laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/open_ecommerce_laravel/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/open_ecommerce_laravel/storage/logs/worker.log
stopwaitsecs=3600
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start open_ecommerce_laravel-worker:*
```

Adjust `database` if you use `redis` for queues.

---

## Scheduler (cron)

```cron
* * * * * cd /var/www/open_ecommerce_laravel && php artisan schedule:run >> /dev/null 2>&1
```

Add backup cleanup or other tasks in `routes/console.php` as needed.

---

## Laravel optimization (after deploy)

```bash
cd /var/www/open_ecommerce_laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Clear caches when changing `.env` or routes:

```bash
php artisan optimize:clear
```

---

## Rollback strategy

1. **Code:** `git checkout <previous-tag>` and run `composer install`, `npm ci && npm run build`, then `php artisan migrate` (only if new migrations are backward-compatible; otherwise restore DB backup).
2. **Database:** restore the SQL dump taken before deploy.
3. **Config:** `php artisan optimize:clear` then `config:cache` again.

---

## Post-deploy verification

```bash
php artisan open_ecommerce_laravel:release-check
php artisan open_ecommerce_laravel:check-installation
```

Manually verify: storefront, admin login, checkout (sandbox), mail, and HTTPS.

---

## Backup before deploy

```bash
php artisan backup:run
```

Configure `config/backup.php` and off-site disks for production archives.
