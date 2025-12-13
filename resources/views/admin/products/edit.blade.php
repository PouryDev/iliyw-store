<x-admin.layout :title="'ویرایش محصول'">
    <h1 class="text-xl font-bold mb-4">ویرایش محصول</h1>
    
    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/50 bg-rose-500/10 p-4 mb-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-rose-400 mb-2">خطاهای اعتبارسنجی:</h3>
                    <ul class="text-xs text-rose-300 space-y-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
    
    <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3">
        @csrf
        @method('PUT')
        <x-ui.select name="category_id" label="دسته‌بندی">
            <option value="">بدون دسته‌بندی</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.input name="title" :value="old('title',$product->title)" label="عنوان" required />
        <x-ui.input name="slug" :value="old('slug',$product->slug)" label="اسلاگ" required />
        <x-ui.textarea name="description" label="توضیحات" rows="4">{{ old('description',$product->description) }}</x-ui.textarea>
        <div class="grid grid-cols-2 gap-3">
            <x-ui.input type="number" name="price" :value="old('price',$product->price)" label="قیمت (تومان)" required />
            <x-ui.input type="number" name="stock" :value="old('stock',$product->stock)" label="موجودی" required />
        </div>
        
        <div class="border-t border-white/10 pt-4">
            <h3 class="text-lg font-semibold mb-3">تنوع محصول</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <x-ui.checkbox name="has_variants" value="1" label="دارای تنوع" :checked="old('has_variants', $product->has_variants)" />
                <x-ui.checkbox name="has_colors" value="1" label="دارای رنگ‌بندی" :checked="old('has_colors', $product->has_colors)" />
                <x-ui.checkbox name="has_sizes" value="1" label="دارای سایزبندی" :checked="old('has_sizes', $product->has_sizes)" />
            </div>
            @if($product->has_variants)
                <div class="mt-3">
                    <a href="{{ route('admin.product-variants.index', ['product_id' => $product->id]) }}" 
                       class="inline-flex items-center gap-2 text-cherry-400 hover:text-cherry-300 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        مدیریت تنوع‌های محصول
                    </a>
                </div>
            @endif
        </div>
        
        <x-ui.checkbox name="is_active" value="1" label="فعال" :checked="$product->is_active" />
        <x-ui.file name="images[]" label="افزودن تصاویر جدید" :multiple="true" accept="image/*" />
        <div class="flex gap-2 flex-wrap">
            @foreach($product->images as $img)
                <img src="{{ $img->url }}" class="w-16 h-16 rounded object-cover" />
            @endforeach
        </div>
        <x-ui.button type="submit">ذخیره</x-ui.button>
    </form>
</x-admin.layout>


