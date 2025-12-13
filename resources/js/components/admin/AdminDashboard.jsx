import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiRequest } from '../../utils/sanctumAuth';

function AdminDashboard() {
    const navigate = useNavigate();
    const [dashboardData, setDashboardData] = useState({
        stats: {},
        recent_orders: [],
        top_products: [],
        revenue_by_month: [],
        orders_by_status: {}
    });
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const loadDashboardData = async () => {
            try {
                setLoading(true);
                setError(null);
                
                const res = await apiRequest('/api/admin/dashboard');
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        setDashboardData(data.data);
                    } else {
                        setError('خطا در بارگذاری اطلاعات داشبورد');
                    }
                } else {
                    setError('خطا در ارتباط با سرور');
                }
            } catch (error) {
                console.error('Failed to load dashboard data:', error);
                setError('خطا در بارگذاری اطلاعات');
            } finally {
                setLoading(false);
            }
        };

        loadDashboardData();
    }, []);

    const formatPrice = (value) => {
        try { 
            return Number(value || 0).toLocaleString('fa-IR'); 
        } catch { 
            return value || '0'; 
        }
    };

    const formatDate = (dateString) => {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch {
            return dateString;
        }
    };

    const getStatusColor = (status) => {
        const colors = {
            'pending': 'text-orange-400',
            'confirmed': 'text-emerald-400',
            'processing': 'text-blue-400',
            'shipped': 'text-purple-400',
            'delivered': 'text-green-400',
            'cancelled': 'text-red-400'
        };
        return colors[status] || 'text-gray-400';
    };

    const getStatusText = (status) => {
        const texts = {
            'pending': 'در انتظار',
            'confirmed': 'در حال آماده سازی',
            'processing': 'در حال پردازش',
            'shipped': 'ارسال شده',
            'delivered': 'تحویل داده شده',
            'cancelled': 'لغو شده'
        };
        return texts[status] || status;
    };

    const quickActions = [
        {
            title: 'محصول جدید',
            description: 'اضافه کردن محصول جدید',
            icon: (
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            ),
            color: 'from-green-500 to-green-600',
            onClick: () => navigate('/admin/products/create')
        },
        {
            title: 'سفارش‌های جدید',
            description: 'مشاهده سفارش‌های جدید',
            icon: (
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            ),
            color: 'from-blue-500 to-blue-600',
            onClick: () => navigate('/admin/orders')
        },
        {
            title: 'کد تخفیف',
            description: 'ایجاد کد تخفیف جدید',
            icon: (
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            ),
            color: 'from-orange-500 to-orange-600',
            onClick: () => navigate('/admin/discounts/create')
        },
        {
            title: 'کمپین جدید',
            description: 'ایجاد کمپین جدید',
            icon: (
                <svg className="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            ),
            color: 'from-purple-500 to-purple-600',
            onClick: () => navigate('/admin/campaigns/create')
        }
    ];

    if (loading) {
        return (
            <div className="max-w-6xl mx-auto">
                <div className="flex items-center justify-center min-h-96">
                    <div className="text-center">
                        <div className="w-12 h-12 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
                        <p className="text-gray-400">در حال بارگذاری...</p>
                    </div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="max-w-6xl mx-auto">
                <div className="flex items-center justify-center min-h-96">
                    <div className="text-center">
                        <div className="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg className="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p className="text-red-400 mb-4">{error}</p>
                        <button 
                            onClick={() => window.location.reload()}
                            className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors"
                        >
                            تلاش مجدد
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-6xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">داشبورد ادمین</h1>
                <p className="text-gray-400">خوش آمدید! مدیریت فروشگاه خود را از اینجا شروع کنید</p>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div className="bg-gradient-to-br from-blue-500/10 to-blue-600/10 backdrop-blur-xl rounded-xl border border-blue-500/20 p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-gray-400 text-sm">کل محصولات</p>
                            <p className="text-white text-2xl font-bold">{formatPrice(dashboardData.stats.total_products)}</p>
                            <p className="text-blue-400 text-xs mt-1">{formatPrice(dashboardData.stats.active_products)} فعال</p>
                        </div>
                        <div className="w-12 h-12 bg-blue-500/20 rounded-full flex items-center justify-center">
                            <svg className="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="bg-gradient-to-br from-green-500/10 to-green-600/10 backdrop-blur-xl rounded-xl border border-green-500/20 p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-gray-400 text-sm">کل سفارش‌ها</p>
                            <p className="text-white text-2xl font-bold">{formatPrice(dashboardData.stats.total_orders)}</p>
                            <p className="text-green-400 text-xs mt-1">{formatPrice(dashboardData.stats.delivered_orders)} تحویل شده</p>
                        </div>
                        <div className="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                            <svg className="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="bg-gradient-to-br from-purple-500/10 to-purple-600/10 backdrop-blur-xl rounded-xl border border-purple-500/20 p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-gray-400 text-sm">درآمد کل</p>
                            <p className="text-white text-2xl font-bold">{formatPrice(dashboardData.stats.total_revenue)} تومان</p>
                        </div>
                        <div className="w-12 h-12 bg-purple-500/20 rounded-full flex items-center justify-center">
                            <svg className="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="bg-gradient-to-br from-orange-500/10 to-orange-600/10 backdrop-blur-xl rounded-xl border border-orange-500/20 p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-gray-400 text-sm">سفارش‌های در انتظار</p>
                            <p className="text-white text-2xl font-bold">{formatPrice(dashboardData.stats.pending_orders)}</p>
                        </div>
                        <div className="w-12 h-12 bg-orange-500/20 rounded-full flex items-center justify-center">
                            <svg className="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {/* Additional Stats */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div className="bg-gradient-to-br from-cyan-500/10 to-cyan-600/10 backdrop-blur-xl rounded-xl border border-cyan-500/20 p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-gray-400 text-sm">کل مشتریان</p>
                            <p className="text-white text-xl font-bold">{formatPrice(dashboardData.stats.total_customers)}</p>
                        </div>
                        <div className="w-10 h-10 bg-cyan-500/20 rounded-full flex items-center justify-center">
                            <svg className="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="bg-gradient-to-br from-violet-500/10 to-violet-600/10 backdrop-blur-xl rounded-xl border border-violet-500/20 p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-gray-400 text-sm">کمپین‌های فعال</p>
                            <p className="text-white text-xl font-bold">{formatPrice(dashboardData.stats.active_campaigns)}</p>
                        </div>
                        <div className="w-10 h-10 bg-violet-500/20 rounded-full flex items-center justify-center">
                            <svg className="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div className="bg-gradient-to-br from-yellow-500/10 to-yellow-600/10 backdrop-blur-xl rounded-xl border border-yellow-500/20 p-6">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-gray-400 text-sm">کدهای تخفیف فعال</p>
                            <p className="text-white text-xl font-bold">{formatPrice(dashboardData.stats.active_discount_codes)}</p>
                        </div>
                        <div className="w-10 h-10 bg-yellow-500/20 rounded-full flex items-center justify-center">
                            <svg className="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {/* Quick Actions */}
            <div className="mb-8">
                <h2 className="text-2xl font-bold text-white mb-6">دسترسی سریع</h2>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    {quickActions.map((action, index) => (
                        <button
                            key={index}
                            onClick={action.onClick}
                            className={`bg-gradient-to-br ${action.color} hover:scale-105 transition-all duration-200 rounded-xl p-6 text-white text-left transform hover:shadow-lg`}
                        >
                            <div className="flex items-center justify-between mb-4">
                                <div className="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                                    {action.icon}
                                </div>
                            </div>
                            <h3 className="font-bold text-lg mb-2">{action.title}</h3>
                            <p className="text-white/80 text-sm">{action.description}</p>
                        </button>
                    ))}
                </div>
            </div>

            {/* Recent Orders */}
            <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-2xl font-bold text-white">سفارش‌های اخیر</h2>
                    <button 
                        onClick={() => navigate('/admin/orders')}
                        className="text-purple-400 hover:text-purple-300 text-sm font-medium"
                    >
                        مشاهده همه
                    </button>
                </div>
                
                {dashboardData.recent_orders.length > 0 ? (
                    <div className="space-y-4">
                        {dashboardData.recent_orders.map((order) => (
                            <div key={order.id} className="flex items-center justify-between p-4 bg-white/5 rounded-lg hover:bg-white/10 transition-colors">
                                <div className="flex items-center space-x-4 space-x-reverse">
                                    <div className="w-10 h-10 bg-blue-500/20 rounded-full flex items-center justify-center">
                                        <svg className="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p className="text-white font-medium">سفارش #{order.id}</p>
                                        <p className="text-gray-400 text-sm">{order.customer_name}</p>
                                        <p className="text-gray-400 text-xs">{formatDate(order.created_at)}</p>
                                    </div>
                                </div>
                                <div className="text-right">
                                    <p className="text-white font-medium">{formatPrice(order.total_amount)} تومان</p>
                                    <p className={`text-xs ${getStatusColor(order.status)}`}>{getStatusText(order.status)}</p>
                                    <p className="text-gray-400 text-xs">{order.items_count} آیتم</p>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-8">
                        <div className="w-16 h-16 bg-gray-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p className="text-gray-400">هنوز سفارشی ثبت نشده است</p>
                    </div>
                )}
            </div>

            {/* Top Products */}
            <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                <div className="flex items-center justify-between mb-6">
                    <h2 className="text-2xl font-bold text-white">پرفروش‌ترین محصولات</h2>
                    <button 
                        onClick={() => navigate('/admin/products')}
                        className="text-purple-400 hover:text-purple-300 text-sm font-medium"
                    >
                        مشاهده همه
                    </button>
                </div>
                
                {dashboardData.top_products.length > 0 ? (
                    <div className="space-y-4">
                        {dashboardData.top_products.map((product, index) => (
                            <div key={product.id} className="flex items-center justify-between p-4 bg-white/5 rounded-lg">
                                <div className="flex items-center space-x-4 space-x-reverse">
                                    <div className="w-8 h-8 bg-purple-500/20 rounded-full flex items-center justify-center">
                                        <span className="text-purple-400 font-bold text-sm">#{index + 1}</span>
                                    </div>
                                    <div>
                                        <p className="text-white font-medium">{product.title}</p>
                                        <p className="text-gray-400 text-sm">فروش: {formatPrice(product.total_sold)} عدد</p>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-8">
                        <div className="w-16 h-16 bg-gray-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <p className="text-gray-400">هنوز محصولی فروخته نشده است</p>
                    </div>
                )}
            </div>
        </div>
    );
}

export default AdminDashboard;