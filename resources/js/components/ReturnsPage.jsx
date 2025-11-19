import React from 'react';
import { Helmet } from 'react-helmet-async';

function ReturnsPage() {
    return (
        <>
            <Helmet>
                <title>بازگردانی و مرجوعی | ایلی استور - سیاست بازگشت کالا</title>
                <meta name="description" content="شرایط و قوانین بازگردانی کالا در فروشگاه ایلی استور. اطلاعات کامل درباره مراحل مرجوعی، شرایط بازگشت و استرداد وجه." />
                <meta name="keywords" content="بازگردانی, مرجوعی, استرداد وجه, بازگشت کالا, ایلی استور" />
                <link rel="canonical" href={`${window.location.origin}/returns`} />
            </Helmet>

            <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
                {/* Header */}
                <div className="sticky top-0 z-30 bg-black/20 backdrop-blur-md border-b border-white/10">
                    <div className="max-w-4xl mx-auto px-4 py-6">
                        <h1 className="text-2xl font-bold text-white text-center">بازگردانی و مرجوعی</h1>
                    </div>
                </div>

                {/* Content */}
                <div className="max-w-4xl mx-auto px-4 py-8">
                    <div className="space-y-8">
                        {/* Return Policy */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">سیاست بازگشت کالا</h2>
                            <div className="space-y-4 text-gray-300">
                                <p>
                                    در فروشگاه ایلی استور، رضایت مشتریان اولویت اول ماست. به همین دلیل امکان بازگردانی کالا 
                                    در شرایط خاص برای شما فراهم شده است.
                                </p>
                                <p>
                                    تمامی کالاها باید در شرایط اولیه و بدون استفاده تحویل داده شوند تا امکان بازگشت فراهم باشد.
                                </p>
                            </div>
                        </section>

                        {/* Return Conditions */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">شرایط بازگشت</h2>
                            <div className="grid md:grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold text-white">کالاهای قابل بازگشت</h3>
                                    <ul className="space-y-2 text-gray-300 text-sm">
                                        <li className="flex items-start gap-2">
                                            <span className="text-green-400 mt-1">✓</span>
                                            <span>کالاهای بدون استفاده و در بسته‌بندی اصلی</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="text-green-400 mt-1">✓</span>
                                            <span>کالاهای دارای عیب و نقص</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="text-green-400 mt-1">✓</span>
                                            <span>کالاهای نادرست ارسال شده</span>
                                        </li>
                                    </ul>
                                </div>
                                <div className="space-y-4">
                                    <h3 className="text-lg font-semibold text-white">کالاهای غیرقابل بازگشت</h3>
                                    <ul className="space-y-2 text-gray-300 text-sm">
                                        <li className="flex items-start gap-2">
                                            <span className="text-red-400 mt-1">✗</span>
                                            <span>کالاهای استفاده شده</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="text-red-400 mt-1">✗</span>
                                            <span>کالاهای شخصی‌سازی شده</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <span className="text-red-400 mt-1">✗</span>
                                            <span>کالاهای آسیب دیده توسط مشتری</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        {/* Return Process */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">مراحل بازگشت</h2>
                            <div className="space-y-4">
                                <div className="flex items-start gap-4">
                                    <div className="bg-cherry-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0">1</div>
                                    <div>
                                        <h3 className="text-white font-semibold">تماس با پشتیبانی</h3>
                                        <p className="text-gray-300 text-sm">از طریق اینستاگرام یا تلگرام با ما تماس بگیرید</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-4">
                                    <div className="bg-cherry-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0">2</div>
                                    <div>
                                        <h3 className="text-white font-semibold">ارسال مدارک</h3>
                                        <p className="text-gray-300 text-sm">عکس کالا و فاکتور خرید را ارسال کنید</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-4">
                                    <div className="bg-cherry-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0">3</div>
                                    <div>
                                        <h3 className="text-white font-semibold">تایید بازگشت</h3>
                                        <p className="text-gray-300 text-sm">پس از بررسی، تاییدیه بازگشت برای شما ارسال می‌شود</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-4">
                                    <div className="bg-cherry-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0">4</div>
                                    <div>
                                        <h3 className="text-white font-semibold">ارسال کالا</h3>
                                        <p className="text-gray-300 text-sm">کالا را به آدرس مشخص شده ارسال کنید</p>
                                    </div>
                                </div>
                                <div className="flex items-start gap-4">
                                    <div className="bg-cherry-600 text-white rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold flex-shrink-0">5</div>
                                    <div>
                                        <h3 className="text-white font-semibold">استرداد وجه</h3>
                                        <p className="text-gray-300 text-sm">پس از دریافت کالا، وجه شما بازگردانده می‌شود</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {/* Time Limits */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">مهلت بازگشت</h2>
                            <div className="grid md:grid-cols-2 gap-6">
                                <div className="bg-white/5 rounded-xl p-4">
                                    <h3 className="text-lg font-semibold text-white mb-2">کالاهای عادی</h3>
                                    <p className="text-gray-300 text-sm mb-3">
                                        امکان بازگشت تا 7 روز پس از دریافت کالا
                                    </p>
                                    <div className="text-cherry-400 font-medium">7 روز کاری</div>
                                </div>
                                <div className="bg-white/5 rounded-xl p-4">
                                    <h3 className="text-lg font-semibold text-white mb-2">کالاهای معیوب</h3>
                                    <p className="text-gray-300 text-sm mb-3">
                                        امکان بازگشت تا 14 روز پس از دریافت کالا
                                    </p>
                                    <div className="text-cherry-400 font-medium">14 روز کاری</div>
                                </div>
                            </div>
                        </section>

                        {/* Refund Process */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">استرداد وجه</h2>
                            <div className="space-y-4 text-gray-300">
                                <p>
                                    پس از تایید بازگشت کالا و دریافت آن در انبار، وجه شما به همان روش پرداخت اولیه 
                                    بازگردانده می‌شود.
                                </p>
                                <div className="bg-white/5 rounded-xl p-4">
                                    <h3 className="text-white font-semibold mb-2">زمان استرداد وجه</h3>
                                    <ul className="space-y-1 text-sm">
                                        <li>• پرداخت آنلاین: 2-3 روز کاری</li>
                                        <li>• واریز به حساب: 3-5 روز کاری</li>
                                        <li>• کارت به کارت: 1-2 روز کاری</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        {/* Contact Info */}
                        <section className="bg-gradient-to-r from-cherry-600/20 to-violet-600/20 backdrop-blur-sm rounded-2xl p-6 border border-cherry-500/30">
                            <h2 className="text-xl font-bold text-white mb-4">نیاز به کمک دارید؟</h2>
                            <p className="text-gray-300 mb-4">
                                تیم پشتیبانی ما آماده پاسخگویی به سوالات شما درباره بازگشت کالا است
                            </p>
                            <div className="flex flex-wrap gap-4">
                                <a href="https://instagram.com/jemehshopp" target="_blank" rel="noopener noreferrer" className="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg transition-colors">
                                    اینستاگرام
                                </a>
                                <a href="https://t.me/jemeh_shop" target="_blank" rel="noopener noreferrer" className="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg transition-colors">
                                    تلگرام
                                </a>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </>
    );
}

export default ReturnsPage;
