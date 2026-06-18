<?php

declare(strict_types=1);

namespace Tests\Feature\Qa;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RouteGuardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Protected endpoints that must reject unauthenticated requests.
     *
     * @return list<array{0: string, 1: string}>
     */
    public static function protectedRoutes(): array
    {
        return [
            ['get', '/api/v1/profile'],
            ['get', '/api/v1/notifications'],
            ['get', '/api/v1/profile/orders'],
            ['get', '/api/v1/profile/loyalty'],
            ['get', '/api/v1/profile/subscriptions'],
            ['get', '/api/v1/profile/wishlist'],
            ['post', '/api/v1/subscriptions'],
            ['get', '/api/v1/admin/settings'],
            ['get', '/api/v1/admin/reports/dashboard'],
            ['get', '/api/v1/admin/customers'],
            ['post', '/api/v1/admin/coupons'],
            ['post', '/api/v1/admin/ai/product-description'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_guests_are_rejected_from_protected_endpoints(string $method, string $uri): void
    {
        $this->json($method, $uri)->assertUnauthorized();
    }
}
