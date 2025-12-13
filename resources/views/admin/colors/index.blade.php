<x-admin.layout :title="'رنگ‌ها'">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">رنگ‌ها</h1>
        <a href="{{ route('admin.colors.create') }}" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">
            رنگ جدید
        </a>
    </div>
    
    <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-ui.input name="q" :value="$q ?? ''" label="جستجو" placeholder="نام رنگ" />
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
                        <th class="p-3 text-right font-semibold text-gray-200">رنگ</th>
                        <th class="p-3 font-semibold text-gray-200">نام</th>
                        <th class="p-3 font-semibold text-gray-200">کد HEX</th>
                        <th class="p-3 font-semibold text-gray-200">تعداد استفاده</th>
                        <th class="p-3 font-semibold text-gray-200">وضعیت</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($colors as $color)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-3">
                                @if($color->hex_code)
                                    <div class="w-8 h-8 rounded-full border border-white/20" style="background-color: {{ $color->hex_code }}"></div>
                                @else
                                    <div class="w-8 h-8 rounded-full border border-white/20 bg-gray-600"></div>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="font-semibold">{{ $color->name }}</div>
                            </td>
                            <td class="p-3">
                                @if($color->hex_code)
                                    <code class="text-xs bg-gray-800 px-2 py-1 rounded">{{ $color->hex_code }}</code>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-300">
                                    {{ $color->product_variants_count }} محصول
                                </span>
                            </td>
                            <td class="p-3">
                                @if($color->is_active)
                                    <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.colors.show', $color) }}" class="text-blue-400 hover:text-blue-300">مشاهده</a>
                                    <a href="{{ route('admin.colors.edit', $color) }}" class="text-yellow-400 hover:text-yellow-300">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.colors.toggle-status', $color) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $color->is_active ? 'red' : 'green' }}-400 hover:text-{{ $color->is_active ? 'red' : 'green' }}-300">
                                            {{ $color->is_active ? 'غیرفعال' : 'فعال' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-gray-400">رنگی یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="md:hidden">
            @forelse($colors as $color)
                <div class="p-4 border-b border-white/10 last:border-b-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            @if($color->hex_code)
                                <div class="w-8 h-8 rounded-full border border-white/20" style="background-color: {{ $color->hex_code }}"></div>
                            @else
                                <div class="w-8 h-8 rounded-full border border-white/20 bg-gray-600"></div>
                            @endif
                            <div class="font-semibold">{{ $color->name }}</div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.colors.show', $color) }}" class="text-blue-400 hover:text-blue-300 text-sm">مشاهده</a>
                            <a href="{{ route('admin.colors.edit', $color) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">ویرایش</a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                        <div>
                            <span class="text-gray-400">کد HEX:</span>
                            @if($color->hex_code)
                                <code class="text-xs bg-gray-800 px-1 py-0.5 rounded">{{ $color->hex_code }}</code>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-400">استفاده:</span>
                            <span class="text-blue-300">{{ $color->product_variants_count }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            @if($color->is_active)
                                <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.colors.toggle-status', $color) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-{{ $color->is_active ? 'red' : 'green' }}-400 hover:text-{{ $color->is_active ? 'red' : 'green' }}-300 text-sm">
                                {{ $color->is_active ? 'غیرفعال' : 'فعال' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">رنگی یافت نشد</div>
            @endforelse
        </div>
    </div>
    
    <div class="mt-4">
        {{ $colors->links() }}
    </div>
</x-admin.layout>
