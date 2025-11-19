import React, { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { apiRequest } from '../../utils/sanctumAuth';

function AccountProfile() {
    const { user, login, loading: authLoading } = useAuth();
    const [form, setForm] = useState({
        name: '',
        phone: '',
        instagram_id: '',
        email: ''
    });
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [stats, setStats] = useState({
        totalOrders: 0,
        totalAmount: 0,
        totalAddresses: 0
    });
    const [statsLoading, setStatsLoading] = useState(true);

    const formatPrice = (value) => {
        try { 
            return Number(value || 0).toLocaleString('fa-IR'); 
        } catch { 
            return value || '0'; 
        }
    };

    useEffect(() => {
        if (user) {
            setForm({
                name: user.name || '',
                phone: user.phone || '',
                instagram_id: user.instagram_id || '',
                email: user.email || ''
            });
        }
    }, [user]);

    // Load user stats
    useEffect(() => {
        const loadStats = async () => {
            if (!user) return;
            
            try {
                setStatsLoading(true);
                const res = await apiRequest('/api/user/stats');
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        setStats({
                            totalOrders: data.data.total_orders,
                            totalAmount: data.data.total_amount,
                            totalAddresses: data.data.total_addresses
                        });
                    }
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            } finally {
                setStatsLoading(false);
            }
        };

        loadStats();
    }, [user]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessage('');

        try {
            const res = await apiRequest('/api/user/profile', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(form),
            });

            if (!res.ok) {
                throw new Error('خطا در به‌روزرسانی پروفایل');
            }

            const data = await res.json();
            if (data.success) {
                setMessage('پروفایل با موفقیت به‌روزرسانی شد');
                // Refresh user data
                await login();
            }
        } catch (error) {
            setMessage('خطا در به‌روزرسانی پروفایل');
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e) => {
        setForm(prev => ({
            ...prev,
            [e.target.name]: e.target.value
        }));
    };

    // Show loading state while user data is being fetched
    if (authLoading) {
        return (
            <div className="max-w-2xl mx-auto">
                <div className="flex items-center justify-center min-h-96">
                    <div className="text-center">
                        <div className="w-12 h-12 border-4 border-cherry-500/30 border-t-cherry-500 rounded-full animate-spin mx-auto mb-4"></div>
                        <p className="text-gray-400">در حال بارگذاری...</p>
                    </div>
                </div>
            </div>
        );
    }

    // Show message if user is not authenticated
    if (!user) {
        return (
            <div className="max-w-2xl mx-auto">
                <div className="text-center py-12">
                    <div className="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h2 className="text-white text-xl font-semibold mb-2">خطا در بارگذاری پروفایل</h2>
                    <p className="text-gray-400">لطفاً دوباره وارد شوید</p>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-2xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">پروفایل من</h1>
                <p className="text-gray-400">اطلاعات شخصی خود را مدیریت کنید</p>
            </div>

            {/* Profile Card */}
            <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 lg:p-8">
                {/* User Avatar */}
                <div className="text-center mb-8">
                    <div className="w-24 h-24 bg-gradient-to-br from-cherry-500 to-cherry-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span className="text-white text-3xl font-bold">
                            {user?.name?.charAt(0) || 'U'}
                        </span>
                    </div>
                    <h2 className="text-white text-2xl font-semibold">{user?.name || 'کاربر'}</h2>
                    <p className="text-gray-400">عضو از {user?.created_at ? new Date(user.created_at).toLocaleDateString('fa-IR') : 'نامشخص'}</p>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Name */}
                    <div>
                        <label className="block text-white font-medium mb-2">نام و نام خانوادگی</label>
                        <input
                            type="text"
                            name="name"
                            value={form.name}
                            onChange={handleChange}
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500 focus:border-transparent transition-all duration-200"
                            placeholder="نام و نام خانوادگی خود را وارد کنید"
                        />
                    </div>

                    {/* Phone */}
                    <div>
                        <label className="block text-white font-medium mb-2">شماره تلفن</label>
                        <input
                            type="tel"
                            name="phone"
                            value={form.phone}
                            onChange={handleChange}
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500 focus:border-transparent transition-all duration-200"
                            placeholder="شماره تلفن خود را وارد کنید"
                        />
                    </div>

                    {/* Instagram ID */}
                    <div>
                        <label className="block text-white font-medium mb-2">آیدی اینستاگرام</label>
                        <input
                            type="text"
                            name="instagram_id"
                            value={form.instagram_id}
                            onChange={handleChange}
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500 focus:border-transparent transition-all duration-200"
                            placeholder="آیدی اینستاگرام خود را وارد کنید"
                        />
                    </div>

                    {/* Message */}
                    {message && (
                        <div className={`p-4 rounded-xl ${
                            message.includes('موفقیت') 
                                ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                : 'bg-red-500/20 text-red-400 border border-red-500/30'
                        }`}>
                            {message}
                        </div>
                    )}

                    {/* Submit Button */}
                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full bg-gradient-to-r from-cherry-500 to-cherry-600 hover:from-cherry-600 hover:to-cherry-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                        {loading ? (
                            <div className="flex items-center justify-center space-x-2 space-x-reverse">
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <span>در حال ذخیره...</span>
                            </div>
                        ) : (
                            'ذخیره تغییرات'
                        )}
                    </button>
                </form>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <div className="bg-gradient-to-br from-blue-500/10 to-blue-600/10 backdrop-blur-xl rounded-xl border border-blue-500/20 p-6 text-center">
                    <div className="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg className="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 className="text-white font-semibold text-lg">
                        {statsLoading ? (
                            <div className="w-6 h-6 border-2 border-blue-400/30 border-t-blue-400 rounded-full animate-spin mx-auto"></div>
                        ) : (
                            formatPrice(stats.totalOrders)
                        )}
                    </h3>
                    <p className="text-gray-400 text-sm">سفارش کل</p>
                </div>

                <div className="bg-gradient-to-br from-green-500/10 to-green-600/10 backdrop-blur-xl rounded-xl border border-green-500/20 p-6 text-center">
                    <div className="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg className="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <h3 className="text-white font-semibold text-lg">
                        {statsLoading ? (
                            <div className="w-6 h-6 border-2 border-green-400/30 border-t-green-400 rounded-full animate-spin mx-auto"></div>
                        ) : (
                            `${formatPrice(stats.totalAmount)} تومان`
                        )}
                    </h3>
                    <p className="text-gray-400 text-sm">مبلغ کل</p>
                </div>

                <div className="bg-gradient-to-br from-purple-500/10 to-purple-600/10 backdrop-blur-xl rounded-xl border border-purple-500/20 p-6 text-center">
                    <div className="w-12 h-12 bg-purple-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg className="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 className="text-white font-semibold text-lg">
                        {statsLoading ? (
                            <div className="w-6 h-6 border-2 border-purple-400/30 border-t-purple-400 rounded-full animate-spin mx-auto"></div>
                        ) : (
                            formatPrice(stats.totalAddresses)
                        )}
                    </h3>
                    <p className="text-gray-400 text-sm">آدرس ذخیره شده</p>
                </div>
            </div>
        </div>
    );
}

export default AccountProfile;
