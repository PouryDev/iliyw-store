<x-admin.layout :title="'جزئیات کد تخفیف'">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.discount-codes.index') }}" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-xl font-bold">جزئیات کد تخفیف</h1>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- کد تخفیف Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-semibold">اطلاعات کد تخفیف</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.discount-codes.edit', $discountCode) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">ویرایش</a>
                        <form method="POST" action="{{ route('admin.discount-codes.toggle-status', $discountCode) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-{{ $discountCode->is_active ? 'red' : 'green' }}-400 hover:text-{{ $discountCode->is_active ? 'red' : 'green' }}-300 text-sm">
                                {{ $discountCode->is_active ? 'غیرفعال' : 'فعال' }}
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">کد تخفیف</label>
                        <div class="font-mono text-cherry-400 text-lg font-bold">{{ $discountCode->code }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">نوع</label>
                        <span class="px-3 py-1 rounded-full text-sm {{ $discountCode->type === 'percentage' ? 'bg-blue-500/20 text-blue-300' : 'bg-green-500/20 text-green-300' }}">
                            {{ $discountCode->type === 'percentage' ? 'درصدی' : 'مبلغ ثابت' }}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">مقدار</label>
                        <div class="text-lg font-semibold">
                            @if($discountCode->type === 'percentage')
                                {{ $discountCode->value }}%
                            @else
                                {{ number_format($discountCode->value) }} تومان
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">وضعیت</label>
                        @if(!$discountCode->is_active)
                            <span class="px-3 py-1 rounded-full text-sm bg-red-500/20 text-red-300">غیرفعال</span>
                        @elseif($discountCode->expires_at && $discountCode->expires_at->isPast())
                            <span class="px-3 py-1 rounded-full text-sm bg-red-500/20 text-red-300">منقضی شده</span>
                        @elseif($discountCode->usage_limit && $discountCode->used_count >= $discountCode->usage_limit)
                            <span class="px-3 py-1 rounded-full text-sm bg-orange-500/20 text-orange-300">تمام شده</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm bg-green-500/20 text-green-300">فعال</span>
                        @endif
                    </div>
                    
                    @if($discountCode->max_discount_amount)
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">حداکثر تخفیف</label>
                            <div>{{ number_format($discountCode->max_discount_amount) }} تومان</div>
                        </div>
                    @endif
                    
                    @if($discountCode->min_order_amount)
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">حداقل سفارش</label>
                            <div>{{ number_format($discountCode->min_order_amount) }} تومان</div>
                        </div>
                    @endif
                    
                    @if($discountCode->starts_at)
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">تاریخ شروع</label>
                            <div>{{ $discountCode->starts_at->format('Y/m/d H:i') }}</div>
                        </div>
                    @endif
                    
                    @if($discountCode->expires_at)
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">تاریخ انقضا</label>
                            <div>{{ $discountCode->expires_at->format('Y/m/d H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- آمار استفاده -->
            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent p-6">
                <h3 class="text-lg font-semibold mb-4">آمار استفاده</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="text-center p-4 bg-white/5 rounded-lg">
                        <div class="text-2xl font-bold text-cherry-400">{{ $discountCode->used_count }}</div>
                        <div class="text-sm text-gray-400">تعداد استفاده</div>
                    </div>
                    
                    <div class="text-center p-4 bg-white/5 rounded-lg">
                        <div class="text-2xl font-bold text-green-400">
                            @if($discountCode->usage_limit)
                                {{ $discountCode->usage_limit }}
                            @else
                                ∞
                            @endif
                        </div>
                        <div class="text-sm text-gray-400">حد مجاز</div>
                    </div>
                </div>
                
                @if($discountCode->usage_limit)
                    <div class="mb-2">
                        <div class="flex justify-between text-sm text-gray-400 mb-1">
                            <span>پیشرفت استفاده</span>
                            <span>{{ min(100, round(($discountCode->used_count / $discountCode->usage_limit) * 100, 1)) }}%</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-cherry-600 h-2 rounded-full" style="width: {{ min(100, ($discountCode->used_count / $discountCode->usage_limit) * 100) }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- لیست استفاده‌ها -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent p-6">
                <h3 class="text-lg font-semibold mb-4">تاریخچه استفاده</h3>
                
                @if($discountCode->usages->count() > 0)
                    <div class="space-y-3">
                        @foreach($discountCode->usages as $usage)
                            <div class="p-3 bg-white/5 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="text-sm">
                                        <div class="font-semibold">{{ $usage->user->name ?? 'کاربر حذف شده' }}</div>
                                        <div class="text-gray-400">{{ $usage->user->phone ?? 'بدون شماره تلفن' }}</div>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-cherry-400 font-semibold">{{ number_format($usage->discount_amount) }} تومان</div>
                                        <div class="text-xs text-gray-400">{{ $usage->created_at->format('Y/m/d H:i') }}</div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400">
                                    سفارش #{{ $usage->order->id }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-400 py-8">
                        هنوز استفاده‌ای از این کد تخفیف نشده است
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin.layout>
