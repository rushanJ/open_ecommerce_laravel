<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'token_hash',
        'abilities',
        'last_used_at',
        'expires_at',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * @param  list<string>|null  $abilities
     */
    public function hasAbility(?string $ability): bool
    {
        if ($ability === null || $ability === '') {
            return true;
        }

        $abilities = $this->abilities ?? [];

        if (in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }
}
