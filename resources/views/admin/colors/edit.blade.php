<x-admin.layout :title="'ویرایش رنگ'">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.colors.index') }}" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-xl font-bold">ویرایش رنگ</h1>
    </div>
    
    <form method="POST" action="{{ route('admin.colors.update', $color) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <x-ui.input 
                        name="name" 
                        label="نام رنگ" 
                        placeholder="نام رنگ را وارد کنید"
                        value="{{ old('name', $color->name) }}"
                        required
                    />
                </div>
                
                <div>
                    <x-ui.input 
                        name="hex_code" 
                        label="کد HEX (اختیاری)" 
                        placeholder="#FF0000"
                        value="{{ old('hex_code', $color->hex_code) }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">کد رنگ hex مثل #FF0000 برای قرمز</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $color->is_active) ? 'checked' : '' }} class="rounded border-gray-600 bg-gray-700 text-cherry-600 focus:ring-cherry-500 focus:ring-offset-gray-800">
                    <label for="is_active" class="mr-2 text-sm text-gray-300">فعال</label>
                </div>
                
                @if($color->hex_code)
                    <div class="p-4 bg-white/5 rounded-lg">
                        <h3 class="text-sm font-semibold mb-2">پیش‌نمایش رنگ:</h3>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full border border-white/20" style="background-color: {{ $color->hex_code }}"></div>
                            <div class="text-sm text-gray-300">{{ $color->name }}</div>
                        </div>
                    </div>
                @endif
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
                به‌روزرسانی رنگ
            </button>
            <a href="{{ route('admin.colors.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white rounded px-6 py-2">
                انصراف
            </a>
        </div>
    </form>
</x-admin.layout>
