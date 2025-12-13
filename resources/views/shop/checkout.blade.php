<x-layouts.app :title="'تسویه حساب'">
    <h1 class="text-xl font-bold mb-4">تسویه حساب</h1>
    <div class="grid md:grid-cols-2 gap-6">
        <x-ui.card class="p-4">
            <h2 class="font-bold mb-3">جزئیات سفارش</h2>
            @php 
                $cartItems = [];
                foreach($cart as $cartKey => $qty) {
                    $parts = explode('_', $cartKey);
                    $productId = $parts[0];
                    $colorId = isset($parts[1]) && $parts[1] !== '0' ? $parts[1] : null;
                    $sizeId = isset($parts[2]) && $parts[2] !== '0' ? $parts[2] : null;
                    
                    $product = $products[$productId] ?? null;
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
                            'product' => $product,
                            'variant_display_name' => $variantDisplayName,
                            'unit_price' => $unitPrice,
                            'quantity' => $qty,
                            'total' => $unitPrice * $qty
                        ];
                    }
                }
            @endphp
            
            @foreach($cartItems as $item)
                <div class="flex items-center justify-between border-b py-2">
                    <div>
                        <div class="font-medium">{{ $item['product']->title }}</div>
                        @if($item['variant_display_name'])
                            <div class="text-xs text-gray-400">{{ $item['variant_display_name'] }}</div>
                        @endif
                        <div class="text-xs text-gray-500">{{ $item['quantity'] }} عدد × {{ number_format($item['unit_price']) }} تومان</div>
                    </div>
                    <div class="text-sm font-medium">{{ number_format($item['total']) }} تومان</div>
                </div>
            @endforeach
            <div class="text-right mt-3">
                <div class="text-sm text-gray-400">جمع کل: {{ number_format($total) }} تومان</div>
                @if(isset($discountAmount) && $discountAmount > 0)
                    <div class="text-sm text-green-400">تخفیف: -{{ number_format($discountAmount) }} تومان</div>
                @endif
                <div class="font-extrabold text-cherry-400 mt-2">
                    مبلغ نهایی: {{ number_format($finalAmount ?? $total) }} تومان
                </div>
            </div>
        </x-ui.card>
        <x-ui.card class="p-4">
            <h2 class="font-bold mb-3">اطلاعات تماس و رسید</h2>
            
            @if ($errors->any())
                <div class="rounded-lg border border-rose-500/50 bg-rose-500/10 p-3 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <ul class="text-xs text-rose-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <form method="post" action="{{ route('checkout.place') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <x-ui.input 
                    name="customer_name" 
                    :value="old('customer_name', auth()->user()?->name)" 
                    label="نام و نام خانوادگی" 
                    placeholder="" 
                    required 
                />
                <x-ui.input 
                    name="customer_phone" 
                    :value="old('customer_phone', auth()->user()?->phone)" 
                    label="شماره تماس" 
                    placeholder="09123456789" 
                    required 
                />
                <x-ui.textarea 
                    name="customer_address" 
                    label="آدرس کامل" 
                    rows="4" 
                    required
                >{{ old('customer_address', auth()->user()?->address) }}</x-ui.textarea>
                
                @auth
                    <div class="text-xs text-gray-400 bg-white/5 p-2 rounded border border-white/10">
                        💡 اطلاعات شما از حساب کاربری پر شده است. در صورت نیاز می‌توانید ویرایش کنید.
                    </div>
                @endauth
                
                @auth
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-300">کد تخفیف</label>
                        <div class="flex gap-2">
                            <x-ui.input 
                                name="discount_code" 
                                :value="old('discount_code', request('discount_code'))" 
                                placeholder="کد تخفیف را وارد کنید"
                                class="flex-1"
                            />
                            <button type="submit" name="validate_discount" value="1" class="bg-gray-600 hover:bg-gray-700 text-white rounded px-4 py-2 whitespace-nowrap">
                                اعمال
                            </button>
                        </div>
                        @if(isset($discountCodeError) && $discountCodeError)
                            <div class="text-sm text-red-400">{{ $discountCodeError }}</div>
                        @endif
                        @if(isset($discountCode) && $discountCode && isset($discountAmount) && $discountAmount > 0)
                            <div class="text-sm text-green-400">
                                ✅ کد تخفیف {{ $discountCode->code }} اعمال شد ({{ number_format($discountAmount) }} تومان تخفیف)
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-xs text-gray-400 bg-blue-500/10 p-2 rounded border border-blue-500/20">
                        💡 برای استفاده از کد تخفیف باید وارد حساب کاربری خود شوید.
                    </div>
                @endauth
                
                <x-ui.file name="receipt" label="آپلود فیش واریزی (اختیاری)" accept="image/*" />
                <x-ui.button type="submit">ثبت سفارش</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>


