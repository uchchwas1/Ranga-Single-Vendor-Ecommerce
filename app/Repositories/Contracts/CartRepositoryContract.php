<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Cart;

/**
 * Persistence boundary for Cart aggregates.
 */
interface CartRepositoryContract
{
    /**
     * Get (or create) the cart belonging to a user.
     */
    public function forUser(string $userId): Cart;

    /**
     * Get (or create) the cart for an anonymous session token.
     */
    public function forSession(string $sessionId): Cart;

    /**
     * Find a cart by primary key.
     */
    public function find(string $id): ?Cart;
}
