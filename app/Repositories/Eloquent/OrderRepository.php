<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    /**
     * Get orders by user
     */
    public function getByUser(int $userId, array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get orders by user with pagination
     */
    public function getByUserPaginated(int $userId, int $perPage = 15, array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get orders by status
     */
    public function getByStatus(string $status, array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent orders
     */
    public function getRecent(int $limit = 10, array $relations = []): Collection
    {
        return $this->model->with($relations)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Update order status
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        return $this->update($orderId, ['status' => $status]);
    }

    /**
     * Get order with full details
     */
    public function getWithDetails(int $orderId): Order
    {
        return $this->model->with([
            'user',
            'items.product.images',
            'items.color',
            'items.size',
            'deliveryAddress',
            'deliveryMethod',
            'invoice',
        ])->findOrFail($orderId);
    }
}

