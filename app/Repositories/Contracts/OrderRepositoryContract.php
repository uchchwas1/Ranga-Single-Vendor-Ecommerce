<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Persistence boundary for Order aggregates.
 */
interface OrderRepositoryContract
{
    /**
     * Persist a new order.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Order;

    /**
     * Find an order by its public order number.
     */
    public function findByNumber(string $orderNumber): ?Order;

    /**
     * Paginate a user's orders, newest first.
     *
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginateForUser(string $userId, int $perPage = 15): LengthAwarePaginator;
}
