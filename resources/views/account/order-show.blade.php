<x-account.layout :title="'سفارش #'.$order->id">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent overflow-hidden">
            <div class="p-6 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">سفارش #{{ $order->id }}</h1>
                    <div class="text-gray-400 text-sm mt-1">{{ $order->created_at->format('Y/m/d H:i') }}</div>
                </div>
                @php $statusColors = [
                    'pending' => ['text' => 'text-amber-300', 'bg' => 'bg-amber-500/10', 'bd' => 'border-amber-500/20', 'label' => 'در انتظار'],
                    'confirmed' => ['text' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10', 'bd' => 'border-emerald-500/20', 'label' => 'در حال آماده سازی'],
                    'cancelled' => ['text' => 'text-rose-300', 'bg' => 'bg-rose-500/10', 'bd' => 'border-rose-500/20', 'label' => 'لغو شده'],
                    'shipped' => ['text' => 'text-sky-300', 'bg' => 'bg-sky-500/10', 'bd' => 'border-sky-500/20', 'label' => 'ارسال شده'],
                ]; $c = $statusColors[$order->status] ?? $statusColors['pending']; @endphp
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-md text-xs border {{ $c['bg'] }} {{ $c['bd'] }} {{ $c['text'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ str_replace('text','bg',$c['text']) }}"></span>
                    {{ $c['label'] }}
                </span>
            </div>

            <div class="divide-y divide-white/10">
                @foreach($order->items as $item)
                    @php $thumb = optional($item->product->images->first())->url ?? null; @endphp
                    <div class="p-4 flex items-center gap-4 hover:bg-white/5 transition">
                        <div class="w-16 h-16 rounded-lg overflow-hidden bg-white/5 flex items-center justify-center">
                            @if($thumb)
                                <img src="{{ $thumb }}" class="w-full h-full object-cover" />
                            @else
                                <span class="text-xl">🪞</span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium truncate">{{ $item->product->title }}</div>
                            <div class="text-xs text-gray-400">تعداد: {{ $item->quantity }}</div>
                        </div>
                        <div class="text-left">
                            <div class="font-medium">{{ number_format($item->line_total) }} <span class="text-xs text-gray-400">تومان</span></div>
                            <div class="text-xs text-gray-400">{{ number_format($item->unit_price) }} × {{ $item->quantity }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent overflow-hidden">
                <div class="p-5 border-b border-white/10 font-semibold">جزئیات پرداخت/فاکتور</div>
                <div class="p-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400">مبلغ سفارش</span>
                        <span class="font-semibold">{{ number_format($order->total_amount) }} <span class="text-xs text-gray-400">تومان</span></span>
                    </div>
                    @if($order->invoice)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">شماره فاکتور</span>
                            <span class="font-semibold">{{ $order->invoice->invoice_number }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">وضعیت فاکتور</span>
                            <span class="font-semibold">{{ $order->invoice->status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}</span>
                        </div>
                        @if($order->invoice->paid_at)
                            <div class="flex items-center justify-between">
                                <span class="text-gray-400">تاریخ پرداخت</span>
                                <span class="font-semibold">{{ $order->invoice->paid_at->format('Y/m/d H:i') }}</span>
                            </div>
                        @endif
                    @else
                        <div class="text-gray-400">فاکتوری ثبت نشده است.</div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent overflow-hidden">
                <div class="p-5 border-b border-white/10 font-semibold">اطلاعات ارسال</div>
                <div class="p-5 text-sm space-y-2">
                    <div><span class="text-gray-400">نام گیرنده:</span> <span class="font-medium">{{ $order->customer_name }}</span></div>
                    <div><span class="text-gray-400">تلفن:</span> <span class="font-medium">{{ $order->customer_phone }}</span></div>
                    <div><span class="text-gray-400">آدرس:</span> <span class="font-medium leading-6">{{ $order->customer_address }}</span></div>
                </div>
            </div>
        </div>
    </div>
</x-account.layout>



