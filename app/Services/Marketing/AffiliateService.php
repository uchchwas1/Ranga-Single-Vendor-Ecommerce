<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Affiliate;
use App\Models\AffiliateClick;
use App\Models\AffiliateConversion;
use App\Models\Order;
use App\Support\Enums\AffiliateStatus;
use App\Support\Enums\ConversionStatus;

/**
 * Application service for affiliate click tracking and conversions.
 */
class AffiliateService
{
    /**
     * Find an active affiliate by code.
     */
    public function findActiveByCode(string $code): ?Affiliate
    {
        return Affiliate::query()
            ->where('code', $code)
            ->where('status', AffiliateStatus::Active->value)
            ->first();
    }

    /**
     * Record a referral click.
     */
    public function recordClick(Affiliate $affiliate, ?string $ip, ?string $referrer, ?string $landingPage): AffiliateClick
    {
        /** @var AffiliateClick $click */
        $click = $affiliate->clicks()->create([
            'ip' => $ip,
            'referrer' => $referrer,
            'landing_page' => $landingPage,
        ]);

        return $click;
    }

    /**
     * Create a conversion for a paid order (idempotent per order).
     */
    public function convert(Affiliate $affiliate, Order $order): ?AffiliateConversion
    {
        if ($affiliate->conversions()->where('order_id', $order->id)->exists()) {
            return null;
        }

        $commission = $affiliate->commission_type->commissionFor((float) $order->total, (float) $affiliate->commission_rate);

        /** @var AffiliateConversion $conversion */
        $conversion = $affiliate->conversions()->create([
            'order_id' => $order->id,
            'commission' => $commission,
            'status' => ConversionStatus::Pending,
        ]);

        $affiliate->increment('earnings_total', $commission);

        return $conversion;
    }
}
