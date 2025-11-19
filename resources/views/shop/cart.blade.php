<x-dynamic-component :component="auth()->check() ? 'account.layout' : 'layouts.app'" :title="'سبد خرید'">
    <h1 class="text-xl font-bold mb-4">سبد خرید</h1>
    @php 
        $total = 0;
        $cartItems = [];
        foreach($cart as $cartKey => $qty) {
            $parts = explode('_', $cartKey);
            $productId = $parts[0];
            $colorId = isset($parts[1]) && $parts[1] !== '0' ? $parts[1] : null;
            $sizeId = isset($parts[2]) && $parts[2] !== '0' ? $parts[2] : null;
            
            $product = \App\Models\Product::find($productId);
            if ($product) {
                $variantDisplayName = null;
                $unitPrice = $product->price;
                
                if ($colorId || $sizeId) {
                    $productVariant = \App\Models\ProductVariant::where('product_id', $productId)
                        ->when($colorId, function ($query) use ($colorId) {
                            $query->where('color_id', $colorId);
                        })
                        ->when($sizeId, function ($query) use ($sizeId) {
                            $query->where('size_id', $sizeId);
                        })
                        ->first();
                    
                    if ($productVariant) {
                        $variantDisplayName = $productVariant->display_name;
                        $unitPrice = $productVariant->price ?? $product->price;
                    }
                }
                
                $cartItems[] = [
                    'cart_key' => $cartKey,
                    'product' => $product,
                    'variant_display_name' => $variantDisplayName,
                    'unit_price' => $unitPrice,
                    'quantity' => $qty,
                    'total' => $unitPrice * $qty
                ];
                $total += $unitPrice * $qty;
            }
        }
    @endphp
    
    @if(empty($cart))
        <x-ui.card class="p-4 text-gray-200">سبد خرید خالی است.</x-ui.card>
    @else
        <x-ui.card class="p-4">
            @foreach($cartItems as $item)
                <div class="flex items-center justify-between border-b border-white/10 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded bg-white/10 flex items-center justify-center">🛍️</div>
                        <div>
                            <div class="font-bold">{{ $item['product']->title }}</div>
                            @if($item['variant_display_name'])
                                <div class="text-xs text-gray-300">{{ $item['variant_display_name'] }}</div>
                            @endif
                            <div class="text-xs text-cherry-400">{{ number_format($item['unit_price']) }} تومان × {{ $item['quantity'] }}</div>
                        </div>
                    </div>
                    <form method="post" action="{{ route('cart.remove', $item['cart_key']) }}">
                        @csrf @method('DELETE')
                        <button class="text-red-400 hover:text-red-300">حذف</button>
                    </form>
                </div>
            @endforeach
            <div class="text-right mt-3 font-extrabold text-cherry-400">جمع: {{ number_format($total) }} تومان</div>
        </x-ui.card>
        <div class="mt-4 text-left">
            <a href="{{ route('checkout.index') }}" class="bg-cherry-500 hover:bg-cherry-600 text-white rounded px-4 py-2">ادامه خرید</a>
        </div>
    @endif
</x-dynamic-component>


