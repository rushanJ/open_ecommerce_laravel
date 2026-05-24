<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_breeze_auth_scaffold_tests_are_not_applicable(): void
    {
        $this->markTestSkipped('open_ecommerce_laravel uses a custom customer guard/routes; Breeze scaffold tests are not applicable.');
    }
}
