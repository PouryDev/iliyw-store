<x-account.layout title="تنظیمات حساب">
    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6">
            <h2 class="font-bold mb-3">اطلاعات پروفایل</h2>
            
            @if ($errors->has('name') || $errors->has('phone') || $errors->has('address'))
                <div class="rounded-lg border border-rose-500/50 bg-rose-500/10 p-3 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <ul class="text-xs text-rose-300 space-y-1">
                                @error('name')<li>{{ $message }}</li>@enderror
                                @error('phone')<li>{{ $message }}</li>@enderror
                                @error('address')<li>{{ $message }}</li>@enderror
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <form method="post" action="{{ route('account.profile.update') }}" class="space-y-3">
                @csrf
                <x-ui.input name="name" :value="old('name',$user->name)" label="نام" placeholder="نام" required />
                <x-ui.input name="instagram_id" :value="$user->instagram_id" label="آیدی اینستاگرام" disabled readonly class="opacity-50 cursor-not-allowed" />
                <x-ui.input name="phone" :value="$user->phone" label="شماره تماس" disabled readonly class="opacity-50 cursor-not-allowed" />
                <x-ui.textarea name="address" label="آدرس" rows="4" placeholder="آدرس کامل">{{ old('address',$user->address) }}</x-ui.textarea>
                @if(session('status'))
                    <div class="text-sm text-green-400">{{ session('status') }}</div>
                @endif
                <button class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">ذخیره</button>
            </form>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-6">
            <h2 class="font-bold mb-3">تغییر رمز عبور</h2>
            
            @if ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))
                <div class="rounded-lg border border-rose-500/50 bg-rose-500/10 p-3 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <ul class="text-xs text-rose-300 space-y-1">
                                @error('current_password')<li>{{ $message }}</li>@enderror
                                @error('password')<li>{{ $message }}</li>@enderror
                                @error('password_confirmation')<li>{{ $message }}</li>@enderror
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <form method="post" action="{{ route('account.password.update') }}" class="space-y-3">
                @csrf
                <x-ui.input type="password" name="current_password" label="رمز فعلی" placeholder="" required />
                <x-ui.input type="password" name="password" label="رمز جدید" placeholder="حداقل ۶ کاراکتر" required />
                <x-ui.input type="password" name="password_confirmation" label="تکرار رمز جدید" placeholder="" required />
                @if(session('status'))
                    <div class="text-sm text-green-400">{{ session('status') }}</div>
                @endif
                <button class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-4 py-2">تغییر رمز</button>
            </form>
        </div>
    </div>
</x-account.layout>


