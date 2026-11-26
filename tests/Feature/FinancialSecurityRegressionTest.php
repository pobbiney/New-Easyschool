<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FinancialSecurityRegressionTest extends TestCase
{
    public function test_paystack_webhook_is_public_but_only_csrf_is_exempted(): void
    {
        $route = Route::getRoutes()->getByName('paystack-webhook');

        $this->assertNotNull($route);
        $this->assertSame(['POST'], $route->methods());
        $this->assertNotContains('staff.auth', $route->gatherMiddleware());
        $this->assertNotContains('menu.access', $route->gatherMiddleware());
        $this->assertContains(VerifyCsrfToken::class, $route->excludedMiddleware());
    }
}
