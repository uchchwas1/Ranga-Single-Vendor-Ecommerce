<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Authorization rules for catalogue products.
 *
 * Public storefront reads are open; write operations require the
 * "manage products" permission (assigned to staff roles).
 */
class ProductPolicy
{
    /**
     * Anyone may browse the catalogue listing.
     */
    public function viewAny(?User $actor): bool
    {
        return true;
    }

    /**
     * Anyone may view an individual (active) product.
     */
    public function view(?User $actor, Product $product): bool
    {
        return true;
    }

    /**
     * Whether the actor may create products.
     */
    public function create(User $actor): bool
    {
        return $actor->can('products.manage');
    }

    /**
     * Whether the actor may update the given product.
     */
    public function update(User $actor, Product $product): bool
    {
        return $actor->can('products.manage');
    }

    /**
     * Whether the actor may delete the given product.
     */
    public function delete(User $actor, Product $product): bool
    {
        return $actor->can('products.manage');
    }
}
