<x-admin.layout :title="'کدهای تخفیف'">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">کدهای تخفیف</h1>
        <a href="{{ route('admin.discount-codes.create') }}" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">
            کد تخفیف جدید
        </a>
    </div>
    
    <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-ui.input name="q" :value="$q ?? ''" label="جستجو" placeholder="کد تخفیف" />
        <x-ui.select name="status" label="وضعیت">
            <option value="">همه وضعیت‌ها</option>
            @foreach(['active'=>'فعال','expired'=>'منقضی شده','exhausted'=>'تمام شده'] as $key=>$label)
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
                        <th class="p-3 font-semibold text-gray-200">نوع</th>
                        <th class="p-3 font-semibold text-gray-200">مقدار</th>
                        <th class="p-3 font-semibold text-gray-200">استفاده شده</th>
                        <th class="p-3 font-semibold text-gray-200">وضعیت</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($discountCodes as $discountCode)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-3">
                                <div class="font-mono text-cherry-400">{{ $discountCode->code }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs {{ $discountCode->type === 'percentage' ? 'bg-blue-500/20 text-blue-300' : 'bg-green-500/20 text-green-300' }}">
                                    {{ $discountCode->type === 'percentage' ? 'درصدی' : 'مبلغ ثابت' }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($discountCode->type === 'percentage')
                                    {{ $discountCode->value }}%
                                @else
                                    {{ number_format($discountCode->value) }} تومان
                                @endif
                                @if($discountCode->max_discount_amount)
                                    <div class="text-xs text-gray-400">حداکثر: {{ number_format($discountCode->max_discount_amount) }} تومان</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="text-sm">
                                    {{ $discountCode->used_count }}
                                    @if($discountCode->usage_limit)
                                        / {{ $discountCode->usage_limit }}
                                    @else
                                        / نامحدود
                                    @endif
                                </div>
                                @if($discountCode->usage_limit)
                                    <div class="w-full bg-gray-700 rounded-full h-1.5 mt-1">
                                        <div class="bg-cherry-600 h-1.5 rounded-full" style="width: {{ min(100, ($discountCode->used_count / $discountCode->usage_limit) * 100) }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td class="p-3">
                                @if(!$discountCode->is_active)
                                    <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                                @elseif($discountCode->expires_at && $discountCode->expires_at->isPast())
                                    <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">منقضی شده</span>
                                @elseif($discountCode->usage_limit && $discountCode->used_count >= $discountCode->usage_limit)
                                    <span class="px-2 py-1 rounded text-xs bg-orange-500/20 text-orange-300">تمام شده</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.discount-codes.show', $discountCode) }}" class="text-blue-400 hover:text-blue-300">مشاهده</a>
                                    <a href="{{ route('admin.discount-codes.edit', $discountCode) }}" class="text-yellow-400 hover:text-yellow-300">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.discount-codes.toggle-status', $discountCode) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $discountCode->is_active ? 'red' : 'green' }}-400 hover:text-{{ $discountCode->is_active ? 'red' : 'green' }}-300">
                                            {{ $discountCode->is_active ? 'غیرفعال' : 'فعال' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">کد تخفیفی یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="md:hidden">
            @forelse($discountCodes as $discountCode)
                <div class="p-4 border-b border-white/10 last:border-b-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-mono text-cherry-400 font-bold">{{ $discountCode->code }}</div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.discount-codes.show', $discountCode) }}" class="text-blue-400 hover:text-blue-300 text-sm">مشاهده</a>
                            <a href="{{ route('admin.discount-codes.edit', $discountCode) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">ویرایش</a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                        <div>
                            <span class="text-gray-400">نوع:</span>
                            <span class="px-2 py-1 rounded text-xs {{ $discountCode->type === 'percentage' ? 'bg-blue-500/20 text-blue-300' : 'bg-green-500/20 text-green-300' }}">
                                {{ $discountCode->type === 'percentage' ? 'درصدی' : 'مبلغ ثابت' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">مقدار:</span>
                            @if($discountCode->type === 'percentage')
                                {{ $discountCode->value }}%
                            @else
                                {{ number_format($discountCode->value) }} تومان
                            @endif
                        </div>
                    </div>
                    <div class="text-sm mb-2">
                        <span class="text-gray-400">استفاده شده:</span>
                        {{ $discountCode->used_count }}
                        @if($discountCode->usage_limit)
                            / {{ $discountCode->usage_limit }}
                        @else
                            / نامحدود
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            @if(!$discountCode->is_active)
                                <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                            @elseif($discountCode->expires_at && $discountCode->expires_at->isPast())
                                <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">منقضی شده</span>
                            @elseif($discountCode->usage_limit && $discountCode->used_count >= $discountCode->usage_limit)
                                <span class="px-2 py-1 rounded text-xs bg-orange-500/20 text-orange-300">تمام شده</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.discount-codes.toggle-status', $discountCode) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-{{ $discountCode->is_active ? 'red' : 'green' }}-400 hover:text-{{ $discountCode->is_active ? 'red' : 'green' }}-300 text-sm">
                                {{ $discountCode->is_active ? 'غیرفعال' : 'فعال' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">کد تخفیفی یافت نشد</div>
            @endforelse
        </div>
    </div>
    
    <div class="mt-4">
        {{ $discountCodes->links() }}
    </div>
</x-admin.layout>
