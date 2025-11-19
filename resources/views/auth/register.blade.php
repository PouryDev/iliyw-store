<x-layouts.app :title="'ثبت‌نام'">
    <div class="max-w-md mx-auto">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6">
            <h1 class="text-xl font-bold mb-4">ثبت‌نام</h1>
            
            @if ($errors->any())
                <div class="rounded-lg border border-rose-500/50 bg-rose-500/10 p-3 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-rose-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                            <ul class="text-xs text-rose-300 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <form method="post" action="{{ route('auth.register') }}" class="space-y-3">
                @csrf
                <x-ui.input name="name" :value="old('name')" label="نام و نام خانوادگی" placeholder="علی احمدی" required />
                <x-ui.input name="instagram_id" :value="old('instagram_id')" label="آیدی اینستاگرام" placeholder="@username" />
                <x-ui.input name="phone" type="tel" :value="old('phone')" label="شماره تلفن" placeholder="09123456789" required />
                <x-ui.input name="password" type="password" label="رمز عبور" placeholder="حداقل ۶ کاراکتر" required />
                <x-ui.input name="password_confirmation" type="password" label="تکرار رمز عبور" placeholder="" required />
                <x-ui.button type="submit" class="w-full">ثبت‌نام</x-ui.button>
            </form>
            <div class="text-xs text-gray-300 mt-3">حساب دارید؟ <a class="text-cherry-400" href="{{ route('login') }}">ورود</a></div>
        </div>
    </div>
</x-layouts.app>


