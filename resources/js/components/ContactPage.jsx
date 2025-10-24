import React from 'react';
import { useSeo } from '../hooks/useSeo';

function ContactPage(){
    useSeo({
        title: 'تماس با ما - فروشگاه iliyw',
        description: 'راه‌های ارتباط با iliyw: پشتیبانی اینستاگرام و تماس مستقیم. سوالی دارید؟ همین حالا پیام بفرستید.',
        keywords: 'تماس با iliyw, پشتیبانی, شماره تماس, ایمیل, اینستاگرام',
        image: '/images/logo.png',
        canonical: window.location.origin + '/contact'
    });

    // فرم حذف شد؛ فقط راه‌های ارتباطی باقی مانده است

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white px-4 py-6">
            <div className="max-w-7xl mx-auto">
                {/* Intro */}
                <section className="rounded-2xl glass-card soft-shadow border border-white/10 p-5 md:p-7">
                    <h1 className="text-2xl md:text-3xl font-extrabold mb-3">بیایید صحبت کنیم</h1>
                    <p className="text-gray-300 text-sm leading-7">
                        تیم پشتیبانی iliyw اینجاست تا سریع پاسخ بدهد. اگر درباره سفارش، ابعاد تابلو یا موجودی سوال دارید، همین حالا از یکی از راه‌های زیر پیام بفرستید.
                    </p>
                    <div className="mt-4 space-y-2 text-sm">
                        <a href="https://instagram.com/iliywstore" target="_blank" rel="noopener noreferrer" className="block rounded-lg bg-white/5 border border-white/10 p-3 hover:bg-white/10 transition">
                            اینستاگرام: @iliywstore
                        </a>
                        <a href="mailto:support@iliyw.store" className="block rounded-lg bg-white/5 border border-white/10 p-3 hover:bg-white/10 transition">
                            ایمیل: support@iliyw.store
                        </a>
                    </div>
                </section>

                
            </div>
        </div>
    );
}

export default ContactPage;


