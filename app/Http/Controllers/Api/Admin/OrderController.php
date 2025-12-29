<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Actions\Order\CancelOrderAction;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CancelOrderAction $cancelOrderAction
    ) {}

    /**
     * Get all orders (admin)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $status = $request->input('status');

        $query = $this->orderRepository->newQuery()
            ->with(['user', 'items.product', 'deliveryMethod', 'invoice'])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest();

        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders->items()),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Show order details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $order = $this->orderRepository->getWithDetails($id);

            return response()->json([
                'success' => true,
                'data' => new OrderResource($order)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد'
            ], 404);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        try {
            $order = $this->orderRepository->findOrFail($id);
            $oldStatus = $order->status;

            $updated = $this->orderRepository->update($id, [
                'status' => $request->status
            ]);

            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در به‌روزرسانی وضعیت'
                ], 500);
            }

            // Dispatch event
            $order->refresh();
            event(new OrderStatusChanged($order, $oldStatus, $request->status));

            return response()->json([
                'success' => true,
                'message' => 'وضعیت سفارش به‌روزرسانی شد',
                'data' => new OrderResource($order)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'سفارش یافت نشد'
            ], 404);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $order = $this->orderRepository->findOrFail($id);

            $this->cancelOrderAction->execute($order, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت لغو شد',
                'data' => new OrderResource($order->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در لغو سفارش: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete order
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->orderRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'سفارش یافت نشد'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'سفارش با موفقیت حذف شد'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف سفارش: ' . $e->getMessage()
            ], 500);
        }
    }
}

