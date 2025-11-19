<x-admin.layout :title="'ایجاد دسته‌بندی'">
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.categories.index') }}" class="text-gray-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </a>
        <h1 class="text-xl font-bold">ایجاد دسته‌بندی</h1>
    </div>
    
    <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <x-ui.input 
                        name="name" 
                        label="نام دسته‌بندی" 
                        placeholder="نام دسته‌بندی را وارد کنید"
                        value="{{ old('name') }}"
                        required
                    />
                </div>
                
                <div>
                    <x-ui.input 
                        name="slug" 
                        label="Slug" 
                        placeholder="نامک (اختیاری)"
                        value="{{ old('slug') }}"
                    />
                    <p class="text-sm text-gray-400 mt-1">اگر خالی باشد، از نام دسته‌بندی تولید می‌شود</p>
                </div>
            </div>
            
            <div class="space-y-4">
                <div>
                    <x-ui.textarea 
                        name="description" 
                        label="توضیحات" 
                        placeholder="توضیحات دسته‌بندی"
                        rows="4"
                    >{{ old('description') }}</x-ui.textarea>
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
                ایجاد دسته‌بندی
            </button>
            <a href="{{ route('admin.categories.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white rounded px-6 py-2">
                انصراف
            </a>
        </div>
    </form>
</x-admin.layout>
