import React from 'react';
import { Link } from 'react-router-dom';

function Footer() {
    return (
        <footer className="bg-black/50 backdrop-blur-md border-t border-white/10 mt-20">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                    {/* Company Info */}
                    <div className="col-span-1 md:col-span-2">
                        <div className="flex items-center space-x-2 mb-4">
                            {/* <img src="/images/logo.png" alt="Logo" className="h-8 w-8"/> */}
                            <span className="font-bold text-xl text-white">ایلی استور</span>
                            {/* E-Namad logo */}
                            <a referrerPolicy="origin" target="_blank"
                               href="https://trustseal.enamad.ir/?id=674556&Code=81yH64Nnp4T3nhMSRqlEpaYxcR6WaLdu"><img
                                referrerPolicy="origin"
                                src="https://trustseal.enamad.ir/logo.aspx?id=674556&Code=81yH64Nnp4T3nhMSRqlEpaYxcR6WaLdu"
                                alt="" style={{cursor: 'pointer'}} code="81yH64Nnp4T3nhMSRqlEpaYxcR6WaLdu"/></a>
                        </div>
                        <p className="text-gray-400 mb-4">
                            فروشگاه آنلاین اکسسوری و تابلو با بهترین قیمت
                        </p>
                        <div className="flex space-x-4">
                            <a
                                href="https://instagram.com/jemehshopp"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="text-gray-400 hover:text-white transition-colors"
                            >
                                <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {/* Quick Links */}
                    <div>
                        <h3 className="text-white font-semibold mb-4">لینک‌های سریع</h3>
                        <ul className="space-y-2">
                            <li>
                                <Link to="/" className="text-gray-400 hover:text-white transition-colors">
                                    خانه
                                </Link>
                            </li>
                            <li>
                                <Link to="/products" className="text-gray-400 hover:text-white transition-colors">
                                    محصولات
                                </Link>
                            </li>
                            <li>
                                <Link to="/about" className="text-gray-400 hover:text-white transition-colors">
                                    درباره ما
                                </Link>
                            </li>
                            <li>
                                <Link to="/contact" className="text-gray-400 hover:text-white transition-colors">
                                    تماس
                                </Link>
                            </li>
                        </ul>
                    </div>

                    {/* Customer Service */}
                    <div>
                        <h3 className="text-white font-semibold mb-4">خدمات مشتری</h3>
                        <ul className="space-y-2">
                            <li>
                                <Link to="/account" className="text-gray-400 hover:text-white transition-colors">
                                    حساب کاربری
                                </Link>
                            </li>
                            <li>
                                <Link to="/account/orders" className="text-gray-400 hover:text-white transition-colors">
                                    سفارشات
                                </Link>
                            </li>
                            <li>
                                <Link to="/shipping" className="text-gray-400 hover:text-white transition-colors">
                                    ارسال و تحویل
                                </Link>
                            </li>
                            <li>
                                <Link to="/returns" className="text-gray-400 hover:text-white transition-colors">
                                    بازگردانی
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <div className="border-t border-white/10 mt-8 pt-8 text-center">
                    <p className="text-gray-400">
                        © 2024 ایلی استور. تمامی حقوق محفوظ است.
                    </p>
                </div>
            </div>
        </footer>
    );
}

export default Footer;
