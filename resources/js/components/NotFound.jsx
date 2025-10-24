import React from 'react';
import { Link } from 'react-router-dom';

function NotFound() {
    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
            <div className="text-center">
                <div className="text-9xl font-bold text-amber-400 mb-4">404</div>
                <h1 className="text-2xl font-bold text-white mb-4">صفحه مورد نظر یافت نشد</h1>
                <p className="text-gray-400 mb-8">متأسفانه صفحه‌ای که به دنبال آن هستید وجود ندارد.</p>
                <Link 
                    to="/" 
                    className="inline-block bg-gradient-to-r from-amber-600 to-indigo-600 hover:from-amber-500 hover:to-indigo-500 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200"
                >
                    بازگشت به صفحه اصلی
                </Link>
            </div>
        </div>
    );
}

export default NotFound;