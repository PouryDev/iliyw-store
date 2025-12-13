<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'ایلی استور' }}</title>
        
        <!-- PWA Meta Tags -->
        <meta name="application-name" content="ایلی استور">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="ایلی استور">
        <meta name="description" content="فروشگاه آنلاین ایلی استور - اکسسوری و تابلو با بهترین قیمت">
        <meta name="format-detection" content="telephone=no">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="msapplication-config" content="/browserconfig.xml">
        <meta name="msapplication-TileColor" content="#2563eb">
        <meta name="msapplication-tap-highlight" content="no">
        <meta name="theme-color" content="#2563eb">
        
        <!-- Icons -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}">
        <link rel="apple-touch-icon" sizes="167x167" href="{{ asset('images/logo.png') }}">
        
        <!-- Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        
        <!-- Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((registration) => {
                            console.log('SW registered: ', registration);
                        })
                        .catch((registrationError) => {
                            console.log('SW registration failed: ', registrationError);
                        });
                });
            }
        </script>
        
        @if (file_exists(public_path('build/manifest.json')) || (app()->environment('local') && file_exists(public_path('hot'))))
            @vite(['resources/css/app.css','resources/js/app.js'])
        @endif
        
        <style>
            body { font-family: Vazirmatn, var(--font-sans); }
            .brand-grad { background: linear-gradient(135deg,#111827,#1f2937); }
            .brand-text { background: linear-gradient(135deg,#2563eb,#3b82f6); -webkit-background-clip: text; background-clip: text; color: transparent; }
            .glass { backdrop-filter: saturate(180%) blur(12px); background: rgba(255,255,255,0.6); }
            .no-scrollbar::-webkit-scrollbar{ display:none; }
            @keyframes fadeUp { from { opacity:0; transform: translateY(10px);} to { opacity:1; transform: none;} }
            .anim-fade-up { animation: fadeUp .5s ease-out both; }
            @keyframes slideDown { from { opacity:0; transform: translateY(-8px) scale(.98);} to { opacity:1; transform: none;} }
            .anim-slide-down { animation: slideDown .2s ease-out both; }
            /* Modern number input: hide spinners (WebKit) */
            input[type=number]::-webkit-outer-spin-button,
            input[type=number]::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
            /* Firefox */
            input[type=number] { -moz-appearance: textfield; appearance: textfield; }
        </style>
    </head>
    <body class="min-h-screen bg-[#0b0b0f] text-white">
        <header class="sticky top-0 z-40 brand-grad/90 shadow">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between relative">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Iliywstore" class="h-10 w-auto">
                    <span class="text-xl font-extrabold tracking-tight">
                        <span class="brand-text">ایلی</span><span class="text-white">استور</span>
                    </span>
                </a>
                <nav class="hidden md:flex items-center gap-6 text-sm text-gray-200">
                    <a href="/" class="hover:text-white transition">خانه</a>
                    <a href="{{ route('checkout.index') }}" class="hover:text-white transition">تسویه حساب</a>
                    <div class="relative">
                        <button id="cartHoverBtn" class="relative hover:text-white transition">
                            <span class="sr-only">سبد خرید</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5"><path fill="currentColor" d="M8 22q-.825 0-1.412-.587T6 20q0-.825.588-1.412T8 18q.825 0 1.413.588T10 20q0 .825-.587 1.413T8 22m8 0q-.825 0-1.412-.587T14 20q0-.825.588-1.412T16 18q.825 0 1.413.588T18 20q0 .825-.587 1.413T16 22M6.2 6l2.8 6h6.825q.15 0 .275-.075t.2-.2l2.8-5.05q.125-.225.012-.45T18.7 6zM5.225 4h13.65q.575 0 .863.462t.037.963l-3.05 5.6q-.275.5-.763.787T14.8 12H8.45l-1.1 2H18v2H8q-.775 0-1.15-.662T6.9 14.8l.35-.8L3 4H1V2h3.05z"/></svg>
                            @php $count = array_sum(session('cart', [])); @endphp
                            <span data-cart-count class="absolute -top-2 -left-3 text-[10px] bg-cherry-600 rounded-full px-1.5 py-0.5 {{ $count ? '' : 'hidden' }}">{{ $count ?: '' }}</span>
                        </button>
                        <div id="cartDropdown" class="hidden absolute left-1/2 -translate-x-1/2 mt-2 w-72 max-h-80 overflow-auto rounded-xl border border-white/10 bg-[#0d0d14]/95 shadow-2xl">
                            <div id="cartDropdownList" class="py-2"></div>
                            <div class="p-2 border-t border-white/10 text-right">
                                <a href="{{ route('cart.index') }}" class="text-xs text-cherry-400 hover:text-cherry-300">مشاهده سبد</a>
                            </div>
                        </div>
                    </div>
                    @auth
                        <a href="{{ route('account.orders') }}" class="hover:text-white">سفارش‌ها</a>
                        <a href="{{ route('account.index') }}" class="hover:text-white">داشبورد</a>
                        <form method="post" action="{{ route('auth.logout') }}">
                            @csrf
                            <button class="text-gray-200 hover:text-white">خروج</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-white">ورود</a>
                        <a href="{{ route('register') }}" class="bg-cherry-600 hover:bg-cherry-700 text-white rounded px-3 py-1">ثبت‌نام</a>
                    @endauth
                </nav>

                <button id="mobileMenuBtn" class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded border border-white/15 text-white/90 hover:text-white hover:border-white/30" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                
            </div>
        </header>
        <main class="max-w-6xl mx-auto px-4 py-6">
            {{ $slot }}
            @stack('scripts')
        </main>
        <footer class="border-t border-white/10">
            <div class="max-w-6xl mx-auto px-4 py-6 text-xs text-gray-400 flex items-center justify-between">
                <div>© {{ date('Y') }} ایلی استور</div>
                <div class="space-x-3 space-x-reverse">
                    <a href="/" class="hover:text-white">قوانین</a>
                    <a href="/" class="hover:text-white">حریم خصوصی</a>
                </div>
            </div>
        </footer>

        <!-- Mobile sidebar (separate from header) -->
        <div id="mobileMenu" class="md:hidden hidden fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] h-[100dvh] overflow-y-auto border-r border-white/10 bg-[#0d0d14]/95 shadow-2xl transform -translate-x-full transition-transform duration-300 ease-out will-change-transform">
            <div class="flex items-center justify-between h-14 px-4 border-b border-white/10">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Iliywstore" class="h-8 w-auto">
                    <span class="text-lg font-extrabold tracking-tight">
                        <span class="brand-text">ایلی</span><span class="text-white">استور</span>
                    </span>
                </a>
                <button id="mobileCloseBtn" class="inline-flex items-center justify-center w-9 h-9 rounded border border-white/15 text-white/90 hover:text-white hover:border-white/30" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="p-3 text-sm text-gray-200">
                <div class="space-y-1">
                    <a href="/" class="block px-3 py-2 rounded hover:bg-white/5">خانه</a>
                    <a href="{{ route('cart.index') }}" class="block px-3 py-2 rounded hover:bg-white/5">سبد خرید</a>
                    <a href="{{ route('checkout.index') }}" class="block px-3 py-2 rounded hover:bg-white/5">تسویه حساب</a>
                </div>
                <div class="mt-4 pt-4 border-t border-white/10 space-y-1">
                    @auth
                        <a href="/account/orders" class="block px-3 py-2 rounded hover:bg-white/5">سفارش‌های من</a>
                        <a href="/account" class="block px-3 py-2 rounded hover:bg-white/5">داشبورد</a>
                        <form method="post" action="{{ route('auth.logout') }}" class="px-3 py-2">
                            @csrf
                            <button class="w-full text-left rounded hover:bg-white/5">خروج</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block px-3 py-2 rounded hover:bg-white/5">ورود</a>
                        <a href="{{ route('register') }}" class="block px-3 py-2 rounded bg-cherry-600/10 text-white hover:bg-cherry-600/20">ثبت‌نام</a>
                    @endauth
                </div>
            </nav>
        </div>
        <div id="mobileBackdrop" class="md:hidden hidden fixed inset-0 z-40 bg-black/40 opacity-0 transition-opacity duration-300 ease-out pointer-events-none"></div>
        <script>
            (function(){
                const btn = document.getElementById('mobileMenuBtn');
                const menu = document.getElementById('mobileMenu');
                const backdrop = document.getElementById('mobileBackdrop');
                if(!btn || !menu) return;
                function openMenu(){
                    // Ensure start state is applied and browser registers it before transition
                    menu.classList.remove('hidden');
                    // force reflow to commit current styles before removing translate class
                    void menu.offsetWidth;
                    menu.classList.remove('-translate-x-full');
                    if (backdrop){
                        backdrop.classList.remove('hidden');
                        void backdrop.offsetWidth;
                        backdrop.classList.remove('opacity-0');
                        backdrop.classList.remove('pointer-events-none');
                    }
                    document.body.style.overflow = 'hidden';
                }
                function closeMenu(){
                    menu.classList.add('-translate-x-full');
                    setTimeout(()=>{ menu.classList.add('hidden'); }, 300);
                    if (backdrop){
                        backdrop.classList.add('opacity-0');
                        backdrop.classList.add('pointer-events-none');
                        setTimeout(()=>{ backdrop.classList.add('hidden'); }, 300);
                    }
                    document.body.style.overflow = '';
                }

                btn.addEventListener('click', function(e){
                    e.stopPropagation();
                    const isHidden = menu.classList.contains('hidden');
                    if (isHidden) openMenu(); else closeMenu();
                });
                const closeBtn = document.getElementById('mobileCloseBtn');
                if (closeBtn) closeBtn.addEventListener('click', function(e){ e.stopPropagation(); closeMenu(); });
                document.addEventListener('click', function(e){
                    if(!menu.classList.contains('hidden')){
                        const within = menu.contains(e.target) || btn.contains(e.target);
                        if(!within){ closeMenu(); }
                    }
                });
                window.addEventListener('resize', function(){
                    if(window.innerWidth >= 768){
                        closeMenu();
                    }
                });
            })();
        </script>
        <script>
            (function(){
                const btn = document.getElementById('cartHoverBtn');
                const dd = document.getElementById('cartDropdown');
                if(!btn || !dd) return;
                let hoverTimer;
                function open(){ dd.classList.remove('hidden'); }
                function close(){ dd.classList.add('hidden'); }
                btn.addEventListener('mouseenter', ()=>{ clearTimeout(hoverTimer); open(); });
                btn.addEventListener('mouseleave', ()=>{ hoverTimer = setTimeout(close, 200); });
                dd.addEventListener('mouseenter', ()=>{ clearTimeout(hoverTimer); });
                dd.addEventListener('mouseleave', ()=>{ hoverTimer = setTimeout(close, 200); });
            })();
        </script>
    </body>
 </html>


