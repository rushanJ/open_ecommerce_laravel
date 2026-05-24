<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use App\Models\PaymentMethod;
use App\Models\Store;
use App\Models\SystemSetting;
use App\Services\SettingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class CheckInstallationCommand extends Command
{
    protected $signature = 'open_ecommerce_laravel:check-installation';

    protected $description = 'Verify critical configuration for a new open_ecommerce_laravel installation.';

    public function handle(SettingService $settings): int
    {
        $this->info('open_ecommerce_laravel installation check');
        $this->newLine();

        $checks = [];

        $checks[] = $this->check('APP_KEY is set', function (): bool {
            $key = (string) config('app.key', '');

            return $key !== '' && $key !== 'base64:';
        });

        $checks[] = $this->check('Database connection', function (): bool {
            try {
                DB::connection()->getPdo();

                return true;
            } catch (\Throwable) {
                return false;
            }
        });

        $checks[] = $this->check('Stores table has a record', function (): bool {
            try {
                return Store::query()->exists();
            } catch (\Throwable) {
                return false;
            }
        });

        $checks[] = $this->check('At least one active admin user', function (): bool {
            try {
                return AdminUser::query()->where('status', 'active')->exists();
            } catch (\Throwable) {
                return false;
            }
        });

        $checks[] = $this->check('system_settings has store.name', function (): bool {
            try {
                return SystemSetting::query()->where('key', 'store.name')->exists();
            } catch (\Throwable) {
                return false;
            }
        });

        $checks[] = $this->check('payment_methods includes PayHere', function (): bool {
            try {
                return PaymentMethod::query()->where('code', 'payhere')->exists();
            } catch (\Throwable) {
                return false;
            }
        });

        $checks[] = $this->check('Public storage link exists', function (): bool {
            $target = public_path('storage');

            return is_dir($target) || is_link($target);
        });

        $checks[] = $this->check('storage/ is writable', function (): bool {
            return is_writable(storage_path());
        });

        $checks[] = $this->check('bootstrap/cache is writable', function (): bool {
            return is_writable(base_path('bootstrap/cache'));
        });

        $checks[] = $this->check('storage/framework is writable', function (): bool {
            return is_writable(storage_path('framework'));
        });

        $this->newLine();
        $this->info('Production readiness warnings (review before go-live):');
        $this->newLine();

        if (config('app.env') === 'production' && config('app.debug')) {
            $this->line('[WARN] APP_DEBUG is true while APP_ENV is production.');
        } else {
            $this->line('[ OK ] APP_DEBUG is acceptable for the current environment.');
        }

        $appUrl = (string) config('app.url', '');
        if (config('app.env') === 'production' && $appUrl !== '' && ! str_starts_with(strtolower($appUrl), 'https://')) {
            $this->line('[WARN] APP_URL should use https:// in production.');
        } else {
            $this->line('[ OK ] APP_URL scheme looks acceptable for the current environment.');
        }

        try {
            $payHereEnabled = (bool) $settings->get('payhere.enabled', false);
            $merchantId = trim((string) $settings->get('payhere.merchant_id', ''));
            $merchantSecret = trim((string) $settings->get('payhere.merchant_secret', ''));
            if ($payHereEnabled && ($merchantId === '' || $merchantSecret === '')) {
                $this->line('[WARN] PayHere is enabled but merchant ID or secret is missing.');
            } else {
                $this->line('[ OK ] PayHere merchant credentials appear present or gateway is disabled.');
            }
        } catch (\Throwable) {
            $this->line('[WARN] Could not evaluate PayHere settings.');
        }

        try {
            $from = trim((string) $settings->get('mail.from_address', ''));
            if ($from === '') {
                $this->line('[WARN] Mail from address is empty (configure mail settings).');
            } else {
                $this->line('[ OK ] Mail from address is set.');
            }
        } catch (\Throwable) {
            $this->line('[WARN] Could not evaluate mail settings.');
        }

        $queueDriver = (string) config('queue.default', 'sync');
        if (config('app.env') === 'production' && $queueDriver === 'sync') {
            $this->line('[WARN] Queue connection is sync; consider database/redis with a worker for production.');
        } else {
            $this->line('[ OK ] Queue configuration noted.');
        }

        $this->newLine();

        if (in_array(false, $checks, true)) {
            $this->error('Some checks failed. Fix the items marked FAIL above before going live.');

            return self::FAILURE;
        }

        $this->info('All checks passed.');

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
}
