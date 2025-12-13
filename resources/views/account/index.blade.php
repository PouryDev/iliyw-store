<x-account.layout title="پنل کاربری">
    <div class="space-y-6">
        <div class="rounded-xl border border-white/10 bg-white/5 p-6">
            <h1 class="text-2xl font-extrabold mb-2">سلام، {{ $user->name }}</h1>
            <div class="text-gray-300 text-sm">{{ $user->email }}</div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <a href="{{ route('cart.index') }}" class="rounded-lg border border-white/10 bg-white/5 p-4 hover:border-cherry-600 transition">
                <div class="font-semibold mb-1">سبد خرید</div>
                <div class="text-xs text-gray-300">مشاهده سبد و ادامه خرید</div>
            </a>

            <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                <div class="font-semibold mb-3">آخرین سفارش‌ها</div>
                @forelse($recentOrders as $o)
                    <div class="flex items-center justify-between text-sm border-b border-white/10 py-2">
                        <div>#{{ $o->id }}</div>
                        <div class="text-gray-300">{{ number_format($o->total_amount) }}</div>
                        <div class="text-cherry-400">{{ $o->status }}</div>
                    </div>
                @empty
                    <div class="text-xs text-gray-300">سفارشی ثبت نشده است.</div>
                @endforelse
            </div>

            @if($user->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-white/10 bg-gradient-to-br from-cherry-600 to-fuchsia-600 p-4 hover:shadow-lg transition">
                    <div class="font-semibold mb-1">ورود سریع به پنل ادمین</div>
                    <div class="text-xs">مدیریت محصولات و سفارش‌ها</div>
                </a>
            @endif
        </div>

        <form method="post" action="{{ route('auth.logout') }}" class="text-left">
            @csrf
            <button class="text-sm text-gray-300 hover:text-white">خروج از حساب</button>
        </form>
    </div>
</x-account.layout>


