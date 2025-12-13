<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use Illuminate\Http\Request;

class DeliveryMethodController extends Controller
{
    public function index()
    {
        $deliveryMethods = DeliveryMethod::ordered()->get();
        return view('admin.delivery-methods.index', compact('deliveryMethods'));
    }

    public function create()
    {
        return view('admin.delivery-methods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'fee' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        DeliveryMethod::create([
            'title' => $request->title,
            'fee' => $request->fee,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.delivery-methods.index')
            ->with('success', 'روش ارسال با موفقیت ایجاد شد');
    }

    public function show(DeliveryMethod $deliveryMethod)
    {
        return view('admin.delivery-methods.show', compact('deliveryMethod'));
    }

    public function edit(DeliveryMethod $deliveryMethod)
    {
        return view('admin.delivery-methods.edit', compact('deliveryMethod'));
    }

    public function update(Request $request, DeliveryMethod $deliveryMethod)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'fee' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $deliveryMethod->update([
            'title' => $request->title,
            'fee' => $request->fee,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('admin.delivery-methods.index')
            ->with('success', 'روش ارسال با موفقیت به‌روزرسانی شد');
    }

    public function destroy(DeliveryMethod $deliveryMethod)
    {
        // Check if delivery method is used in any orders
        if ($deliveryMethod->orders()->exists()) {
            return redirect()->route('admin.delivery-methods.index')
                ->with('error', 'این روش ارسال در سفارشات استفاده شده و قابل حذف نیست');
        }

        $deliveryMethod->delete();

        return redirect()->route('admin.delivery-methods.index')
            ->with('success', 'روش ارسال با موفقیت حذف شد');
    }
}
