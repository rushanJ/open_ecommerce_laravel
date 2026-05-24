<?php

namespace App\Services;

use App\Models\ApiToken;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ApiTokenService
{
    /**
     * @param  list<string>  $abilities
     * @return array{plain: string, token: ApiToken}
     */
    public function createToken(string $name, array $abilities = [], ?Carbon $expiresAt = null): array
    {
        $prefix = app()->environment('production') ? 'mk_live_' : 'mk_test_';
        $plain = $prefix.Str::random(40);

        $token = ApiToken::query()->create([
            'name' => $name,
            'token_hash' => $this->hashToken($plain),
            'abilities' => $abilities === [] ? null : array_values(array_unique($abilities)),
            'expires_at' => $expiresAt,
            'status' => 'active',
        ]);

        return ['plain' => $plain, 'token' => $token];
    }

    public function findActiveToken(string $plainToken): ?ApiToken
    {
        if (! preg_match('/^mk_(live|test)_[A-Za-z0-9]{40}$/', $plainToken)) {
            return null;
        }

        $hash = $this->hashToken($plainToken);

        /** @var ApiToken|null $token */
        $token = ApiToken::query()->where('token_hash', $hash)->first();

        if ($token === null || ! $token->isActive() || $token->isExpired()) {
            return null;
        }

        return $token;
    }

    public function validateToken(string $plainToken, ?string $ability = null): ?ApiToken
    {
        $token = $this->findActiveToken($plainToken);
        if ($token === null) {
            return null;
        }

        if ($ability !== null && $ability !== '' && ! $token->hasAbility($ability)) {
            return null;
        }

        return $token;
    }

    public function revoke(ApiToken $token): void
    {
        $token->forceFill(['status' => 'revoked'])->save();
    }

    public function hashToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
