<x-layouts.app :title="$title ?? 'پنل ادمین'">
    <div class="hidden lg:flex gap-6">
        <aside class="w-64 shrink-0 sticky top-20 self-start">
            <nav class="space-y-2 text-sm">
                <a href="{{ route('admin.products.index') }}" class="block rounded-lg border px-4 py-2 {{ request()->routeIs('admin.products.*') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">📦 محصولات</a>
                <a href="{{ route('admin.orders.index') }}" class="block rounded-lg border px-4 py-2 {{ request()->routeIs('admin.orders.*') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">🧾 سفارش‌ها</a>
                <a href="/" class="block rounded-lg border px-4 py-2 border-white/10 bg-white/5 hover:border-cherry-600">🏪 فروشگاه</a>
            </nav>
        </aside>
        <section class="flex-1 min-w-0">
            {{ $slot }}
        </section>
    </div>

    <div class="lg:hidden space-y-3 -mx-4 px-4 mb-4">
        <div class="flex gap-2 overflow-x-auto overflow-y-hidden no-scrollbar">
            <a href="{{ route('admin.products.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm border {{ request()->routeIs('admin.products.*') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">📦 محصولات</a>
            <a href="{{ route('admin.orders.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm border {{ request()->routeIs('admin.orders.*') ? 'border-cherry-600 bg-cherry-600/10' : 'border-white/10 bg-white/5 hover:border-cherry-600' }}">🧾 سفارش‌ها</a>
            <a href="/" class="shrink-0 rounded-full px-4 py-2 text-sm border border-white/10 bg-white/5 hover:border-cherry-600">🏪 فروشگاه</a>
        </div>
        <section>
            {{ $slot }}
        </section>
    </div>
</x-layouts.app>


