<x-admin.layout :title="'ایجاد کمپین'">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.campaigns.index') }}" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-xl font-bold">ایجاد کمپین</h1>
    </div>
    
    <form method="POST" action="{{ route('admin.campaigns.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <x-ui.input 
                        name="name" 
                        label="نام کمپین" 
                        placeholder="نام کمپین را وارد کنید"
                        value="{{ old('name') }}"
                        required
                    />
                </div>
                
                <div>
                    <x-ui.textarea 
                        name="description" 
                        label="توضیحات (اختیاری)" 
                        rows="3"
                        placeholder="توضیحات کمپین"
                    >{{ old('description') }}</x-ui.textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-ui.select name="type" label="نوع تخفیف" required>
                            <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>درصدی</option>
                            <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                        </x-ui.select>
                    </div>
                    
                    <div>
                        <x-ui.input 
                            type="number"
                            name="discount_value" 
                            label="مقدار تخفیف" 
                            placeholder="مقدار تخفیف"
                            value="{{ old('discount_value') }}"
                            required
                            min="1"
                        />
                    </div>
                </div>
                
                <div>
                    <x-ui.input 
                        type="number"
                        name="max_discount_amount" 
                        label="حداکثر مبلغ تخفیف (اختیاری)" 
                        placeholder="برای تخفیف درصدی"
                        value="{{ old('max_discount_amount') }}"
                        min="0"
                    />
                    <p class="text-sm text-gray-400 mt-1">فقط برای تخفیف درصدی استفاده می‌شود</p>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-ui.input 
                            type="datetime-local"
                            name="starts_at" 
                            label="تاریخ و ساعت شروع" 
                            value="{{ old('starts_at') }}"
                            required
                        />
                    </div>
                    
                    <div>
                        <x-ui.input 
                            type="datetime-local"
                            name="ends_at" 
                            label="تاریخ و ساعت پایان" 
                            value="{{ old('ends_at') }}"
                            required
                        />
                    </div>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <x-ui.input 
                        type="number"
                        name="priority" 
                        label="اولویت" 
                        placeholder="0"
                        value="{{ old('priority', 0) }}"
                        min="0"
                        max="999"
                    />
                    <p class="text-sm text-gray-400 mt-1">بالاتر = مهم‌تر (در صورت تداخل کمپین‌ها)</p>
                </div>
                
                <div>
                    <x-ui.input 
                        name="badge_text" 
                        label="متن نشان (اختیاری)" 
                        placeholder="مثل: فروش فوق‌العاده"
                        value="{{ old('badge_text') }}"
                        maxlength="50"
                    />
                </div>
                
                <div>
                    <x-ui.file 
                        name="banner_image" 
                        label="تصویر بنر (اختیاری)" 
                        accept="image/*"
                    />
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-cherry-600 focus:ring-cherry-500 focus:ring-offset-gray-800">
                    <label for="is_active" class="mr-2 text-sm text-gray-300">فعال</label>
                </div>
            </div>
        </div>
        
        <div class="space-y-4">
            <h3 class="text-lg font-semibold">اهداف کمپین</h3>
            <p class="text-sm text-gray-400">محصولات یا دسته‌بندی‌هایی که این کمپین روی آن‌ها اعمال می‌شود</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium mb-3">محصولات</h4>
                    <div class="max-h-48 overflow-y-auto space-y-2">
                        @foreach($products as $product)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="targets[]" value="product:{{ $product->id }}" {{ in_array("product:{$product->id}", old('targets', [])) ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-cherry-600 focus:ring-cherry-500">
                                <span>{{ $product->title }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium mb-3">دسته‌بندی‌ها</h4>
                    <div class="max-h-48 overflow-y-auto space-y-2">
                        @foreach($categories as $category)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="targets[]" value="category:{{ $category->id }}" {{ in_array("category:{$category->id}", old('targets', [])) ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-cherry-600 focus:ring-cherry-500">
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
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
                ایجاد کمپین
            </button>
            <a href="{{ route('admin.campaigns.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white rounded px-6 py-2">
                انصراف
            </a>
        </div>
    </form>
</x-admin.layout>
