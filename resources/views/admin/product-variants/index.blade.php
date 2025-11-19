<x-admin.layout :title="'تنوع‌های محصول'">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-xl font-bold">تنوع‌های محصول</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $product->title }}</p>
        </div>
        <a href="{{ route('admin.product-variants.create', ['product_id' => $product->id]) }}" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">
            تنوع جدید
        </a>
    </div>
    
    <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-[720px] w-full text-sm">
                <thead>
                    <tr class="bg-white/5/50">
                        <th class="p-3 text-right font-semibold text-gray-200">رنگ</th>
                        <th class="p-3 font-semibold text-gray-200">سایز</th>
                        <th class="p-3 font-semibold text-gray-200">SKU</th>
                        <th class="p-3 font-semibold text-gray-200">موجودی</th>
                        <th class="p-3 font-semibold text-gray-200">قیمت</th>
                        <th class="p-3 font-semibold text-gray-200">وضعیت</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($variants as $variant)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-3">
                                @if($variant->color)
                                    <div class="flex items-center gap-2">
                                        @if($variant->color->hex_code)
                                            <div class="w-6 h-6 rounded-full border border-white/20" style="background-color: {{ $variant->color->hex_code }}"></div>
                                        @else
                                            <div class="w-6 h-6 rounded-full border border-white/20 bg-gray-600"></div>
                                        @endif
                                        <span class="text-sm">{{ $variant->color->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($variant->size)
                                    <span class="px-2 py-1 rounded text-xs bg-gray-700">{{ $variant->size->name }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <code class="text-xs bg-gray-800 px-2 py-1 rounded">{{ $variant->sku }}</code>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs {{ $variant->stock > 0 ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300' }}">
                                    {{ $variant->stock }}
                                </span>
                            </td>
                            <td class="p-3">
                                @if($variant->price && $variant->price !== $product->price)
                                    <div class="text-sm">{{ number_format($variant->price) }} تومان</div>
                                    <div class="text-xs text-gray-400">(مخصوص این تنوع)</div>
                                @else
                                    <div class="text-sm text-gray-400">قیمت محصول</div>
                                @endif
                            </td>
                            <td class="p-3">
                                @if($variant->is_active)
                                    <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.product-variants.show', $variant) }}" class="text-blue-400 hover:text-blue-300">مشاهده</a>
                                    <a href="{{ route('admin.product-variants.edit', $variant) }}" class="text-yellow-400 hover:text-yellow-300">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.product-variants.toggle-status', $variant) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $variant->is_active ? 'red' : 'green' }}-400 hover:text-{{ $variant->is_active ? 'red' : 'green' }}-300">
                                            {{ $variant->is_active ? 'غیرفعال' : 'فعال' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <span>تنوعی برای این محصول ایجاد نشده است</span>
                                    <a href="{{ route('admin.product-variants.create', ['product_id' => $product->id]) }}" class="text-cherry-400 hover:text-cherry-300 text-sm">
                                        اولین تنوع را ایجاد کنید
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="md:hidden">
            @forelse($variants as $variant)
                <div class="p-4 border-b border-white/10 last:border-b-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-semibold">{{ $variant->display_name }}</div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.product-variants.show', $variant) }}" class="text-blue-400 hover:text-blue-300 text-sm">مشاهده</a>
                            <a href="{{ route('admin.product-variants.edit', $variant) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">ویرایش</a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                        <div>
                            <span class="text-gray-400">SKU:</span>
                            <code class="text-xs bg-gray-800 px-1 py-0.5 rounded">{{ $variant->sku }}</code>
                        </div>
                        <div>
                            <span class="text-gray-400">موجودی:</span>
                            <span class="{{ $variant->stock > 0 ? 'text-green-300' : 'text-red-300' }}">{{ $variant->stock }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            @if($variant->is_active)
                                <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.product-variants.toggle-status', $variant) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-{{ $variant->is_active ? 'red' : 'green' }}-400 hover:text-{{ $variant->is_active ? 'red' : 'green' }}-300 text-sm">
                                {{ $variant->is_active ? 'غیرفعال' : 'فعال' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <span>تنوعی برای این محصول ایجاد نشده است</span>
                        <a href="{{ route('admin.product-variants.create', ['product_id' => $product->id]) }}" class="text-cherry-400 hover:text-cherry-300 text-sm">
                            اولین تنوع را ایجاد کنید
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
    
    <div class="mt-4 flex justify-between items-center">
        <div>
            {{ $variants->links() }}
        </div>
        <a href="{{ route('admin.products.edit', $product) }}" class="text-gray-400 hover:text-white text-sm">
            بازگشت به ویرایش محصول
        </a>
    </div>
</x-admin.layout>
