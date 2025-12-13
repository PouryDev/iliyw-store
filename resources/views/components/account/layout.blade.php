<x-layouts.app :title="$title ?? 'پنل کاربری'">
    <!-- Mobile tabs -->
    <div class="lg:hidden -mx-4 px-4 mb-4">
        <div class="flex gap-2 overflow-x-auto overflow-y-hidden no-scrollbar">
            <a href="{{ route('account.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm border {{ request()->routeIs('account.index') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">🏠 داشبورد</a>
            <a href="{{ route('cart.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm border {{ (request()->routeIs('cart.*') || request()->is('cart')) ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">🛒 سبد خرید</a>
            <a href="{{ route('account.orders') }}" class="shrink-0 rounded-full px-4 py-2 text-sm border {{ (request()->routeIs('account.orders') || request()->routeIs('account.orders.show')) ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">📦 سفارش‌ها</a>
            <a href="{{ route('account.settings') }}" class="shrink-0 rounded-full px-4 py-2 text-sm border {{ request()->routeIs('account.settings') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">⚙️ تنظیمات</a>
            @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="shrink-0 rounded-full px-4 py-2 text-sm bg-gradient-to-br from-cherry-600 to-fuchsia-600">🛠️ ادمین</a>
            @endif
        </div>
    </div>

    <!-- Mobile content area -->
    <div class="lg:hidden">
        <section class="min-w-0">
            {{ $slot }}
        </section>
    </div>

    <div class="hidden lg:flex gap-6">
        <aside class="w-64 shrink-0 sticky top-20 self-start">
            <nav class="space-y-2">
                <a href="{{ route('account.index') }}" class="block rounded-lg border px-4 py-2 transition {{ request()->routeIs('account.index') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">🏠 داشبورد</a>
                <a href="{{ route('cart.index') }}" class="block rounded-lg border px-4 py-2 transition {{ (request()->routeIs('cart.*') || request()->is('cart')) ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">🛒 سبد خرید</a>
                <a href="{{ route('account.orders') }}" class="block rounded-lg border px-4 py-2 transition {{ (request()->routeIs('account.orders') || request()->routeIs('account.orders.show')) ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">📦 سفارش‌های من</a>
                <a href="{{ route('account.settings') }}" class="block rounded-lg border px-4 py-2 transition {{ request()->routeIs('account.settings') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">⚙️ تنظیمات</a>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-4 py-2 bg-gradient-to-br from-cherry-600 to-fuchsia-600">🛠️ پنل ادمین</a>
                @endif
            </nav>
        </aside>
        <section class="flex-1 min-w-0">
            {{ $slot }}
        </section>
    </div>
</x-layouts.app>


