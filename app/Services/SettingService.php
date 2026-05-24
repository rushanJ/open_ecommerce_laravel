<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const string CACHE_KEY = 'open_ecommerce_laravel.system_settings.v1';

    /**
     * @var list<string>
     */
    private const array SECRET_KEY_SUBSTRINGS = ['secret', 'password', 'smtp_password'];

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->allCached();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public function set(string $key, mixed $value, string $type = 'string', string $group = 'general', bool $isPublic = false): void
    {
        $stored = $this->encodeForStorage($type, $value);

        SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $stored,
                'type' => $type,
                'group' => $group,
                'is_public' => $isPublic,
            ],
        );

        $this->forgetCache();
    }

    /**
     * @return array<string, mixed>
     */
    public function getGroup(string $group): array
    {
        $rows = SystemSetting::query()->where('group', $group)->get(['key', 'value', 'type']);
        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row->key] = $this->castFromStorage((string) $row->type, $row->value);
        }

        return $out;
    }

    /**
     * Public storefront-safe settings only (never secrets).
     *
     * @return array<string, mixed>
     */
    public function publicSettings(): array
    {
        $rows = SystemSetting::query()
            ->where('is_public', true)
            ->get(['key', 'value', 'type']);

        $out = [];
        foreach ($rows as $row) {
            if ($this->isBlockedSecretKey((string) $row->key)) {
                continue;
            }
            $out[$row->key] = $this->castFromStorage((string) $row->type, $row->value);
        }

        return $out;
    }

    public function forget(string $key): void
    {
        SystemSetting::query()->where('key', $key)->delete();
        $this->forgetCache();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function allCached(): array
    {
        /** @var array<string, mixed> */
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $rows = SystemSetting::query()->get(['key', 'value', 'type']);
            $out = [];
            foreach ($rows as $row) {
                $out[(string) $row->key] = $this->castFromStorage((string) $row->type, $row->value);
            }

            return $out;
        });
    }

    private function isBlockedSecretKey(string $key): bool
    {
        $lower = strtolower($key);
        foreach (self::SECRET_KEY_SUBSTRINGS as $frag) {
            if (str_contains($lower, $frag)) {
                return true;
            }
        }

        return false;
    }

    private function castFromStorage(string $type, mixed $raw): mixed
    {
        if ($raw === null) {
            return null;
        }

        $str = is_string($raw) ? $raw : (string) $raw;

        return match ($type) {
            'boolean' => filter_var($str, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $str,
            'json' => json_decode($str, true),
            'file', 'string' => $str,
            default => $str,
        };
    }

    private function encodeForStorage(string $type, mixed $value): ?string
    {
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'integer' => (string) (int) $value,
            'json' => json_encode($value),
            'file', 'string' => $value === null ? null : (string) $value,
            default => $value === null ? null : (string) $value,
        };
    }
}
