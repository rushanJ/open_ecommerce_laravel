<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlugService
{
    /**
     * Generate a unique slug column value for a table.
     *
     * @param  string  $table  Database table name.
     * @param  string  $column  Column storing the slug (e.g. "slug").
     * @param  string  $source  Human-readable source (typically a name).
     * @param  int|null  $ignoreId  Row id to ignore (for updates).
     */
    public function unique(string $table, string $column, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $counter = 2;

        while ($this->slugExists($table, $column, $slug, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $table, string $column, string $slug, ?int $ignoreId): bool
    {
        $query = DB::table($table)->where($column, $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * Unique slug with extra equality scopes (e.g. attribute_id for attribute values).
     *
     * @param  array<string, scalar|null>  $scope
     */
    public function uniqueScoped(string $table, string $column, string $source, array $scope, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'item';
        }

        $slug = $base;
        $counter = 2;

        while ($this->slugExistsScoped($table, $column, $slug, $scope, $ignoreId)) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @param  array<string, scalar|null>  $scope
     */
    private function slugExistsScoped(string $table, string $column, string $slug, array $scope, ?int $ignoreId): bool
    {
        $query = DB::table($table)->where($column, $slug);

        foreach ($scope as $key => $val) {
            if ($val === null) {
                $query->whereNull($key);
            } else {
                $query->where($key, $val);
            }
        }

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
