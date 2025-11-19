import React from 'react';
import { Helmet } from 'react-helmet-async';

function ShippingPage() {
    return (
        <>
            <Helmet>
                <title>ارسال و تحویل | ایلی استور - فروشگاه آنلاین اکسسوری و تابلو</title>
                <meta name="description" content="اطلاعات کامل درباره روش‌های ارسال، هزینه ارسال، زمان تحویل و شرایط ارسال در فروشگاه ایلی استور. ارسال سریع و مطمئن به سراسر کشور." />
                <meta name="keywords" content="ارسال, تحویل, پست, پیک, هزینه ارسال, زمان تحویل, ایلی استور" />
                <link rel="canonical" href={`${window.location.origin}/shipping`} />
            </Helmet>

            <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
                {/* Header */}
                <div className="sticky top-0 z-30 bg-black/20 backdrop-blur-md border-b border-white/10">
                    <div className="max-w-4xl mx-auto px-4 py-6">
                        <h1 className="text-2xl font-bold text-white text-center">ارسال و تحویل</h1>
                    </div>
                </div>

                {/* Content */}
                <div className="max-w-4xl mx-auto px-4 py-8">
                    <div className="space-y-8">
                        {/* Shipping Methods */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">روش‌های ارسال</h2>
                            <div className="grid md:grid-cols-2 gap-6">
                                <div className="bg-white/5 rounded-xl p-4">
                                    <h3 className="text-lg font-semibold text-white mb-2">پست پیشتاز</h3>
                                    <p className="text-gray-300 text-sm mb-3">
                                        ارسال از طریق پست پیشتاز با قابلیت ردیابی کامل
                                    </p>
                                    <div className="text-cherry-400 font-medium">3-5 روز کاری</div>
                                </div>
                                <div className="bg-white/5 rounded-xl p-4">
                                    <h3 className="text-lg font-semibold text-white mb-2">پیک موتوری</h3>
                                    <p className="text-gray-300 text-sm mb-3">
                                        ارسال سریع با پیک موتوری در شهر تهران
                                    </p>
                                    <div className="text-cherry-400 font-medium">2-4 ساعت</div>
                                </div>
                            </div>
                        </section>

                        {/* Shipping Costs */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">هزینه ارسال</h2>
                            <div className="overflow-x-auto overflow-y-hidden">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b border-white/10">
                                            <th className="text-right py-3 text-white">مبلغ سفارش</th>
                                            <th className="text-right py-3 text-white">هزینه ارسال</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr className="border-b border-white/5">
                                            <td className="py-3 text-gray-300">تا 500 هزار تومان</td>
                                            <td className="py-3 text-gray-300">25 هزار تومان</td>
                                        </tr>
                                        <tr className="border-b border-white/5">
                                            <td className="py-3 text-gray-300">500 هزار تا 1 میلیون تومان</td>
                                            <td className="py-3 text-gray-300">15 هزار تومان</td>
                                        </tr>
                                        <tr>
                                            <td className="py-3 text-gray-300">بالای 1 میلیون تومان</td>
                                            <td className="py-3 text-green-400 font-medium">رایگان</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        {/* Delivery Areas */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">مناطق تحت پوشش</h2>
                            <div className="grid md:grid-cols-2 gap-6">
                                <div>
                                    <h3 className="text-lg font-semibold text-white mb-3">تهران</h3>
                                    <ul className="space-y-1 text-gray-300 text-sm">
                                        <li>• تمام مناطق تهران</li>
                                        <li>• ارسال با پیک موتوری</li>
                                        <li>• تحویل در همان روز</li>
                                    </ul>
                                </div>
                                <div>
                                    <h3 className="text-lg font-semibold text-white mb-3">سایر شهرها</h3>
                                    <ul className="space-y-1 text-gray-300 text-sm">
                                        <li>• ارسال با پست پیشتاز</li>
                                        <li>• قابلیت ردیابی</li>
                                        <li>• تحویل در 3-5 روز</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        {/* Important Notes */}
                        <section className="bg-white/5 backdrop-blur-sm rounded-2xl p-6 border border-white/10">
                            <h2 className="text-xl font-bold text-white mb-4">نکات مهم</h2>
                            <div className="space-y-3 text-gray-300 text-sm">
                                <div className="flex items-start gap-2">
                                    <span className="text-cherry-400 mt-1">•</span>
                                    <span>در صورت عدم حضور در آدرس، بسته به اداره پست محلی ارسال می‌شود</span>
                                </div>
                                <div className="flex items-start gap-2">
                                    <span className="text-cherry-400 mt-1">•</span>
                                    <span>لطفاً شماره تماس صحیح وارد کنید تا در صورت نیاز با شما تماس گرفته شود</span>
                                </div>
                                <div className="flex items-start gap-2">
                                    <span className="text-cherry-400 mt-1">•</span>
                                    <span>در صورت تغییر آدرس، حتماً قبل از ارسال با ما تماس بگیرید</span>
                                </div>
                                <div className="flex items-start gap-2">
                                    <span className="text-cherry-400 mt-1">•</span>
                                    <span>هزینه ارسال برای سفارشات بالای 1 میلیون تومان رایگان است</span>
                    </div>
                    </div>
                </section>

                        {/* Contact Info */}
                        <section className="bg-gradient-to-r from-cherry-600/20 to-violet-600/20 backdrop-blur-sm rounded-2xl p-6 border border-cherry-500/30">
                            <h2 className="text-xl font-bold text-white mb-4">سوالی دارید؟</h2>
                            <p className="text-gray-300 mb-4">
                                برای اطلاعات بیشتر درباره ارسال و تحویل، با ما تماس بگیرید
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

export default ShippingPage;