<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\BackInStockSubscription;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\BackInStockNotification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

/**
 * Manages back-in-stock watch requests and restock notifications.
 */
class BackInStockService
{
    /**
     * Register a watch request for a variant (idempotent per email).
     */
    public function subscribe(ProductVariant $variant, string $email, ?User $user = null): BackInStockSubscription
    {
        /** @var BackInStockSubscription $subscription */
        $subscription = BackInStockSubscription::query()->updateOrCreate(
            ['variant_id' => $variant->id, 'email' => $email],
            ['user_id' => $user?->id, 'notified_at' => null],
        );

        return $subscription;
    }

    /**
     * Notify everyone watching a restocked variant, then mark them notified.
     */
    public function notifyRestocked(ProductVariant $variant): int
    {
        $variant->loadMissing('product');

        $pending = BackInStockSubscription::query()
            ->where('variant_id', $variant->id)
            ->pending()
            ->get();

        foreach ($pending as $subscription) {
            Notification::route('mail', $subscription->email)
                ->notify(new BackInStockNotification($variant));

            $subscription->forceFill(['notified_at' => Date::now()])->save();
        }

        return $pending->count();
    }
}
