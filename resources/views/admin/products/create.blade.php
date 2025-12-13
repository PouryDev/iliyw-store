<x-admin.layout :title="'افزودن محصول'">
    <h1 class="text-xl font-bold mb-4">افزودن محصول</h1>
    
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
    
    <form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3">
        @csrf
        <x-ui.select name="category_id" label="دسته‌بندی">
            <option value="">بدون دسته‌بندی</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </x-ui.select>
        <x-ui.input name="title" :value="old('title')" label="عنوان" placeholder="" required />
        <x-ui.input name="slug" :value="old('slug')" label="اسلاگ (اختیاری)" placeholder="" />
        <x-ui.textarea name="description" label="توضیحات" rows="4">{{ old('description') }}</x-ui.textarea>
        <div class="grid grid-cols-2 gap-3">
            <x-ui.input type="number" name="price" :value="old('price')" label="قیمت (تومان)" placeholder="" required />
            <x-ui.input type="number" name="stock" :value="old('stock')" label="موجودی" placeholder="" required />
        </div>
        
        <div class="border-t border-white/10 pt-4">
            <h3 class="text-lg font-semibold mb-3">تنوع محصول</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <x-ui.checkbox name="has_variants" value="1" label="دارای تنوع" :checked="old('has_variants')" />
                <x-ui.checkbox name="has_colors" value="1" label="دارای رنگ‌بندی" :checked="old('has_colors')" />
                <x-ui.checkbox name="has_sizes" value="1" label="دارای سایزبندی" :checked="old('has_sizes')" />
            </div>
            <p class="text-sm text-gray-400 mt-2">
                💡 اگر محصول تنوع دارد، بعد از ایجاد محصول می‌توانید رنگ‌ها و سایزهای مختلف را اضافه کنید.
            </p>
        </div>
        
        <x-ui.checkbox name="is_active" value="1" label="فعال" :checked="old('is_active', true)" />
        <x-ui.file name="images[]" label="تصاویر محصول" :multiple="true" accept="image/*" />
        @error('images')
            <div class="text-xs text-rose-400">{{ $message }}</div>
        @enderror
        @error('images.*')
            <div class="text-xs text-rose-400">{{ $message }}</div>
        @enderror
        <x-ui.button type="submit">ذخیره</x-ui.button>
    </form>
</x-admin.layout>


