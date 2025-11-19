<x-admin.layout :title="'جزئیات دسته‌بندی'">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.categories.index') }}" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-xl font-bold">جزئیات دسته‌بندی</h1>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Category Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-lg font-semibold">اطلاعات دسته‌بندی</h2>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-yellow-400 hover:text-yellow-300 text-sm">ویرایش</a>
                        <form method="POST" action="{{ route('admin.categories.toggle-status', $category) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-{{ $category->is_active ? 'red' : 'green' }}-400 hover:text-{{ $category->is_active ? 'red' : 'green' }}-300 text-sm">
                                {{ $category->is_active ? 'غیرفعال' : 'فعال' }}
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">نام</label>
                        <div class="text-lg font-bold">{{ $category->name }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Slug</label>
                        <code class="text-sm bg-gray-800 px-2 py-1 rounded">{{ $category->slug }}</code>
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">وضعیت</label>
                        @if($category->is_active)
                            <span class="px-3 py-1 rounded-full text-sm bg-green-500/20 text-green-300">فعال</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm bg-red-500/20 text-red-300">غیرفعال</span>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">تاریخ ایجاد</label>
                        <div>{{ $category->created_at->format('Y/m/d H:i') }}</div>
                    </div>
                    
                    @if($category->description)
                        <div class="md:col-span-2">
                            <label class="block text-sm text-gray-400 mb-1">توضیحات</label>
                            <div class="text-sm">{{ $category->description }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Products List -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-white/10 bg-gradient-to-b from-white/5 to-transparent p-6">
                <h3 class="text-lg font-semibold mb-4">محصولات این دسته‌بندی</h3>
                
                @if($category->products->count() > 0)
                    <div class="space-y-3">
                        @foreach($category->products as $product)
                            <div class="p-3 bg-white/5 rounded-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="text-sm">
                                        <div class="font-semibold">{{ $product->title }}</div>
                                        <div class="text-gray-400">{{ number_format($product->price) }} تومان</div>
                                    </div>
                                    <div class="text-left">
                                        @if($product->is_active)
                                            <span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-300">فعال</span>
                                        @else
                                            <span class="px-2 py-1 rounded text-xs bg-red-500/20 text-red-300">غیرفعال</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400">
                                    موجودی: {{ $product->stock }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-400 py-8">
                        محصولی در این دسته‌بندی وجود ندارد
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin.layout>
