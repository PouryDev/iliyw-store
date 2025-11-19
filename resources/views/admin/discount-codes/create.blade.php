<x-admin.layout :title="'ایجاد کد تخفیف'">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.discount-codes.index') }}" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-xl font-bold">ایجاد کد تخفیف</h1>
    </div>
    
    <form method="POST" action="{{ route('admin.discount-codes.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <x-ui.input 
                        name="code" 
                        label="کد تخفیف" 
                        placeholder="کد تخفیف را وارد کنید"
                        value="{{ old('code') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">اگر خالی باشد، کد به صورت خودکار تولید می‌شود</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="auto_generate" id="auto_generate" value="1" class="rounded border-gray-600 bg-gray-700 text-cherry-600 focus:ring-cherry-500 focus:ring-offset-gray-800">
                    <label for="auto_generate" class="mr-2 text-sm text-gray-300">تولید خودکار کد</label>
                </div>
                
                <div>
                    <x-ui.select name="type" label="نوع تخفیف" required>
                        <option value="">انتخاب کنید</option>
                        <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>درصدی</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>مبلغ ثابت</option>
                    </x-ui.select>
                </div>
                
                <div>
                    <x-ui.input 
                        name="value" 
                        type="number" 
                        label="مقدار تخفیف" 
                        placeholder="مقدار تخفیف"
                        value="{{ old('value') }}"
                        required
                    />
                    <p class="text-sm text-gray-400 mt-1">برای درصدی: 1-100، برای مبلغ ثابت: مقدار به تومان</p>
                </div>
                
                <div>
                    <x-ui.input 
                        name="usage_limit" 
                        type="number" 
                        label="محدودیت تعداد استفاده" 
                        placeholder="محدودیت تعداد استفاده"
                        value="{{ old('usage_limit') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">اگر خالی باشد، استفاده نامحدود است</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <x-ui.input 
                        name="max_discount_amount" 
                        type="number" 
                        label="حداکثر مبلغ تخفیف (تومان)" 
                        placeholder="حداکثر مبلغ تخفیف"
                        value="{{ old('max_discount_amount') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">حداکثر مبلغ تخفیفی که اعمال می‌شود</p>
                </div>
                
                <div>
                    <x-ui.input 
                        name="min_order_amount" 
                        type="number" 
                        label="حداقل مبلغ سفارش (تومان)" 
                        placeholder="حداقل مبلغ سفارش"
                        value="{{ old('min_order_amount') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">حداقل مبلغ سفارش برای اعمال تخفیف</p>
                </div>
                
                <div>
                    <x-ui.input 
                        name="starts_at" 
                        type="datetime-local" 
                        label="تاریخ شروع" 
                        value="{{ old('starts_at') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">اگر خالی باشد، از همین الان فعال است</p>
                </div>
                
                <div>
                    <x-ui.input 
                        name="expires_at" 
                        type="datetime-local" 
                        label="تاریخ انقضا" 
                        value="{{ old('expires_at') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">اگر خالی باشد، منقضی نمی‌شود</p>
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
                ایجاد کد تخفیف
            </button>
            <a href="{{ route('admin.discount-codes.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white rounded px-6 py-2">
                انصراف
            </a>
        </div>
    </form>
</x-admin.layout>
