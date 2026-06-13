<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of the order repository.
 */
class EloquentOrderRepository implements OrderRepositoryContract
{
    /**
     * Persist a new order.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Order
    {
        return Order::query()->create($attributes);
    }

    /**
     * Find an order by its public order number.
     */
    public function findByNumber(string $orderNumber): ?Order
    {
        return Order::query()
            ->with(['items', 'addresses', 'payments'])
            ->where('order_number', $orderNumber)
            ->first();
    }

    /**
     * Paginate a user's orders, newest first.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateForUser(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->forUser($userId)
            ->with(['items'])
            ->latest()
            ->paginate($perPage);
    }
}
