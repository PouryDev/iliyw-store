<x-layouts.app :title="$title ?? 'پنل کاربری'">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <aside class="md:col-span-1 space-y-2">
            <a href="{{ route('account.index') }}" class="block rounded-lg px-4 py-2 {{ request()->routeIs('account.index') ? 'border border-cherry-600 bg-white/10 text-white' : 'border border-white/10 bg-white/5 hover:border-cherry-600' }}">داشبورد</a>
            <a href="{{ route('cart.index') }}" class="block rounded-lg px-4 py-2 {{ request()->routeIs('cart.index') ? 'border border-cherry-600 bg-white/10 text-white' : 'border border-white/10 bg-white/5 hover:border-cherry-600' }}">سبد خرید</a>
            <a href="{{ route('account.orders') }}" class="block rounded-lg px-4 py-2 {{ request()->routeIs('account.orders') || request()->routeIs('account.orders.show') ? 'border border-cherry-600 bg-white/10 text-white' : 'border border-white/10 bg-white/5 hover:border-cherry-600' }}">سفارش‌های من</a>
            <a href="{{ route('account.settings') }}" class="block rounded-lg px-4 py-2 {{ request()->routeIs('account.settings') ? 'border border-cherry-600 bg-white/10 text-white' : 'border border-white/10 bg-white/5 hover:border-cherry-600' }}">تنظیمات</a>
            @if(auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="block rounded-lg bg-gradient-to-br from-cherry-600 to-fuchsia-600 px-4 py-2">پنل ادمین</a>
            @endif
        </aside>
        <section class="md:col-span-3">
            {{ $slot }}
        </section>
    </div>
</x-layouts.app>


