<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $q = $request->string('q')->toString();

        $orders = Order::query()
            ->with('invoice')
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where(column: 'customer_name', 'like', "%{$q}%")
                        ->orWhere('customer_phone', 'like', "%{$q}%")
                        ->orWhere('id', $q);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'status', 'q'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'items.color', 'items.size', 'items.productVariant', 'invoice.transactions', 'user', 'deliveryMethod']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,shipped',
        ]);
        
        $oldStatus = $order->status;
        $newStatus = $data['status'];
        
        // If order is being cancelled, restore stock
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            DB::transaction(function () use ($order) {
                $order->load('items');
                
                foreach ($order->items as $item) {
                    $quantity = $item->quantity;
                    
                    if ($item->product_variant_id) {
                        // Restore variant stock
                        $variant = ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            $variant->increment('stock', $quantity);
                        }
                    } else {
                        // Restore product stock
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('stock', $quantity);
                        }
                    }
                }
            });
        }
        
        $order->update(['status' => $newStatus]);
        return back();
    }

    public function verifyTransaction(Request $request, Order $order)
    {
        $invoice = $order->invoice;
        if (!$invoice) {
            return back();
        }
        $transactionId = $request->integer('transaction_id');
        $transaction = Transaction::where('invoice_id', $invoice->id)->where('id', $transactionId)->firstOrFail();
        $transaction->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $order->update(['status' => 'confirmed']);
        return back();
    }
}


