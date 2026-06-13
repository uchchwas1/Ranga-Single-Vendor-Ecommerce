<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryContract;

/**
 * Eloquent implementation of the cart repository.
 */
class EloquentCartRepository implements CartRepositoryContract
{
    /**
     * Get (or create) the cart belonging to a user.
     */
    public function forUser(string $userId): Cart
    {
        return Cart::query()->firstOrCreate(
            ['user_id' => $userId],
            ['currency' => (string) config('ranga.defaults.currency', 'BDT')],
        );
    }

    /**
     * Get (or create) the cart for an anonymous session token.
     */
    public function forSession(string $sessionId): Cart
    {
        return Cart::query()->firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null],
            ['currency' => (string) config('ranga.defaults.currency', 'BDT')],
        );
    }

    /**
     * Find a cart by primary key.
     */
    public function find(string $id): ?Cart
    {
        return Cart::query()->find($id);
    }
}
