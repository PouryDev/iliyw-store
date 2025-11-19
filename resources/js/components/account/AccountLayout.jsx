import React, { useState, useEffect } from 'react';
import { Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import AuthModal from '../AuthModal';

function AccountLayout() {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [sidebarAnimating, setSidebarAnimating] = useState(false);
    const [userMenuOpen, setUserMenuOpen] = useState(false);
    const [authModalOpen, setAuthModalOpen] = useState(false);
    const location = useLocation();
    const navigate = useNavigate();
    const { user, logout, loading } = useAuth();

    // Show auth modal if user is not logged in
    useEffect(() => {
        if (!loading && !user) {
            setAuthModalOpen(true);
            // Show toast notification
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { 
                    type: 'warning', 
                    message: 'لطفاً ابتدا وارد حساب کاربری خود شوید' 
                } 
            }));
        }
    }, [user, loading]);

    const menuItems = [
        {
            id: 'profile',
            title: 'پروفایل',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            ),
            path: '/account/profile'
        },
        {
            id: 'orders',
            title: 'سفارش‌های من',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            ),
            path: '/account/orders'
        },
        {
            id: 'addresses',
            title: 'آدرس‌ها',
            icon: (
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            ),
            path: '/account/addresses'
        }
    ];

    const handleLogout = async () => {
        await logout();
        navigate('/');
    };

    const openSidebar = () => {
        setSidebarOpen(true);
        // Trigger animation after DOM update
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
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
            {/* Mobile Header */}
            <div className="lg:hidden bg-gradient-to-r from-cherry-600 to-cherry-700 px-4 py-4 sticky top-0 z-50">
                <div className="flex items-center justify-between">
                    <button
                        onClick={openSidebar}
                        className="text-white p-2 rounded-lg hover:bg-white/10 transition-colors"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h1 className="text-white font-bold text-lg">حساب کاربری</h1>
                    
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
                                    {user?.name || 'کاربر'}
                                </div>
                                <button
                                    onClick={() => {
                                        navigate('/account/profile');
                                        setUserMenuOpen(false);
                                    }}
                                    className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm"
                                >
                                    پروفایل
                                </button>
                                <button
                                    onClick={() => {
                                        navigate('/account/orders');
                                        setUserMenuOpen(false);
                                    }}
                                    className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm"
                                >
                                    سفارش‌ها
                                </button>
                                <button
                                    onClick={() => {
                                        navigate('/account/addresses');
                                        setUserMenuOpen(false);
                                    }}
                                    className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm"
                                >
                                    آدرس‌ها
                                </button>
                                {user?.is_admin && (
                                    <button
                                        onClick={() => {
                                            navigate('/admin');
                                            setUserMenuOpen(false);
                                        }}
                                        className="w-full text-right px-3 py-2 rounded hover:bg-white/5 text-sm text-cherry-400"
                                    >
                                        پنل ادمین
                                    </button>
                                )}
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
                        className="fixed right-0 top-0 h-full w-80 bg-gradient-to-b from-gray-800 to-gray-900 shadow-2xl transition-transform duration-300 ease-out"
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

                            {/* User Info */}
                            <div className="text-center mb-8">
                                <div className="w-20 h-20 bg-gradient-to-br from-cherry-500 to-cherry-600 rounded-full flex items-center justify-center mx-auto mb-4 transform hover:scale-110 transition-transform duration-200">
                                    <span className="text-white text-2xl font-bold">
                                        {user?.name?.charAt(0) || 'U'}
                                    </span>
                                </div>
                                <h2 className="text-white text-xl font-semibold">{user?.name || 'کاربر'}</h2>
                                <p className="text-gray-400 text-sm">{user?.phone || user?.instagram_id}</p>
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
                                                ? 'bg-cherry-500/20 text-cherry-400 border border-cherry-500/30 shadow-cherry-500/20'
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
                <div className="hidden lg:block w-80 bg-gradient-to-b from-gray-800 to-gray-900 min-h-screen sticky top-0">
                    <div className="p-8">
                        {/* User Info */}
                        <div className="text-center mb-8">
                            <div className="w-24 h-24 bg-gradient-to-br from-cherry-500 to-cherry-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                <span className="text-white text-3xl font-bold">
                                    {user?.name?.charAt(0) || 'U'}
                                </span>
                            </div>
                            <h2 className="text-white text-2xl font-bold mb-2">{user?.name || 'کاربر'}</h2>
                            <p className="text-gray-400">{user?.phone || user?.instagram_id}</p>
                        </div>

                        {/* Menu Items */}
                        <nav className="space-y-3">
                            {menuItems.map((item) => (
                                <button
                                    key={item.id}
                                    onClick={() => navigate(item.path)}
                                    className={`w-full flex items-center space-x-4 space-x-reverse p-4 rounded-xl transition-all duration-200 ${
                                        location.pathname === item.path
                                            ? 'bg-cherry-500/20 text-cherry-400 border border-cherry-500/30'
                                            : 'text-gray-300 hover:bg-white/5 hover:text-white'
                                    }`}
                                >
                                    {item.icon}
                                    <span className="font-medium text-lg">{item.title}</span>
                                </button>
                            ))}
                        </nav>

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
            
            {/* Auth Modal */}
            <AuthModal 
                open={authModalOpen} 
                onClose={() => {
                    setAuthModalOpen(false);
                    navigate('/');
                }}
                onSuccess={() => {
                    setAuthModalOpen(false);
                }}
            />
        </div>
    );
}

export default AccountLayout;