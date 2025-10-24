import React from 'react';
import { useSeo } from '../hooks/useSeo';

function AboutPage(){
    useSeo({
        title: 'درباره ما - فروشگاه iliyw',
        description: 'با فروشگاه آنلاین تابلوهای هنری با کیفیت بالا iliyw آشنا شوید. داستان شروع، ارزش‌ها و مجموعه تابلوهای موزیکال ما را بخوانید.',
        keywords: 'درباره iliyw, فروشگاه تابلو, تابلوهای هنری, تابلو موزیکال, خرید آنلاین',
        image: '/images/logo.png',
        canonical: window.location.origin + '/about'
    });

    React.useEffect(() => {
        const structuredData = {
            '@context': 'https://schema.org',
            '@type': 'Organization',
            'name': 'iliyw',
            'url': window.location.origin,
            'logo': window.location.origin + '/images/logo.png',
            'sameAs': [
                'https://instagram.com/iliywstore'
            ]
        };
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(structuredData);
        document.head.appendChild(script);
        return () => { try { document.head.removeChild(script); } catch {} };
    }, []);

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white px-4 py-6">
            <div className="max-w-7xl mx-auto">
                {/* Hero */}
                <section className="rounded-2xl glass-card elegant-shadow border border-amber-500/20 p-8 md:p-10 mb-8">
                    <div className="flex items-start md:items-center justify-between gap-6 flex-col md:flex-row">
                        <div className="min-w-0">
                            <h1 className="text-3xl md:text-4xl font-extrabold bg-gradient-to-l from-amber-400 to-amber-200 bg-clip-text text-transparent">درباره iliyw</h1>
                            <p className="text-gray-300 text-base mt-4 leading-8">
                                ما یک گالری آنلاین تابلوهای هنری با تمرکز روی اصالت، زیبایی و کیفیت هستیم. هدف ما این است که هر فضا را به یک گالری خصوصی تبدیل کنیم؛ جایی که هنر و موسیقی در کنار هم زندگی می‌کنند.
                            </p>
                        </div>
                        <img src="/images/logo.png" alt="iliyw" className="w-20 h-20 rounded-xl border-2 border-amber-500/30 shadow-lg shadow-amber-500/20" />
                    </div>
                </section>

                {/* Story */}
                <section className="rounded-2xl glass-card elegant-shadow border border-indigo-500/20 p-8 md:p-10 mb-8">
                    <h2 className="text-2xl font-bold mb-4 text-amber-200">از عشق به هنر تا دیوارهای خانه شما</h2>
                    <p className="text-gray-300 text-base leading-8 mb-6">
                        شروع iliyw از یک اعتقاد ساده بود: هنر باید در دسترس همه باشد و هر فضایی می‌تواند با یک تابلو زیبا متحول شود. کم‌کم همین ایده تبدیل شد به مجموعه‌ای از تابلوهای هنری، تابلوهای موزیکال و آثار منتخب که هر کدام داستانی برای گفتن دارند.
                    </p>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 text-center">
                        <div className="rounded-2xl bg-gradient-to-br from-amber-500/10 to-indigo-500/10 border border-amber-500/30 p-5 backdrop-blur">
                            <div className="text-amber-400 font-extrabold text-2xl">+500</div>
                            <div className="text-gray-300 text-sm mt-1">هنردوست راضی</div>
                        </div>
                        <div className="rounded-2xl bg-gradient-to-br from-amber-500/10 to-indigo-500/10 border border-amber-500/30 p-5 backdrop-blur">
                            <div className="text-amber-400 font-extrabold text-2xl">72h</div>
                            <div className="text-gray-300 text-sm mt-1">ارسال سریع</div>
                        </div>
                        <div className="rounded-2xl bg-gradient-to-br from-amber-500/10 to-indigo-500/10 border border-amber-500/30 p-5 backdrop-blur">
                            <div className="text-amber-400 font-extrabold text-2xl">100%</div>
                            <div className="text-gray-300 text-sm mt-1">اصالت تضمین</div>
                        </div>
                        <div className="rounded-2xl bg-gradient-to-br from-amber-500/10 to-indigo-500/10 border border-amber-500/30 p-5 backdrop-blur">
                            <div className="text-amber-400 font-extrabold text-2xl">✓</div>
                            <div className="text-gray-300 text-sm mt-1">کیفیت بالا</div>
                        </div>
                    </div>
                </section>

                {/* What we value */}
                <section className="rounded-2xl glass-card elegant-shadow border border-indigo-500/20 p-8 md:p-10 mb-8">
                    <h2 className="text-2xl font-bold mb-6 text-indigo-300">چرا iliyw؟</h2>
                    <ul className="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
                        <li className="rounded-2xl bg-gradient-to-br from-amber-500/5 to-indigo-500/5 border border-amber-500/20 p-6 backdrop-blur hover:border-amber-500/40 transition-all duration-300">
                            <div className="font-bold mb-2 text-amber-300 text-base">چاپ با کیفیت موزه‌ای</div>
                            <p className="text-gray-300 leading-7">از انتخاب کاغذ تا فرآیند چاپ، همه‌چیز با بالاترین استانداردها انجام می‌شود تا رنگ‌ها زنده بمانند و تابلو سال‌ها دوام بیاورد.</p>
                        </li>
                        <li className="rounded-2xl bg-gradient-to-br from-amber-500/5 to-indigo-500/5 border border-amber-500/20 p-6 backdrop-blur hover:border-amber-500/40 transition-all duration-300">
                            <div className="font-bold mb-2 text-amber-300 text-base">تابلوهای موزیکال منحصربه‌فرد</div>
                            <p className="text-gray-300 leading-7">مجموعه‌ای خاص از تابلوهای همراه با موسیقی که تجربه هنری چندحسی ایجاد می‌کنند و فضای شما را به گالری خصوصی تبدیل می‌کنند.</p>
                        </li>
                        <li className="rounded-2xl bg-gradient-to-br from-amber-500/5 to-indigo-500/5 border border-amber-500/20 p-6 backdrop-blur hover:border-amber-500/40 transition-all duration-300">
                            <div className="font-bold mb-2 text-amber-300 text-base">قاب‌بندی حرفه‌ای</div>
                            <p className="text-gray-300 leading-7">انتخاب قاب‌های متنوع و باکیفیت که زیبایی اثر را دوچندان می‌کنند. تمام تابلوها آماده نصب و با تجهیزات کامل ارسال می‌شوند.</p>
                        </li>
                        <li className="rounded-2xl bg-gradient-to-br from-amber-500/5 to-indigo-500/5 border border-amber-500/20 p-6 backdrop-blur hover:border-amber-500/40 transition-all duration-300">
                            <div className="font-bold mb-2 text-amber-300 text-base">خرید اطمینان‌بخش</div>
                            <p className="text-gray-300 leading-7">تضمین اصالت، ارسال ایمن با بسته‌بندی حرفه‌ای و پشتیبانی واقعی. اگر چیزی مطابق انتظار نبود، راحت مرجوع کنید.</p>
                        </li>
                    </ul>
                </section>

                {/* FAQ mini */}
                <section className="rounded-2xl glass-card elegant-shadow border border-amber-500/20 p-8 md:p-10">
                    <h2 className="text-2xl font-bold mb-6 text-amber-200">سوالات پرتکرار</h2>
                    <div className="space-y-4 text-sm">
                        <details className="rounded-2xl bg-gradient-to-br from-white/5 to-white/[0.02] border border-indigo-500/20 p-5 backdrop-blur hover:border-indigo-500/40 transition-all duration-300">
                            <summary className="font-bold cursor-pointer text-base text-gray-200">تابلوهای موزیکال چگونه کار می‌کنند؟</summary>
                            <p className="text-gray-300 mt-3 leading-7">تابلوهای موزیکال ما همراه با یک QR code یا لینک مخصوص هستند که با اسکن آن می‌توانید به پلی‌لیست انتخابی هنرمند دسترسی پیدا کنید و در حین تماشای تابلو، موسیقی‌های همراه را گوش دهید.</p>
                        </details>
                        <details className="rounded-2xl bg-gradient-to-br from-white/5 to-white/[0.02] border border-indigo-500/20 p-5 backdrop-blur hover:border-indigo-500/40 transition-all duration-300">
                            <summary className="font-bold cursor-pointer text-base text-gray-200">ابعاد و نوع قاب‌ها چگونه است؟</summary>
                            <p className="text-gray-300 mt-3 leading-7">در صفحه هر تابلو، ابعاد دقیق و انواع قاب‌های موجود مشخص شده است. همه تابلوها با قاب و آماده نصب ارسال می‌شوند.</p>
                        </details>
                        <details className="rounded-2xl bg-gradient-to-br from-white/5 to-white/[0.02] border border-indigo-500/20 p-5 backdrop-blur hover:border-indigo-500/40 transition-all duration-300">
                            <summary className="font-bold cursor-pointer text-base text-gray-200">ارسال چقدر طول می‌کشد؟</summary>
                            <p className="text-gray-300 mt-3 leading-7">سفارش‌ها در روزهای کاری ظرف ۴۸ تا ۷۲ ساعت با بسته‌بندی ویژه و ایمن ارسال می‌شوند. جزئیات کامل در صفحه پرداخت نمایش داده می‌شود.</p>
                        </details>
                    </div>
                </section>
            </div>
        </div>
    );
}

export default AboutPage;


