<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminActivityLogger
{
    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public function log(string $module, string $action, ?Model $subject = null, array $old = [], array $new = []): void
    {
        $adminId = auth('admin')->id();

        AdminActivityLog::query()->create([
            'admin_user_id' => is_numeric($adminId) ? (int) $adminId : null,
            'module' => Str::limit($module, 100, ''),
            'action' => Str::limit($action, 100, ''),
            'subject_type' => $subject !== null ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'old_values' => $old === [] ? null : $this->stripSensitive($old),
            'new_values' => $new === [] ? null : $this->stripSensitive($new),
            'ip_address' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 2000, ''),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function stripSensitive(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $k = (string) $key;
            if ($this->isSensitiveKey($k)) {
                $out[$k] = '[redacted]';

                continue;
            }
            if ($value instanceof Model) {
                $out[$k] = $value->getKey();

                continue;
            }
            if ($value instanceof Authenticatable) {
                $out[$k] = $value->getAuthIdentifier();

                continue;
            }
            if (is_array($value)) {
                $out[$k] = $this->stripSensitive($value);

                continue;
            }
            $out[$k] = $value;
        }

        return $out;
    }

    private function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);

        return str_contains($lower, 'password')
            || str_contains($lower, 'secret')
            || str_contains($lower, 'token')
            || str_contains($lower, 'api_key');
    }
}
