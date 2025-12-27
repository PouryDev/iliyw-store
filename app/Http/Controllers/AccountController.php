<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index()
    {
        $user = \App\Models\User::findOrFail(Auth::id());
        $recentOrders = Order::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if (!empty($user->phone)) {
                    $q->orWhere('customer_phone', $user->phone);
                }
            })
            ->latest()
            ->take(5)
            ->get();
        return view('account.index', compact('user', 'recentOrders'));
    }

    public function orders()
    {
        $user = \App\Models\User::findOrFail(Auth::id());
        $orders = Order::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if (!empty($user->phone)) {
                    $q->orWhere('customer_phone', $user->phone);
                }
            })
            ->latest()
            ->paginate(10);
        return view('account.orders', compact('user', 'orders'));
    }

    public function orderShow(Order $order)
    {
        $user = \App\Models\User::findOrFail(Auth::id());
        if ($order->user_id !== $user->id && (!empty($user->phone) && $order->customer_phone !== $user->phone)) {
            abort(403);
        }
        $order->load(['items.product.images', 'invoice', 'deliveryMethod']);
        return view('account.order-show', compact('user', 'order'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('account.settings', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
        ]);
        \App\Models\User::query()->whereKey($user->id)->update($data);
        return back()->with('status', 'اطلاعات ذخیره شد.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'رمز عبور فعلی درست نیست.']);
        }
        \App\Models\User::query()->whereKey($user->id)->update([
            'password' => Hash::make($data['password']),
        ]);
        return back()->with('status', 'رمز عبور بروزرسانی شد.');
    }
}


