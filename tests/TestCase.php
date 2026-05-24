<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * Laravel HTTP tests do not automatically forward Set-Cookie headers between requests.
     * For guest flows that rely on session/cart continuity, re-apply cookies from the previous response.
     */
    protected function withCookiesFromResponse(TestResponse $response): void
    {
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];

        foreach ($response->headers->getCookies() as $cookie) {
            $expiresAt = $cookie->getExpiresTime();
            if ($expiresAt !== 0 && $expiresAt < time()) {
                continue;
            }

            $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());
        }
    }
}
