import React from 'react';
import { useSeo } from '../hooks/useSeo';

function AboutPage(){
    useSeo({
        title: 'درباره ما - فروشگاه ایلی استور',
        description: 'با فلسفه طراحی مینیمال، کیفیت‌محور و تجربه خرید موبایل‌پسند ایلی استور آشنا شوید. داستان شروع، ارزش‌ها و روند تولید ما را بخوانید.',
        keywords: 'درباره ایلی استور, فروشگاه اکسسوری, تابلو, کیفیت, طراحی مینیمال, خرید آنلاین',
        image: '/images/logo.png',
        canonical: window.location.origin + '/about'
    });

    React.useEffect(() => {
        const structuredData = {
            '@context': 'https://schema.org',
            '@type': 'Organization',
            'name': 'ایلی استور',
            'url': window.location.origin,
            'logo': window.location.origin + '/images/logo.png',
            'sameAs': [
                'https://instagram.com/jemehshopp',
                'https://t.me/jemeh_shop'
            ]
        };
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(structuredData);
        document.head.appendChild(script);
        return () => { try { document.head.removeChild(script); } catch {} };
    }, []);

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white px-4 py-6">
            <div className="max-w-7xl mx-auto">
                {/* Hero */}
                <section className="rounded-2xl glass-card soft-shadow border border-white/10 p-5 md:p-7 mb-6">
                    <div className="flex items-start md:items-center justify-between gap-4 flex-col md:flex-row">
                        <div className="min-w-0">
                            <h1 className="text-2xl md:text-3xl font-extrabold">درباره ایلی استور</h1>
                            <p className="text-gray-300 text-sm mt-2 leading-6">
                                ما یک فروشگاه آنلاین اکسسوری و تابلو با تمرکز روی طراحی مینیمال و کیفیت هستیم. هدف ما این است که محصولاتی تحویل بدهیم که هر روز دیدنشان حس خوبی بدهد.
                            </p>
                        </div>
                        <img src="/images/logo.png" alt="Iliywstore" className="w-14 h-14 rounded-xl border border-white/10" />
                    </div>
                </section>

                {/* Story */}
                <section className="rounded-2xl glass-card soft-shadow border border-white/10 p-5 md:p-7 mb-6">
                    <h2 className="text-lg font-bold mb-3">از یک ایده ساده تا کمد روزمره</h2>
                    <p className="text-gray-300 text-sm leading-7">
                        شروع ایلی استور از یک دغدغه شخصی بود: اکسسوری و تابلوهای ساده، باکیفیت و قابل‌اعتماد که با هر دکوراسیونی ست شوند. کم‌کم همین ایده تبدیل شد به مجموعه‌ای از اکسسوری‌ها و تابلوهای کاربردی که بدون جنگ با ترندها عمر می‌کنند.
                    </p>
                    <div className="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 text-center">
                        <div className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="text-cherry-400 font-extrabold">+1000</div>
                            <div className="text-gray-400 text-xs">مشتری راضی</div>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="text-cherry-400 font-extrabold">48h</div>
                            <div className="text-gray-400 text-xs">میانگین ارسال</div>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="text-cherry-400 font-extrabold">7day</div>
                            <div className="text-gray-400 text-xs">مرجوع آسان</div>
                        </div>
                        <div className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="text-cherry-400 font-extrabold">QC</div>
                            <div className="text-gray-400 text-xs">کنترل کیفی تک‌به‌تک</div>
                        </div>
                    </div>
                </section>

                {/* What we value */}
                <section className="rounded-2xl glass-card soft-shadow border border-white/10 p-5 md:p-7 mb-6">
                    <h2 className="text-lg font-bold mb-4">چرا ایلی استور؟</h2>
                    <ul className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <li className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="font-semibold mb-1">دوخت تمیز و پارچه حساب‌شده</div>
                            <p className="text-gray-300 leading-6">از انتخاب نخ تا شست‌وشوی اولیه پارچه، همه‌چیز با وسواس انجام می‌شود تا لباس بعد از چند بار شست‌وشو همان فرم روز اول را داشته باشد.</p>
                        </li>
                        <li className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="font-semibold mb-1">طراحی مینیمال، قابل ست‌کردن</div>
                            <p className="text-gray-300 leading-6">پالت رنگ محدود و فرم‌هایی که کنار هم خوب می‌نشینند؛ نتیجه‌اش یک کمد جمع‌وجور است که هر روز انتخاب را ساده می‌کند.</p>
                        </li>
                        <li className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="font-semibold mb-1">تجربه سریع و شفاف</div>
                            <p className="text-gray-300 leading-6">ارسال سریع، پیگیری لحظه‌ای سفارش و پشتیبانی واقعی. اگر چیزی مطابق انتظار نبود، راحت مرجوع کنید.</p>
                        </li>
                        <li className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <div className="font-semibold mb-1">قیمت‌گذاری منصفانه</div>
                            <p className="text-gray-300 leading-6">هزینه‌ها را شفاف نگه می‌داریم؛ تمرکز روی کیفیت و دوام است، نه روی بسته‌بندی پرزرق‌وبرق.</p>
                        </li>
                    </ul>
                </section>

                {/* FAQ mini */}
                <section className="rounded-2xl glass-card soft-shadow border border-white/10 p-5 md:p-7">
                    <h2 className="text-lg font-bold mb-4">سوالات پرتکرار</h2>
                    <div className="space-y-3 text-sm">
                        <details className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <summary className="font-semibold cursor-pointer">سایزبندی‌ها چطور است؟</summary>
                            <p className="text-gray-300 mt-2 leading-6">در صفحه هر محصول جدول دقیق اندازه‌ها قرار داده شده. اگر بین دو سایز مردد بودید، معمولاً پیشنهاد ما سایز بزرگ‌تر است تا فیت راحت‌تری داشته باشید.</p>
                        </details>
                        <details className="rounded-xl bg-white/5 border border-white/10 p-3">
                            <summary className="font-semibold cursor-pointer">ارسال چقدر طول می‌کشد؟</summary>
                            <p className="text-gray-300 mt-2 leading-6">سفارش‌ها در روزهای کاری ظرف ۲۴ تا ۷۲ ساعت ارسال می‌شوند. جزئیات در صفحه پرداخت نمایش داده می‌شود.</p>
                        </details>
                    </div>
                </section>
            </div>
        </div>
    );
}

export default AboutPage;


