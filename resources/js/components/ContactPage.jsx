import React from 'react';
import { useSeo } from '../hooks/useSeo';

function ContactPage(){
    useSeo({
        title: 'تماس با ما - فروشگاه ایلی استور',
        description: 'راه‌های ارتباط با ایلی استور: پشتیبانی واتساپ و تلگرام، ایمیل و فرم تماس. سوالی دارید؟ همین حالا پیام بفرستید.',
        keywords: 'تماس با ایلی استور, پشتیبانی, شماره تماس, ایمیل, واتساپ, تلگرام',
        image: '/images/logo.png',
        canonical: window.location.origin + '/contact'
    });

    // فرم حذف شد؛ فقط راه‌های ارتباطی باقی مانده است

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white px-4 py-6">
            <div className="max-w-7xl mx-auto">
                {/* Intro */}
                <section className="rounded-2xl glass-card soft-shadow border border-white/10 p-5 md:p-7">
                    <h1 className="text-2xl md:text-3xl font-extrabold mb-3">بیایید صحبت کنیم</h1>
                    <p className="text-gray-300 text-sm leading-7">
                        تیم پشتیبانی ایلی استور اینجاست تا سریع پاسخ بدهد. اگر درباره سفارش یا موجودی سوال دارید، همین حالا از یکی از راه‌های زیر پیام بفرستید.
                    </p>
                    <div className="mt-4 space-y-2 text-sm">
                        <a href="https://t.me/jemeh_shop" target="_blank" rel="noopener noreferrer" className="block rounded-lg bg-white/5 border border-white/10 p-3 hover:bg-white/10 transition">
                            تلگرام: @jemeh_shop
                        </a>
                        <a href="mailto:support@jemeh.shop" className="block rounded-lg bg-white/5 border border-white/10 p-3 hover:bg-white/10 transition">
                            ایمیل: support@jemeh.shop
                        </a>
                        <a href="https://instagram.com/jemehshopp" target="_blank" rel="noopener noreferrer" className="block rounded-lg bg-white/5 border border-white/10 p-3 hover:bg-white/10 transition">
                            اینستاگرام: @jemehshopp
                        </a>
                    </div>
                </section>

                
            </div>
        </div>
    );
}

export default ContactPage;


