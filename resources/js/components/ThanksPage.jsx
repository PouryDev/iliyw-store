import React from 'react';
import { useParams } from 'react-router-dom';

function ThanksPage() {
    const { invoiceId } = useParams();
    
    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4">
            <div className="max-w-md w-full">
                {/* Success Card */}
                <div className="bg-gradient-to-br from-green-500/10 to-emerald-500/10 backdrop-blur-xl rounded-3xl border border-green-500/20 shadow-2xl p-8 text-center">
                    {/* Success Icon */}
                    <div className="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <svg className="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    
                    {/* Title */}
                    <h1 className="text-2xl font-bold text-white mb-3">
                        سفارش شما ثبت شد!
                    </h1>
                    
                    {/* Subtitle */}
                    <p className="text-gray-300 mb-6 leading-relaxed">
                        سفارش شما با موفقیت ثبت شد و در حال پردازش است. 
                        شماره فاکتور شما: <span className="text-green-400 font-semibold">#{invoiceId}</span>
                    </p>
                    
                    {/* Info Cards */}
                    <div className="space-y-4 mb-8">
                        <div className="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <div className="flex items-center justify-between">
                                <span className="text-gray-400 text-sm">وضعیت سفارش</span>
                                <span className="text-green-400 text-sm font-medium">در انتظار پردازش</span>
                            </div>
                        </div>
                        
                        <div className="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                            <div className="flex items-center justify-between">
                                <span className="text-gray-400 text-sm">شماره فاکتور</span>
                                <span className="text-white text-sm font-medium">#{invoiceId}</span>
                            </div>
                        </div>
                    </div>
                    
                    {/* Action Buttons */}
                    <div className="space-y-3">
                        <button
                            onClick={() => window.location.href = '/account/orders'}
                            className="w-full bg-gradient-to-r from-cherry-500 to-cherry-600 hover:from-cherry-600 hover:to-cherry-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg"
                        >
                            مشاهده سفارشات
                        </button>
                        
                        <button
                            onClick={() => window.location.href = '/'}
                            className="w-full bg-white/10 hover:bg-white/20 text-white font-medium py-3 px-6 rounded-xl transition-all duration-200 backdrop-blur-sm border border-white/20"
                        >
                            بازگشت به صفحه اصلی
                        </button>
                    </div>
                    
                    {/* Additional Info */}
                    <div className="mt-6 pt-6 border-t border-white/10">
                        <p className="text-gray-400 text-xs leading-relaxed">
                            لطفاً فیش واریزی را به شماره فاکتور ارسال کنید. 
                            پس از تایید پرداخت، سفارش شما آماده ارسال خواهد شد.
                        </p>
                    </div>
                </div>
                
                {/* Footer */}
                <div className="text-center mt-6">
                    <p className="text-gray-500 text-sm">
                        با تشکر از اعتماد شما به ایلی استور
                    </p>
                </div>
            </div>
        </div>
    );
}

export default ThanksPage;
