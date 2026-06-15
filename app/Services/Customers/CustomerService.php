<?php

declare(strict_types=1);

namespace App\Services\Customers;

use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Services\Marketing\LoyaltyService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Admin-side customer management: listing, segments, detail, loyalty.
 */
class CustomerService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly LoyaltyService $loyalty,
    ) {
    }

    /**
     * Paginate customers with optional search and segment filtering.
     *
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(?string $search = null, ?string $segment = null, int $perPage = 20): LengthAwarePaginator
    {
        return User::query()
            ->when($search !== null, function (Builder $query) use ($search): void {
                $query->where(function (Builder $q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($segment === 'with_orders', fn (Builder $q) => $q->whereHas('orders'))
            ->when($segment === 'no_orders', fn (Builder $q) => $q->whereDoesntHave('orders'))
            ->when($segment === 'loyalty', fn (Builder $q) => $q->where('loyalty_points', '>', 0))
            ->withCount('orders')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Load a customer's detail with related counts/relations.
     */
    public function detail(User $user): User
    {
        return $user->loadCount('orders')->load(['orders' => fn ($q) => $q->latest()->limit(10)]);
    }

    /**
     * Apply a manual loyalty-points adjustment to a customer.
     */
    public function adjustLoyalty(User $user, int $points, ?string $note = null): LoyaltyTransaction
    {
        return $this->loyalty->adjust($user, $points, $note);
    }
}
