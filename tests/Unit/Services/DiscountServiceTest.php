<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Coupon;
use App\Models\GiftCard;
use App\Models\User;
use App\Services\Marketing\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DiscountServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): DiscountService
    {
        return app(DiscountService::class);
    }

    public function test_a_percentage_coupon_discount_is_computed(): void
    {
        Coupon::factory()->create(['code' => 'P10', 'value' => 10]);

        $result = $this->service()->forCheckout(2000, 60, 0, null, 'P10');

        $this->assertEqualsWithDelta(200.0, $result->couponDiscount, 0.001);
        $this->assertEqualsWithDelta(1860.0, $result->total, 0.001);
    }

    public function test_a_free_shipping_coupon_zeroes_shipping(): void
    {
        Coupon::factory()->freeShipping()->create(['code' => 'FS']);

        $result = $this->service()->forCheckout(1000, 60, 0, null, 'FS');

        $this->assertTrue($result->freeShipping);
        $this->assertEqualsWithDelta(0.0, $result->shippingPayable, 0.001);
        $this->assertEqualsWithDelta(1000.0, $result->total, 0.001);
    }

    public function test_a_gift_card_is_capped_at_its_balance(): void
    {
        $user = User::factory()->create();
        GiftCard::factory()->create(['code' => 'GC', 'initial_balance' => 500, 'current_balance' => 500]);

        $result = $this->service()->forCheckout(1000, 60, 0, $user, null, 'GC');

        $this->assertEqualsWithDelta(500.0, $result->giftCardApplied, 0.001);
        $this->assertEqualsWithDelta(560.0, $result->total, 0.001);
    }

    public function test_loyalty_points_reduce_the_total(): void
    {
        $user = User::factory()->create(['loyalty_points' => 300]);

        $result = $this->service()->forCheckout(1000, 60, 0, $user, null, null, 100);

        $this->assertEqualsWithDelta(100.0, $result->loyaltyApplied, 0.001);
        $this->assertEqualsWithDelta(960.0, $result->total, 0.001);
    }

    public function test_redeeming_more_points_than_owned_fails(): void
    {
        $user = User::factory()->create(['loyalty_points' => 50]);

        $this->expectException(ValidationException::class);

        $this->service()->forCheckout(1000, 60, 0, $user, null, null, 100);
    }
}
