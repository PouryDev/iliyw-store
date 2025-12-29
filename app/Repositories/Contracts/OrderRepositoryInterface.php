<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * Get orders by user
     *
     * @param int $userId
     * @param array $relations
     * @return Collection
     */
    public function getByUser(int $userId, array $relations = []): Collection;

    /**
     * Get orders by user with pagination
     *
     * @param int $userId
     * @param int $perPage
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function getByUserPaginated(int $userId, int $perPage = 15, array $relations = []): LengthAwarePaginator;

    /**
     * Get orders by status
     *
     * @param string $status
     * @param array $relations
     * @return Collection
     */
    public function getByStatus(string $status, array $relations = []): Collection;

    /**
     * Get recent orders
     *
     * @param int $limit
     * @param array $relations
     * @return Collection
     */
    public function getRecent(int $limit = 10, array $relations = []): Collection;

    /**
     * Update order status
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $orderId, string $status): bool;

    /**
     * Get order with full details
     *
     * @param int $orderId
     * @return Order
     */
    public function getWithDetails(int $orderId): Order;
}

