<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Support\Facades\Session;

class ClearCartAfterOrder
{
    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        // Cart should already be cleared during checkout process
        // This is a safety measure
        Session::forget('cart');
    }
}

