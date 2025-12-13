<x-admin.layout :title="'ایجاد تنوع محصول'">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.product-variants.index', ['product_id' => $product->id]) }}" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold">ایجاد تنوع محصول</h1>
            <p class="text-sm text-gray-400">{{ $product->title }}</p>
        </div>
    </div>
    
    <form method="POST" action="{{ route('admin.product-variants.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <x-ui.select name="color_id" label="رنگ (اختیاری)">
                        <option value="">بدون رنگ</option>
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}" {{ old('color_id') == $color->id ? 'selected' : '' }}>
                                {{ $color->name }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>
                
                <div>
                    <x-ui.select name="size_id" label="سایز (اختیاری)">
                        <option value="">بدون سایز</option>
                        @foreach($sizes as $size)
                            <option value="{{ $size->id }}" {{ old('size_id') == $size->id ? 'selected' : '' }}>
                                {{ $size->display }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <x-ui.input 
                        name="sku" 
                        label="SKU (اختیاری)" 
                        placeholder="کد محصول منحصر به فرد"
                        value="{{ old('sku') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">اگر خالی بگذارید، خودکار تولید می‌شود</p>
                </div>
                
                <div>
                    <x-ui.input 
                        type="number"
                        name="stock" 
                        label="موجودی" 
                        placeholder="0"
                        value="{{ old('stock', 0) }}"
                        required
                        min="0"
                    />
                </div>
                
                <div>
                    <x-ui.input 
                        type="number"
                        name="price" 
                        label="قیمت مخصوص (اختیاری)" 
                        placeholder="قیمت محصول: {{ number_format($product->price) }} تومان"
                        value="{{ old('price') }}"
                        min="0"
                    />
                    <p class="text-sm text-gray-400 mt-1">اگر خالی بگذارید، از قیمت محصول استفاده می‌شود</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="rounded border-gray-600 bg-gray-700 text-cherry-600 focus:ring-cherry-500 focus:ring-offset-gray-800">
                    <label for="is_active" class="mr-2 text-sm text-gray-300">فعال</label>
                </div>
            </div>
        </div>
        
        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                <ul class="text-red-300 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="flex gap-3">
            <button type="submit" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-6 py-2">
                ایجاد تنوع
            </button>
            <a href="{{ route('admin.product-variants.index', ['product_id' => $product->id]) }}" class="bg-gray-600 hover:bg-gray-700 text-white rounded px-6 py-2">
                انصراف
            </a>
        </div>
    </form>
</x-admin.layout>
