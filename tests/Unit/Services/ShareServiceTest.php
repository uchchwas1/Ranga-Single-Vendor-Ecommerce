<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Support\Enums\SubscriptionInterval;
use App\Services\Media\ShareService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ShareServiceTest extends TestCase
{
    public function test_it_builds_a_wa_me_link_with_encoded_text(): void
    {
        $url = (new ShareService())->whatsapp('Hello world & more');

        $this->assertStringStartsWith('https://wa.me/?text=', $url);
        $this->assertStringContainsString('Hello%20world', $url);
    }

    public function test_weekly_interval_advances_by_a_week(): void
    {
        $from = Carbon::parse('2026-06-01 00:00:00');

        $this->assertTrue(SubscriptionInterval::Weekly->next($from)->equalTo($from->copy()->addWeek()));
        $this->assertTrue(SubscriptionInterval::Monthly->next($from)->equalTo($from->copy()->addMonthNoOverflow()));
    }
}
