<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\User;
use App\Support\Enums\LoyaltyTransactionType;
use Illuminate\Validation\ValidationException;

/**
 * Application service for loyalty points: earning, redeeming, tiers.
 */
class LoyaltyService
{
    /**
     * Points earned for spending the given order total.
     */
    public function earnableFor(float $total): int
    {
        $divisor = (float) config('ranga.loyalty.earn_divisor', 100);

        return $divisor > 0 ? (int) floor($total / $divisor) : 0;
    }

    /**
     * Monetary value of a number of points.
     */
    public function pointsValue(int $points): float
    {
        return round(max(0, $points) * (float) config('ranga.loyalty.redeem_value', 1), 2);
    }

    /**
     * Credit points to a user and record the transaction.
     */
    public function earn(User $user, int $points, ?Order $order = null, ?string $note = null): LoyaltyTransaction
    {
        return $this->record($user, LoyaltyTransactionType::Earn, abs($points), $order, $note);
    }

    /**
     * Redeem points for monetary value, deducting the balance.
     *
     * @throws ValidationException
     */
    public function redeem(User $user, int $points, ?Order $order = null): float
    {
        $points = abs($points);

        if ($points > $user->loyalty_points) {
            throw ValidationException::withMessages(['points' => [__('marketing.loyalty.insufficient_points')]]);
        }

        $this->record($user, LoyaltyTransactionType::Redeem, -$points, $order);

        return $this->pointsValue($points);
    }

    /**
     * Apply a manual admin adjustment (positive or negative) to a balance.
     */
    public function adjust(User $user, int $points, ?string $note = null): LoyaltyTransaction
    {
        return $this->record($user, LoyaltyTransactionType::Adjust, $points, null, $note);
    }

    /**
     * The loyalty tier the user currently qualifies for, if any.
     */
    public function currentTier(User $user): ?LoyaltyTier
    {
        return LoyaltyTier::query()
            ->where('min_points', '<=', $user->loyalty_points)
            ->orderByDesc('min_points')
            ->first();
    }

    /**
     * Apply a signed point movement and persist the new balance.
     */
    private function record(User $user, LoyaltyTransactionType $type, int $points, ?Order $order, ?string $note = null): LoyaltyTransaction
    {
        $balance = max(0, $user->loyalty_points + $points);

        $user->forceFill(['loyalty_points' => $balance])->save();

        /** @var LoyaltyTransaction $transaction */
        $transaction = $user->loyaltyTransactions()->create([
            'order_id' => $order?->id,
            'type' => $type,
            'points' => $points,
            'balance_after' => $balance,
            'note' => $note,
        ]);

        return $transaction;
    }
}
