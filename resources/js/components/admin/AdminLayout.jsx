import React, { useState, useEffect } from 'react';
import { Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import { initializeAdminApi } from '../../utils/adminApi';

function AdminLayout() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [sidebarAnimating, setSidebarAnimating] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const location = useLocation();
    const navigate = useNavigate();
    const { user, logout, loading, isAuthenticated, isAdmin } = useAuth();

    // Check authentication and admin access
    useEffect(() => {
        if (!loading) {
            if (!isAuthenticated) {
                // Redirect to account panel for login
                navigate('/account');
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { 
                        type: 'warning', 
                        message: 'لطفاً ابتدا وارد حساب کاربری خود شوید' 
                    } 
                }));
            } else if (!isAdmin) {
                // User is logged in but not admin
                navigate('/account');
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { 
                        type: 'error', 
                        message: 'شما دسترسی به پنل ادمین ندارید' 
                    } 
                }));
            }
        }
    }, [loading, isAuthenticated, isAdmin, navigate]);

    // Initialize admin API when component mounts
    useEffect(() => {
        if (isAuthenticated && isAdmin) {
            initializeAdminApi();
        }
    }, [isAuthenticated, isAdmin]);

    const menuItems = [
        {
            id: 'dashboard',
            title: 'داشبورد',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
                </svg>
            ),
            path: '/admin'
        },
        {
            id: 'products',
            title: 'محصولات',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            ),
            path: '/admin/products'
        },
        {
            id: 'orders',
            title: 'سفارش‌ها',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            ),
            path: '/admin/orders'
        },
        {
            id: 'delivery',
            title: 'روش‌های ارسال',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            ),
            path: '/admin/delivery'
        },
        {
            id: 'discounts',
            title: 'کدهای تخفیف',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            ),
            path: '/admin/discounts'
        },
        {
            id: 'campaigns',
            title: 'کمپین‌ها',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            ),
            path: '/admin/campaigns'
        }
    ];

    const handleLogout = async () => {
        await logout();
        navigate('/');
    };

    const openSidebar = () => {
        setSidebarOpen(true);
        setTimeout(() => {
            setSidebarAnimating(true);
        }, 10);
    };

    const closeSidebar = () => {
        setSidebarAnimating(false);
        setTimeout(() => {
            setSidebarOpen(false);
        }, 300);
    };

    // Handle escape key and body scroll
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape' && sidebarOpen) {
                closeSidebar();
            }
        };

        if (sidebarOpen) {
            document.addEventListener('keydown', handleEscape);
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = 'unset';
        }

        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = 'unset';
        };
    }, [sidebarOpen]);

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950">
            {/* Mobile Header */}
            <div className="lg:hidden bg-gradient-to-r from-amber-600 to-indigo-700 px-4 py-4 sticky top-0 z-50">
                <div className="flex items-center justify-between">
                    <button
                        onClick={openSidebar}
                        className="text-white p-2 rounded-lg hover:bg-white/10 transition-colors"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 className="text-white font-bold text-lg">پنل ادمین</h1>
                    
                    {/* User Menu Dropdown */}
                    <div className="relative">
                        <button 
                            onClick={() => setUserMenuOpen(!userMenuOpen)} 
                            className="text-white p-2 rounded-lg hover:bg-white/10 transition-colors"
                        >
                            <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </button>
                        
                        {userMenuOpen && (
                            <div className="absolute left-0 mt-2 w-48 bg-[#0d0d14]/95 border border-white/10 rounded-xl shadow-2xl p-2 z-[100]">
                                <div className="px-3 py-2 text-sm text-gray-300 border-b border-white/10 mb-2">
                                    {user?.name || 'ادمین'}
                                </div>
                                <button
                                    onClick={() => {
                                        navigate('/account');
                                        setUserMenuOpen(false);
                                    }}
                                    className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm"
                                >
                                    پنل کاربری
                                </button>
                                <button
                                    onClick={() => {
                                        navigate('/');
                                        setUserMenuOpen(false);
                                    }}
                                    className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm"
                                >
                                    فروشگاه
                                </button>
                                <button
                                    onClick={async () => {
                                        await handleLogout();
                                        setUserMenuOpen(false);
                                    }}
                                    className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm text-red-300"
                                >
                                    خروج
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Mobile Sidebar Overlay */}
            {sidebarOpen && (
                <div className="lg:hidden fixed inset-0 z-50">
                    <div 
                        className="fixed inset-0 bg-black/50 transition-opacity duration-300"
                        style={{
                            opacity: sidebarAnimating ? 1 : 0
                        }}
                        onClick={closeSidebar}
                    ></div>
                    <div 
                        className="fixed right-0 top-0 h-full w-80 bg-gradient-to-b from-gray-800 to-slate-950 shadow-2xl transition-transform duration-300 ease-out"
                        style={{
                            transform: sidebarAnimating ? 'translateX(0)' : 'translateX(100%)'
                        }}
                    >
                        <div className="p-6">
                            {/* Close Button */}
                            <div className="flex justify-end mb-6">
                                <button
                                    onClick={closeSidebar}
                                    className="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-white/10"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {/* Admin Info */}
                            <div className="text-center mb-8">
                                <div className="w-20 h-20 bg-gradient-to-br from-amber-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4 transform hover:scale-110 transition-transform duration-200">
                                    <span className="text-white text-2xl font-bold">
                                        {user?.name?.charAt(0) || 'A'}
                                    </span>
                                </div>
                                <h2 className="text-white text-xl font-semibold">{user?.name || 'ادمین'}</h2>
                                <p className="text-purple-400 text-sm">مدیر سیستم</p>
                            </div>

                            {/* Menu Items */}
                            <nav className="space-y-2">
                                {menuItems.map((item) => (
                                    <button
                                        key={item.id}
                                        onClick={() => {
                                            navigate(item.path);
                                            closeSidebar();
                                        }}
                                        className={`w-full flex items-center space-x-3 space-x-reverse p-4 rounded-xl transition-all duration-200 transform hover:scale-105 hover:shadow-lg ${
                                            location.pathname === item.path
                                                ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30 shadow-purple-500/20'
                                                : 'text-gray-300 hover:bg-white/5 hover:text-white hover:shadow-white/10'
                                        }`}
                                    >
                                        {item.icon}
                                        <span className="font-medium">{item.title}</span>
                                    </button>
                                ))}
                            </nav>

                            {/* Logout Button */}
                            <button
                                onClick={handleLogout}
                                className="w-full mt-8 flex items-center space-x-3 space-x-reverse p-4 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200 transform hover:scale-105 hover:shadow-lg hover:shadow-red-500/20"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span className="font-medium">خروج از حساب</span>
                            </button>
                        </div>
                    </div>
                </div>
            )}

            <div className="flex">
                {/* Desktop Sidebar */}
                <div className="hidden lg:block w-80 bg-gradient-to-b from-gray-800 to-slate-950 min-h-screen sticky top-0">
                    <div className="p-8">
                        {/* Admin Info */}
                        <div className="text-center mb-8">
                            <div className="w-24 h-24 bg-gradient-to-br from-amber-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                <span className="text-white text-3xl font-bold">
                                    {user?.name?.charAt(0) || 'A'}
                                </span>
                            </div>
                            <h2 className="text-white text-2xl font-bold mb-2">{user?.name || 'ادمین'}</h2>
                            <p className="text-purple-400">مدیر سیستم</p>
                        </div>

                        {/* Menu Items */}
                        <nav className="space-y-3">
                            {menuItems.map((item) => (
                                <button
                                    key={item.id}
                                    onClick={() => navigate(item.path)}
                                    className={`w-full flex items-center space-x-4 space-x-reverse p-4 rounded-xl transition-all duration-200 ${
                                        location.pathname === item.path
                                            ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30'
                                            : 'text-gray-300 hover:bg-white/5 hover:text-white'
                                    }`}
                                >
                                    {item.icon}
                                    <span className="font-medium text-lg">{item.title}</span>
                                </button>
                            ))}
                        </nav>

                        {/* Quick Actions */}
                        <div className="mt-8 p-4 bg-white/5 rounded-xl">
                            <h3 className="text-white font-semibold mb-3">دسترسی سریع</h3>
                            <div className="space-y-2">
                                <button
                                    onClick={() => navigate('/')}
                                    className="w-full text-right px-3 py-2 rounded-lg text-gray-300 hover:bg-white/5 hover:text-white transition-colors text-sm"
                                >
                                    🏪 فروشگاه
                                </button>
                                <button
                                    onClick={() => navigate('/account')}
                                    className="w-full text-right px-3 py-2 rounded-lg text-gray-300 hover:bg-white/5 hover:text-white transition-colors text-sm"
                                >
                                    👤 پنل کاربری
                                </button>
                            </div>
                        </div>

                        {/* Logout Button */}
                        <button
                            onClick={handleLogout}
                            className="w-full mt-8 flex items-center space-x-4 space-x-reverse p-4 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span className="font-medium text-lg">خروج از حساب</span>
                        </button>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex-1 lg:ml-0">
                    <div className="p-4 lg:p-8">
                <Outlet />
                    </div>
                </div>
            </div>
        </div>
    );
}

export default AdminLayout;