<x-admin.layout :title="'مدیریت محصولات'">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold">محصولات</h1>
            <div class="text-xs text-gray-400 mt-1">
                کل: {{ $totalProducts ?? $products->total() }} محصول
                @if(isset($activeProducts))
                    | فعال: {{ $activeProducts }}
                    | غیرفعال: {{ ($totalProducts ?? 0) - $activeProducts }}
                @endif
            </div>
        </div>
        <a href="{{ route('admin.products.create') }}" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-3 py-2 transition">+ محصول جدید</a>
    </div>
    <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent">
        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-[800px] w-full text-sm">
            <thead>
                <tr class="bg-white/5/50">
                    <th class="p-3 text-right font-semibold text-gray-200">عنوان</th>
                    <th class="p-3 font-semibold text-gray-200">قیمت</th>
                    <th class="p-3 font-semibold text-gray-200">موجودی</th>
                    <th class="p-3 font-semibold text-gray-200">وضعیت</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse($products as $product)
                    <tr class="hover:bg-white/5 transition">
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center text-lg">🛍️</div>
                                <div class="min-w-0">
                                    <div class="font-medium truncate">{{ $product->title }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ $product->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-3 whitespace-nowrap">{{ number_format($product->price) }} <span class="text-xs text-gray-400">تومان</span></td>
                        <td class="p-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs bg-white/5 border border-white/10">
                                {{ $product->stock }}
                            </span>
                        </td>
                        <td class="p-3">
                            @if($product->is_active)
                                <span class="inline-flex items-center gap-1 text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md text-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    فعال
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md text-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    غیرفعال
                                </span>
                            @endif
                        </td>
                        <td class="p-3 text-left">
                            <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex items-center gap-1 text-cherry-300 hover:text-cherry-200 px-2 py-1 rounded-md hover:bg-cherry-500/10 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                                    <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712Z" />
                                    <path d="M19.513 8.199l-3.712-3.712-9.42 9.42a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .927.927l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l9.426-9.414Z" />
                                    <path d="M5.25 19.5h13.5a.75.75 0 0 1 0 1.5H5.25a.75.75 0 0 1 0-1.5Z" />
                                </svg>
                                ویرایش
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center">
                            <div class="text-gray-400 mb-2">هیچ محصولی وجود ندارد</div>
                            <a href="{{ route('admin.products.create') }}" class="inline-block bg-cherry-600 hover:bg-cherry-700 text-white px-4 py-2 rounded text-sm">
                                افزودن اولین محصول
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden p-4 space-y-3">
            @forelse($products as $product)
                <div class="rounded-lg border border-white/10 bg-white/5 p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-lg bg-white/5 flex items-center justify-center text-2xl flex-shrink-0">🛍️</div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-white truncate">{{ $product->title }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $product->slug }}</div>
                        </div>
                        @if($product->is_active)
                            <span class="inline-flex items-center gap-1 text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md text-xs flex-shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                فعال
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-amber-300 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md text-xs flex-shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                غیرفعال
                            </span>
                        @endif
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-gray-400 mb-1">قیمت</div>
                            <div class="text-white font-semibold">{{ number_format($product->price) }} <span class="text-xs text-gray-400">تومان</span></div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 mb-1">موجودی</div>
                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs bg-white/5 border border-white/10">
                                {{ $product->stock }}
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center justify-center gap-2 bg-cherry-600 hover:bg-cherry-700 text-white px-4 py-2 rounded transition text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712Z" />
                            <path d="M19.513 8.199l-3.712-3.712-9.42 9.42a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .927.927l2.685-.8a5.25 5.25 0 0 0 2.214-1.32l9.426-9.414Z" />
                            <path d="M5.25 19.5h13.5a.75.75 0 0 1 0 1.5H5.25a.75.75 0 0 1 0-1.5Z" />
                        </svg>
                        ویرایش محصول
                    </a>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-3">هیچ محصولی وجود ندارد</div>
                    <a href="{{ route('admin.products.create') }}" class="inline-block bg-cherry-600 hover:bg-cherry-700 text-white px-4 py-2 rounded text-sm">
                        افزودن اولین محصول
                    </a>
                </div>
            @endforelse
        </div>
    </div>
    
    @if($products->hasPages())
        <div class="mt-4">{{ $products->links() }}</div>
    @endif
</x-admin.layout>


