<?php

namespace App\Services;

use Illuminate\Support\Str;

class TemplateRendererService
{
    /**
     * Replace {{ key }} placeholders with escaped string values (no Blade).
     *
     * @param  array<string, mixed>  $data  Flat or nested; nested keys available as dot notation.
     */
    public function render(string $template, array $data): string
    {
        $flat = $this->flatten($data);

        return (string) preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.-]+)\s*\}\}/', function (array $m) use ($flat): string {
            $key = $m[1];
            if (! array_key_exists($key, $flat)) {
                return '';
            }

            return $this->stringifyForReplacement($flat[$key]);
        }, $template);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            $k = is_string($key) ? $key : (string) $key;
            $full = $prefix === '' ? $k : $prefix.'.'.$k;

            if (is_array($value)) {
                if ($value === [] || array_is_list($value)) {
                    $out[$full] = $value;

                    continue;
                }
                $out = array_merge($out, $this->flatten($value, $full));

                continue;
            }

            $out[$full] = $value;
        }

        return $out;
    }

    private function stringifyForReplacement(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return e((string) $value);
        }

        return e(json_encode($value) ?: '');
    }
}
