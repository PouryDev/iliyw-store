<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $order->status === 'completed') {
            $order->loadMissing('items.uploads');
            foreach ($order->items as $item) {
                foreach ($item->uploads as $upload) {
                    if ($upload->purpose === 'customer_upload') {
                        Storage::disk($upload->disk ?: 'private')->delete($upload->path);
                        $upload->delete();
                    }
                }
            }
        }
    }
}


