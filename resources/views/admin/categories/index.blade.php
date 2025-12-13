<x-admin.layout :title="'دسته‌بندی‌ها'">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">دسته‌بندی‌ها</h1>
        <a href="{{ route('admin.categories.create') }}" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">
            دسته‌بندی جدید
        </a>
    </div>
    
    <form method="get" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <x-ui.input name="q" :value="$q ?? ''" label="جستجو" placeholder="نام دسته‌بندی" />
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
                        <th class="p-3 text-right font-semibold text-gray-200">نام</th>
                        <th class="p-3 font-semibold text-gray-200">Slug</th>
                        <th class="p-3 font-semibold text-gray-200">تعداد محصولات</th>
                        <th class="p-3 font-semibold text-gray-200">وضعیت</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($categories as $category)
                        <tr class="hover:bg-white/5 transition">
                            <td class="p-3">
                                <div class="font-semibold">{{ $category->name }}</div>
                                @if($category->description)
                                    <div class="text-xs text-gray-400 mt-1">{{ Str::limit($category->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="p-3">
                                <code class="text-xs bg-gray-800 px-2 py-1 rounded">{{ $category->slug }}</code>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs bg-blue-500/20 text-blue-300">
                                    {{ $category->products_count }} محصول
                                </span>
                            </td>
                            <td class="p-3">
                                @if($category->is_active)
                                    <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.categories.show', $category) }}" class="text-blue-400 hover:text-blue-300">مشاهده</a>
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-yellow-400 hover:text-yellow-300">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-{{ $category->is_active ? 'red' : 'green' }}-400 hover:text-{{ $category->is_active ? 'red' : 'green' }}-300">
                                            {{ $category->is_active ? 'غیرفعال' : 'فعال' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">دسته‌بندی‌ای یافت نشد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="md:hidden">
            @forelse($categories as $category)
                <div class="p-4 border-b border-white/10 last:border-b-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-semibold">{{ $category->name }}</div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.categories.show', $category) }}" class="text-blue-400 hover:text-blue-300 text-sm">مشاهده</a>
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">ویرایش</a>
                        </div>
                    </div>
                    @if($category->description)
                        <div class="text-sm text-gray-400 mb-2">{{ Str::limit($category->description, 80) }}</div>
                    @endif
                    <div class="grid grid-cols-2 gap-2 text-sm mb-2">
                        <div>
                            <span class="text-gray-400">Slug:</span>
                            <code class="text-xs bg-gray-800 px-1 py-0.5 rounded">{{ $category->slug }}</code>
                        </div>
                        <div>
                            <span class="text-gray-400">محصولات:</span>
                            <span class="text-blue-300">{{ $category->products_count }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div>
                            @if($category->is_active)
                                <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-{{ $category->is_active ? 'red' : 'green' }}-400 hover:text-{{ $category->is_active ? 'red' : 'green' }}-300 text-sm">
                                {{ $category->is_active ? 'غیرفعال' : 'فعال' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">دسته‌بندی‌ای یافت نشد</div>
            @endforelse
        </div>
    </div>
    
    <div class="mt-4">
        {{ $categories->links() }}
    </div>
</x-admin.layout>
