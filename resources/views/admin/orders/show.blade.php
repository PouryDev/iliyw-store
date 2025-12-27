<x-admin.layout :title="'سفارش #' . $order->id">
    <h1 class="text-xl font-bold mb-4">سفارش #{{ $order->id }}</h1>
    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-white/10 bg-white/5 p-4">
            <h2 class="font-bold mb-2">اقلام</h2>
            @foreach($order->items as $item)
                <div class="flex items-center justify-between border-b py-2">
                    <div>
                        <div class="font-medium">{{ $item->product->title }}</div>
                        @if($item->variant_display_name)
                            <div class="text-xs text-gray-400">{{ $item->variant_display_name }}</div>
                        @endif
                        @if($item->color || $item->size)
                            <div class="text-xs text-gray-500">
                                @if($item->color)
                                    رنگ: {{ $item->color->name }}
                                @endif
                                @if($item->color && $item->size)
                                    -
                                @endif
                                @if($item->size)
                                    سایز: {{ $item->size->name }}
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="text-sm">
                        <div>{{ $item->quantity }} × {{ number_format($item->unit_price) }} تومان</div>
                        <div class="text-xs text-gray-400">مجموع: {{ number_format($item->line_total) }} تومان</div>
                    </div>
                </div>
            @endforeach
            <div class="text-right mt-3 font-extrabold text-cherry-700">جمع: {{ number_format($order->total_amount) }} تومان</div>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-4">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h2 class="font-bold">اطلاعات مشتری</h2>
                    @if($order->user_id)
                        <button 
                            onclick="openUserModal()"
                            class="text-xs bg-cherry-600 hover:bg-cherry-700 text-white px-3 py-1.5 rounded transition"
                        >
                            مشاهده حساب کاربری
                        </button>
                    @endif
                </div>
                <div class="text-sm">{{ $order->customer_name }} | {{ $order->customer_phone }}</div>
                <div class="text-sm text-[#706f6c]">{{ $order->customer_address }}</div>
            </div>

            <div>
                <h2 class="font-bold mb-2">اطلاعات ارسال</h2>
                @if($order->deliveryMethod)
                    <div class="text-sm">روش ارسال: {{ $order->deliveryMethod->title }}</div>
                @endif
                <div class="text-sm">مبلغ پرداختی: {{ number_format($order->final_amount) }} تومان</div>
            </div>

            <div>
                <h2 class="font-bold mb-2">رسید</h2>
                @if($order->receipt_path)
                    <img src="{{ asset('storage/'.$order->receipt_path) }}" class="rounded max-h-72" />
                @else
                    <div class="text-xs">رسیدی آپلود نشده است.</div>
                @endif
            </div>

            @if($order->invoice)
                <div>
                    <h2 class="font-bold mb-2">فاکتور {{ $order->invoice->invoice_number }}</h2>
                    <div class="text-sm">وضعیت: {{ $order->invoice->status }}</div>
                    <div class="text-sm">مبلغ: {{ number_format($order->invoice->amount) }} تومان</div>
                </div>
                <div>
                    <h3 class="font-bold mb-1">تراکنش‌ها</h3>
                    @forelse($order->invoice->transactions as $tx)
                        <div class="border rounded p-2 mb-2">
                            <div class="text-sm">روش: {{ $tx->method }} | مبلغ: {{ number_format($tx->amount) }}</div>
                            <div class="text-xs">وضعیت: {{ $tx->status }} | ارجاع: {{ $tx->reference }}</div>
                        </div>
                    @empty
                        <div class="text-xs">تراکنشی ثبت نشده.</div>
                    @endforelse
                </div>
                <form method="post" action="{{ route('admin.orders.verify',$order) }}" class="flex gap-2">
                    @csrf
                    <x-ui.input type="number" name="transaction_id" label="ID تراکنش" placeholder="" required />
                    <x-ui.button variant="success" type="submit">تایید پرداخت</x-ui.button>
                </form>
            @endif

            <form method="post" action="{{ route('admin.orders.status',$order) }}" class="grid grid-cols-1 md:grid-cols-3 gap-2">
                @csrf
                <div class="space-y-1">
                    <label class="block text-xs text-gray-300">وضعیت سفارش</label>
                    <select name="status" class="w-full rounded-lg bg-white/5 border border-white/10 focus:border-cherry-600 focus:ring-2 focus:ring-cherry-600/30 outline-none py-2.5 px-3 text-sm transition">
                        @php
                            $statusLabels = [
                                'pending' => 'در انتظار',
                                'confirmed' => 'در حال آماده سازی',
                                'cancelled' => 'لغو شده',
                                'shipped' => 'ارسال شده'
                            ];
                        @endphp
                        @foreach(['pending','confirmed','cancelled','shipped'] as $st)
                            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $statusLabels[$st] ?? $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="self-end">
                    <x-ui.button type="submit">به‌روزرسانی وضعیت</x-ui.button>
                </div>
            </form>
        </div>
    </div>

    <!-- User Info Modal -->
    @if($order->user_id && $order->user)
        <div id="userModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 transition-opacity" onclick="closeUserModal()"></div>
            
            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-[#0d0d14] rounded-xl border border-white/10 shadow-2xl max-w-md w-full transform transition-all">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 border-b border-white/10">
                        <h3 class="text-lg font-bold">اطلاعات کاربری</h3>
                        <button onclick="closeUserModal()" class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <!-- Name -->
                        <div>
                            <div class="text-xs text-gray-400 mb-1">نام</div>
                            <div class="text-sm font-semibold">{{ $order->user->name }}</div>
                        </div>

                        <!-- Instagram ID -->
                        <div>
                            <div class="text-xs text-gray-400 mb-1">آیدی اینستاگرام</div>
                            <div class="flex items-center gap-2">
                                <a 
                                    href="https://instagram.com/{{ ltrim($order->user->instagram_id, '@') }}" 
                                    target="_blank"
                                    class="text-sm font-semibold text-cherry-400 hover:text-cherry-300 transition flex items-center gap-1"
                                >
                                    {{ $order->user->instagram_id }}
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                                <button 
                                    onclick="copyToClipboard('{{ $order->user->instagram_id }}')"
                                    class="text-xs bg-white/5 hover:bg-white/10 px-2 py-1 rounded transition"
                                    title="کپی"
                                >
                                    📋
                                </button>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <div class="text-xs text-gray-400 mb-1">شماره تلفن</div>
                            <div class="flex items-center gap-2">
                                <a 
                                    href="tel:{{ $order->user->phone }}"
                                    class="text-sm font-semibold text-cherry-400 hover:text-cherry-300 transition"
                                >
                                    {{ $order->user->phone }}
                                </a>
                                <button 
                                    onclick="copyToClipboard('{{ $order->user->phone }}')"
                                    class="text-xs bg-white/5 hover:bg-white/10 px-2 py-1 rounded transition"
                                    title="کپی"
                                >
                                    📋
                                </button>
                            </div>
                        </div>

                        <!-- Address -->
                        @if($order->user->address)
                            <div>
                                <div class="text-xs text-gray-400 mb-1">آدرس</div>
                                <div class="text-sm text-gray-300 bg-white/5 p-3 rounded border border-white/10">
                                    {{ $order->user->address }}
                                </div>
                            </div>
                        @endif

                        <!-- Member Since -->
                        <div>
                            <div class="text-xs text-gray-400 mb-1">عضویت از</div>
                            <div class="text-sm">{{ $order->user->created_at->format('Y/m/d') }}</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 border-t border-white/10 flex gap-2">
                        <a 
                            href="https://instagram.com/{{ ltrim($order->user->instagram_id, '@') }}" 
                            target="_blank"
                            class="flex-1 bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white text-center py-2 rounded transition text-sm font-medium"
                        >
                            پیام در اینستاگرام
                        </a>
                        <a 
                            href="tel:{{ $order->user->phone }}"
                            class="flex-1 bg-cherry-600 hover:bg-cherry-700 text-white text-center py-2 rounded transition text-sm font-medium"
                        >
                            تماس تلفنی
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openUserModal() {
                const modal = document.getElementById('userModal');
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeUserModal() {
                const modal = document.getElementById('userModal');
                modal.classList.add('hidden');
                document.body.style.overflow = '';
            }

            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    // Show temporary notification
                    const notification = document.createElement('div');
                    notification.textContent = '✓ کپی شد';
                    notification.className = 'fixed top-4 left-1/2 -translate-x-1/2 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-[60] text-sm';
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 2000);
                });
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeUserModal();
                }
            });
        </script>
    @endif
</x-admin.layout>


