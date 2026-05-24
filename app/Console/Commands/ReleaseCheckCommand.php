<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReleaseCheckCommand extends Command
{
    protected $signature = 'open_ecommerce_laravel:release-check';

    protected $description = 'Pre-release sanity checks for open_ecommerce_laravel (self-hosted / production readiness).';

    public function handle(): int
    {
        $this->info('open_ecommerce_laravel release check');
        $this->newLine();

        $failed = false;

        $failed = ! $this->check('APP_KEY is set (non-empty)', function (): bool {
            $key = (string) config('app.key', '');

            return $key !== '' && $key !== 'base64:';
        }) || $failed;

        if (config('app.env') === 'production' && config('app.debug')) {
            $this->warn('[WARN] APP_DEBUG is true while APP_ENV is production. Set APP_DEBUG=false before release.');
        } else {
            $this->line('[ OK ] APP_DEBUG vs APP_ENV looks acceptable.');
        }

        $failed = ! $this->check('Database connection', function (): bool {
            try {
                DB::connection()->getPdo();

                return true;
            } catch (\Throwable) {
                return false;
            }
        }) || $failed;

        $failed = ! $this->check('Core tables migrated (admin_users exists)', function (): bool {
            try {
                return Schema::hasTable('admin_users');
            } catch (\Throwable) {
                return false;
            }
        }) || $failed;

        $failed = ! $this->check('At least one admin user exists', function (): bool {
            try {
                return AdminUser::query()->exists();
            } catch (\Throwable) {
                return false;
            }
        }) || $failed;

        $this->line('');
        $this->info('Warnings & operational notes');
        $this->line('');

        $this->warnDefaultAdminPassword();
        $this->warnPayHereEnv();
        $this->warnMailEnv();

        $failed = ! $this->check('storage/ is writable', fn (): bool => is_writable(storage_path())) || $failed;
        $failed = ! $this->check('bootstrap/cache is writable', fn (): bool => is_writable(base_path('bootstrap/cache'))) || $failed;

        $linkOk = is_dir(public_path('storage')) || is_link(public_path('storage'));
        if (! $linkOk) {
            $this->warn('[WARN] Public storage link missing. Run: php artisan storage:link');
            $failed = true;
        } else {
            $this->line('[ OK ] Public storage link exists.');
        }

        $queue = (string) config('queue.default', 'sync');
        if ($queue === 'database') {
            try {
                if (Schema::hasTable('jobs')) {
                    $this->line('[ OK ] Queue connection is database and `jobs` table exists.');
                } else {
                    $this->warn('[WARN] QUEUE_CONNECTION is database but `jobs` table is missing. Run migrations (queue table).');
                    $failed = true;
                }
            } catch (\Throwable) {
                $this->warn('[WARN] Could not verify `jobs` table (database unavailable?).');
                $failed = true;
            }
        } else {
            $this->line('[ OK ] Queue connection is not database (no jobs table required for this check).');
        }

        $this->newLine();
        $this->comment('Automated tests are not run by this command. Run: php artisan test  (or: composer test)');
        $this->newLine();

        if ($failed) {
            $this->error('Release check completed with failures. Fix the items above.');

            return self::FAILURE;
        }

        $this->info('Release check completed with no blocking failures. Review warnings before deploy.');

        return self::SUCCESS;
    }

    /**
     * @param  callable(): bool  $fn
     */
    private function check(string $label, callable $fn): bool
    {
        $ok = $fn();
        $this->line(sprintf('[%s] %s', $ok ? 'PASS' : 'FAIL', $label));

        return $ok;
    }

    private function warnDefaultAdminPassword(): void
    {
        $email = (string) env('OPEN_ECOMMERCE_LARAVEL_ADMIN_EMAIL', 'admin@open-ecommerce-laravel.test');
        $plain = (string) env('OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD', 'password');

        try {
            $admin = AdminUser::query()->where('email', $email)->first();
            if ($admin === null) {
                $this->line('[ OK ] Default admin email not found (custom admin or different email).');

                return;
            }

            if (Hash::check($plain, (string) $admin->password)) {
                $this->warn('[WARN] Admin user "'.$email.'" still matches OPEN_ECOMMERCE_LARAVEL_ADMIN_PASSWORD from .env. Change password in Admin → profile or via tinker before production.');
            } else {
                $this->line('[ OK ] Default admin password does not match the current .env bootstrap password (or password was changed).');
            }
        } catch (\Throwable) {
            $this->warn('[WARN] Could not evaluate default admin password.');
        }
    }

    private function warnPayHereEnv(): void
    {
        $enabled = filter_var(env('PAYHERE_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
        $id = trim((string) env('PAYHERE_MERCHANT_ID', ''));
        $secret = trim((string) env('PAYHERE_MERCHANT_SECRET', ''));

        if ($enabled && ($id === '' || $secret === '')) {
            $this->warn('[WARN] PAYHERE_ENABLED is true but PAYHERE_MERCHANT_ID or PAYHERE_MERCHANT_SECRET is empty.');
        } else {
            $this->line('[ OK ] PayHere env bootstrap looks consistent (or gateway disabled via env).');
        }
    }

    private function warnMailEnv(): void
    {
        $mailer = strtolower((string) env('MAIL_MAILER', 'log'));
        if ($mailer === 'smtp') {
            $host = trim((string) env('MAIL_HOST', ''));
            if ($host === '') {
                $this->warn('[WARN] MAIL_MAILER is smtp but MAIL_HOST is empty.');
            } else {
                $this->line('[ OK ] SMTP host is set.');
            }
        } else {
            $this->line('[ OK ] Mail mailer is not smtp (log/array is fine for local). For production outbound mail, configure SMTP or a provider.');
        }

        $from = trim((string) env('MAIL_FROM_ADDRESS', ''));
        if ($from === '' || Str::contains($from, 'example.com')) {
            $this->warn('[WARN] MAIL_FROM_ADDRESS is empty or looks like a placeholder.');
        } else {
            $this->line('[ OK ] MAIL_FROM_ADDRESS is set.');
        }
    }
}
