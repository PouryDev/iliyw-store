<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'instagram_id' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $user->update($request->only(['name', 'phone', 'instagram_id']));

        return response()->json([
            'success' => true,
            'message' => 'پروفایل به‌روزرسانی شد',
            'data' => $user
        ]);
    }

    public function orders(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.product', 'items.variant'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total()
            ]
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        
        // Total orders count
        $totalOrders = $user->orders()->count();
        
        // Total amount spent
        $totalAmount = $user->orders()
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        
        // Total addresses count
        $totalAddresses = $user->addresses()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'total_amount' => $totalAmount,
                'total_addresses' => $totalAddresses
            ]
        ]);
    }
}
