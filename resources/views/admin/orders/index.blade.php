<x-admin.layout :title="'سفارش‌ها'">
    <h1 class="text-xl font-bold mb-4">سفارش‌ها</h1>
    <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-ui.input name="q" :value="$q ?? ''" label="جستجو" placeholder="نام/تلفن/کد" />
        <x-ui.select name="status" label="وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach(['pending'=>'در انتظار','confirmed'=>'در حال آماده سازی','cancelled'=>'لغو شده','shipped'=>'ارسال شده'] as $key=>$label)
                <option value="{{ $key }}" {{ ($status ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <div class="self-end">
            <button class="w-full md:w-auto bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">اعمال</button>
        </div>
    </form>
    <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-[720px] w-full text-sm">
            <thead>
                <tr class="bg-white/5/50">
                    <th class="p-3 text-right font-semibold text-gray-200">کد</th>
                    <th class="p-3 font-semibold text-gray-200">نام</th>
                    <th class="p-3 font-semibold text-gray-200">مبلغ</th>
                    <th class="p-3 font-semibold text-gray-200">وضعیت</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($orders as $order)
                    <tr class="hover:bg-white/5 transition">
                        <td class="p-3 whitespace-nowrap">#{{ $order->id }}</td>
                        <td class="p-3">
                            <div class="min-w-0">
                                <div class="font-medium truncate">{{ $order->customer_name }}</div>
                                @isset($order->customer_phone)
                                    <div class="text-xs text-gray-400">{{ $order->customer_phone }}</div>
                                @endisset
                            </div>
                        </td>
                        <td class="p-3 whitespace-nowrap">{{ number_format($order->total_amount) }} <span class="text-xs text-gray-400">تومان</span></td>
                        <td class="p-3">
                            @php $statusColors = [
                                'pending' => ['text' => 'text-amber-300', 'bg' => 'bg-amber-500/10', 'bd' => 'border-amber-500/20', 'label' => 'در انتظار'],
                                'confirmed' => ['text' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10', 'bd' => 'border-emerald-500/20', 'label' => 'در حال آماده سازی'],
                                'cancelled' => ['text' => 'text-rose-300', 'bg' => 'bg-rose-500/10', 'bd' => 'border-rose-500/20', 'label' => 'لغو شده'],
                                'shipped' => ['text' => 'text-sky-300', 'bg' => 'bg-sky-500/10', 'bd' => 'border-sky-500/20', 'label' => 'ارسال شده'],
                            ]; $c = $statusColors[$order->status] ?? $statusColors['pending']; @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs border {{ $c['bg'] }} {{ $c['bd'] }} {{ $c['text'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ str_replace('text','bg',$c['text']) }}"></span>
                                {{ $c['label'] }}
                            </span>
                        </td>
                        <td class="p-3 text-left">
                            <a href="{{ route('admin.orders.show',$order) }}" class="inline-flex items-center gap-1 text-cherry-300 hover:text-cherry-200 px-2 py-1 rounded-md hover:bg-cherry-500/10 transition">مشاهده</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-4 text-center text-gray-300" colspan="5">سفارشی یافت نشد.</td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden p-4 space-y-3">
            @forelse($orders as $order)
                @php $statusColors = [
                    'pending' => ['text' => 'text-amber-300', 'bg' => 'bg-amber-500/10', 'bd' => 'border-amber-500/20', 'label' => 'در انتظار'],
                    'confirmed' => ['text' => 'text-emerald-300', 'bg' => 'bg-emerald-500/10', 'bd' => 'border-emerald-500/20', 'label' => 'تایید شده'],
                    'cancelled' => ['text' => 'text-rose-300', 'bg' => 'bg-rose-500/10', 'bd' => 'border-rose-500/20', 'label' => 'لغو شده'],
                    'shipped' => ['text' => 'text-sky-300', 'bg' => 'bg-sky-500/10', 'bd' => 'border-sky-500/20', 'label' => 'ارسال شده'],
                ]; $c = $statusColors[$order->status] ?? $statusColors['pending']; @endphp
                
                <div class="rounded-lg border border-white/10 bg-white/5 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="font-semibold text-white">#{{ $order->id }}</div>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs border {{ $c['bg'] }} {{ $c['bd'] }} {{ $c['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ str_replace('text','bg',$c['text']) }}"></span>
                            {{ $c['label'] }}
                        </span>
                    </div>
                    
                    <div>
                        <div class="text-xs text-gray-400 mb-1">مشتری</div>
                        <div class="text-sm font-medium">{{ $order->customer_name }}</div>
                        <div class="text-xs text-gray-400">{{ $order->customer_phone }}</div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <div class="text-xs text-gray-400 mb-1">مبلغ</div>
                            <div class="text-white font-semibold">{{ number_format($order->total_amount) }} <span class="text-xs text-gray-400">تومان</span></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1">تاریخ</div>
                            <div class="text-white">{{ $order->created_at->format('Y/m/d') }}</div>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.orders.show',$order) }}" class="block text-center bg-cherry-600 hover:bg-cherry-700 text-white px-4 py-2 rounded transition text-sm">
                        مشاهده جزئیات
                    </a>
                </div>
            @empty
                <div class="text-center text-gray-300 py-8">سفارشی یافت نشد.</div>
            @endforelse
        </div>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</x-admin.layout>


