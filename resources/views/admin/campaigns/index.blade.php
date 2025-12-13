<x-admin.layout :title="'کمپین‌ها'">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">کمپین‌ها</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.campaigns-analytics') }}" class="bg-blue-600 hover:bg-blue-700 text-white rounded px-4 py-2">
                📊 آمار و گزارش‌ها
            </a>
            <a href="{{ route('admin.campaigns.create') }}" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">
                کمپین جدید
            </a>
        </div>
    </div>
    
    <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-ui.input name="q" :value="$q ?? ''" label="جستجو" placeholder="نام کمپین" />
        <x-ui.select name="status" label="وضعیت">
            <option value="">همه وضعیت‌ها</option>
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>فعال</option>
            <option value="upcoming" {{ $status === 'upcoming' ? 'selected' : '' }}>آینده</option>
            <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>منقضی شده</option>
            <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
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
                        <th class="p-3 text-right font-semibold text-gray-200">نام کمپین</th>
                        <th class="p-3 font-semibold text-gray-200">نوع تخفیف</th>
                        <th class="p-3 font-semibold text-gray-200">بازه زمانی</th>
                        <th class="p-3 font-semibold text-gray-200">اهداف</th>
                        <th class="p-3 font-semibold text-gray-200">وضعیت</th>
                        <th class="p-3 font-semibold text-gray-200">آمار فروش</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-3">
                                <div class="font-semibold">{{ $campaign->name }}</div>
                                @if($campaign->badge_text)
                                    <div class="text-xs text-cherry-400">{{ $campaign->badge_text }}</div>
                                @endif
                                @if($campaign->description)
                                    <div class="text-xs text-gray-400 mt-1">{{ Str::limit($campaign->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="text-sm">
                                    @if($campaign->type === 'percentage')
                                        {{ $campaign->discount_value }}%
                                    @else
                                        {{ number_format($campaign->discount_value) }} تومان
                                    @endif
                                </div>
                                @if($campaign->type === 'percentage' && $campaign->max_discount_amount)
                                    <div class="text-xs text-gray-400">حداکثر {{ number_format($campaign->max_discount_amount) }} تومان</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="text-sm">
                                    <div>شروع: {{ $campaign->starts_at->format('Y/m/d H:i') }}</div>
                                    <div>پایان: {{ $campaign->ends_at->format('Y/m/d H:i') }}</div>
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-300">
                                    {{ $campaign->targets_count }} هدف
                                </span>
                            </td>
                            <td class="p-3">
                                @if($campaign->status === 'active')
                                    <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                                @elseif($campaign->status === 'upcoming')
                                    <span class="px-2 py-1 rounded text-xs bg-yellow-500/20 text-yellow-300">آینده</span>
                                @elseif($campaign->status === 'expired')
                                    <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">منقضی شده</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-gray-500/20 text-gray-300">غیرفعال</span>
                                @endif
                                @if($campaign->priority > 0)
                                    <div class="text-xs text-gray-400 mt-1">اولویت: {{ $campaign->priority }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="text-sm">
                                    <div>{{ $campaign->sales_count }} فروش</div>
                                    <div class="text-xs text-green-400">{{ number_format($campaign->total_sales) }} تومان تخفیف</div>
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-blue-400 hover:text-blue-300">مشاهده</a>
                                    <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="text-yellow-400 hover:text-yellow-300">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.campaigns.toggle-status', $campaign) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $campaign->is_active ? 'red' : 'green' }}-400 hover:text-{{ $campaign->is_active ? 'red' : 'green' }}-300">
                                            {{ $campaign->is_active ? 'غیرفعال' : 'فعال' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400">کمپینی یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="md:hidden">
            @forelse($campaigns as $campaign)
                <div class="p-4 border-b border-white/10 last:border-b-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-semibold">{{ $campaign->name }}</div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-blue-400 hover:text-blue-300 text-sm">مشاهده</a>
                            <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">ویرایش</a>
                        </div>
                    </div>
                    
                    @if($campaign->badge_text)
                        <div class="text-xs text-cherry-400 mb-2">{{ $campaign->badge_text }}</div>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                        <div>
                            <span class="text-gray-400">تخفیف:</span>
                            @if($campaign->type === 'percentage')
                                <span class="text-cherry-300">{{ $campaign->discount_value }}%</span>
                            @else
                                <span class="text-cherry-300">{{ number_format($campaign->discount_value) }} تومان</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400">اهداف:</span>
                            <span class="text-blue-300">{{ $campaign->targets_count }}</span>
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-400 mb-2">
                        {{ $campaign->starts_at->format('Y/m/d H:i') }} - {{ $campaign->ends_at->format('Y/m/d H:i') }}
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div>
                            @if($campaign->status === 'active')
                                <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                            @elseif($campaign->status === 'upcoming')
                                <span class="px-2 py-1 rounded text-xs bg-yellow-500/20 text-yellow-300">آینده</span>
                            @elseif($campaign->status === 'expired')
                                <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">منقضی شده</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-gray-500/20 text-gray-300">غیرفعال</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-400">{{ $campaign->sales_count }} فروش</div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">کمپینی یافت نشد</div>
            @endforelse
        </div>
    </div>
    
    <div class="mt-4">
        {{ $campaigns->links() }}
    </div>
</x-admin.layout>
