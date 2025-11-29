import React, { useState } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import CheckoutAuthModal from './CheckoutAuthModal';
import SearchDropdown from './SearchDropdown';

function Header() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const navigate = useNavigate();
    const location = useLocation();
    const { user, logout, isAdmin } = useAuth();
    const { cartData } = useCart();
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const [authOpen, setAuthOpen] = useState(false);

    // Check if user is in account area
    const isInAccountArea = location.pathname.startsWith('/account');


    const toggleMenu = () => {
        setIsMenuOpen(!isMenuOpen);
    };

    return (
        <>
        <header className="bg-black/50 backdrop-blur-md border-b border-white/10 sticky top-0 z-50">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="flex justify-between items-center h-16">
                    {/* Logo */}
                    <Link to="/" className="flex items-center space-x-2 text-white hover:text-cherry-400 transition-colors">
                        <img src="/images/logo.png" alt="Logo" className="h-8 w-8" />
                        <span className="font-bold text-xl">ایلی استور</span>
                    </Link>

                    {/* Desktop Navigation */}
                    <nav className="hidden md:flex space-x-8">
                        <Link to="/" className="text-white hover:text-cherry-400 transition-colors">
                            خانه
                        </Link>
                        <Link to="/products" className="text-white hover:text-cherry-400 transition-colors">
                            محصولات
                        </Link>
                        <Link to="/about" className="text-white hover:text-cherry-400 transition-colors">
                            درباره ما
                        </Link>
                        <Link to="/contact" className="text-white hover:text-cherry-400 transition-colors">
                            تماس
                        </Link>
                    </nav>

                    {/* Search Bar - Desktop */}
                    <div className="hidden md:block w-80 relative" style={{ zIndex: 99999 }}>
                        <SearchDropdown />
                    </div>

                    {/* Right side items */}
                    <div className="flex items-center space-x-4">
                        {/* Cart */}
                        <Link 
                            to="/cart" 
                            className="relative group inline-flex items-center justify-center text-white"
                            aria-label="Cart"
                        >
                            <span className="absolute inset-0 rounded-full bg-white/0 group-hover:bg-white/5 transition-colors"></span>
                            <svg className="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 10-8 0v4M5 9h14l-1 10a2 2 0 01-2 2H8a2 2 0 01-2-2L5 9z" />
                            </svg>
                            {cartData.count > 0 && (
                                <span className="absolute -top-1 -right-1 z-20 bg-gradient-to-r from-cherry-600 to-violet-600 text-white text-[10px] rounded-full h-5 min-w-5 px-1 flex items-center justify-center ring-1 ring-white/30 shadow">
                                    {cartData.count}
                                </span>
                            )}
                        </Link>

                        {/* User Menu - Only show when not in account area */}
                        {!isInAccountArea && (
                            <div className="relative">
                                <button onClick={() => setUserMenuOpen(v=>!v)} className="flex items-center space-x-2 text-white hover:text-cherry-400 transition-colors">
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span className="hidden sm:block">حساب کاربری</span>
                                </button>
                                {userMenuOpen && (
                                    <div className="absolute left-0 mt-2 w-56 bg-[#0d0d14]/95 border border-white/10 rounded-xl shadow-2xl p-2 transform translateZ(0)" style={{ zIndex: 9999, willChange: 'transform' }}>
                                        {user ? (
                                            <>
                                                <Link 
                                                    to="/account" 
                                                    className="block px-3 py-2 rounded hover:bg-white/5 text-sm"
                                                    onClick={() => setUserMenuOpen(false)}
                                                >
                                                    پنل کاربری
                                                </Link>
                                                {isAdmin && (
                                                    <Link 
                                                        to="/admin" 
                                                        className="block px-3 py-2 rounded hover:bg-white/5 text-sm"
                                                        onClick={() => setUserMenuOpen(false)}
                                                    >
                                                        پنل ادمین
                                                    </Link>
                                                )}
                                                <button onClick={async ()=>{ await logout(); setUserMenuOpen(false); }} className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm text-red-300">خروج</button>
                                            </>
                                        ) : (
                                            <>
                                                <button onClick={()=>{ setAuthOpen(true); setUserMenuOpen(false); }} className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm">ورود</button>
                                                <button onClick={()=>{ setAuthOpen(true); setUserMenuOpen(false); }} className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm">ثبت‌نام</button>
                                            </>
                                        )}
                                    </div>
                                )}
                            </div>
                        )}

                        {/* Mobile menu button */}
                        <button
                            onClick={toggleMenu}
                            className="md:hidden p-2 text-white hover:text-cherry-400 transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {isMenuOpen ? (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                ) : (
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                                )}
                            </svg>
                        </button>
                    </div>
                </div>

                {/* Mobile Navigation */}
                <div className={`md:hidden absolute left-0 right-0 top-16 z-40 transition-all duration-200 ${isMenuOpen ? 'opacity-100 translate-y-0' : 'pointer-events-none opacity-0 -translate-y-2'}`}>
                    <div className="bg-black/80 backdrop-blur border-t border-white/10">
                        {/* Mobile Search */}
                        <div className="px-4 py-3 border-b border-white/10 relative" style={{ zIndex: 99999 }}>
                            <SearchDropdown />
                        </div>
                        
                        <nav className="flex flex-col py-3">
                            <Link 
                                to="/" 
                                className="px-4 py-2 text-white/90 hover:text-white hover:bg-white/5 transition-colors"
                                onClick={() => setIsMenuOpen(false)}
                            >
                                خانه
                            </Link>
                            <Link 
                                to="/products" 
                                className="px-4 py-2 text-white/90 hover:text-white hover:bg-white/5 transition-colors"
                                onClick={() => setIsMenuOpen(false)}
                            >
                                محصولات
                            </Link>
                            <Link 
                                to="/about" 
                                className="px-4 py-2 text-white/90 hover:text-white hover:bg-white/5 transition-colors"
                                onClick={() => setIsMenuOpen(false)}
                            >
                                درباره ما
                            </Link>
                            <Link 
                                to="/contact" 
                                className="px-4 py-2 text-white/90 hover:text-white hover:bg-white/5 transition-colors"
                                onClick={() => setIsMenuOpen(false)}
                            >
                                تماس
                            </Link>
                        </nav>
                    </div>
                </div>
            </div>
        </header>
        <CheckoutAuthModal
            open={authOpen}
            onClose={() => setAuthOpen(false)}
            onSuccess={(u)=>{ setAuthOpen(false); try { window.dispatchEvent(new CustomEvent('toast:show', { detail: { type: 'success', message: 'ورود موفقیت‌آمیز بود' } })); } catch {} }}
        />
        </>
    );
}

export default Header;
