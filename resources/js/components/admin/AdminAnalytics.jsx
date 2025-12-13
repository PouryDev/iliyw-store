import React, { useState, useEffect } from 'react';
import { apiRequest } from '../../utils/sanctumAuth';
import ModernSelect from './ModernSelect';
import {
    LineChart,
    Line,
    BarChart,
    Bar,
    XAxis,
    YAxis,
    CartesianGrid,
    Tooltip,
    Legend,
    ResponsiveContainer,
} from 'recharts';

function AdminAnalytics() {
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeTab, setActiveTab] = useState('overview');
    const [filtersOpen, setFiltersOpen] = useState(false);

    // Filters
    const [dateRange, setDateRange] = useState('month');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [status, setStatus] = useState('');

    // Data
    const [overview, setOverview] = useState(null);
    const [salesByDay, setSalesByDay] = useState([]);
    const [salesByHour, setSalesByHour] = useState([]);
    const [topProducts, setTopProducts] = useState([]);
    const [topCategories, setTopCategories] = useState([]);
    const [campaigns, setCampaigns] = useState([]);
    const [heroSlides, setHeroSlides] = useState(null);

    useEffect(() => {
        loadData();
    }, [dateRange, startDate, endDate, status]);

    const buildQueryParams = () => {
        const params = new URLSearchParams();
        if (dateRange && dateRange !== 'custom') {
            params.append('date_range', dateRange);
        }
        if (startDate) {
            params.append('start_date', startDate);
        }
        if (endDate) {
            params.append('end_date', endDate);
        }
        if (status) {
            params.append('status', status);
        }
        return params.toString();
    };

    const loadData = async () => {
        try {
            setLoading(true);
            setError(null);

            const queryParams = buildQueryParams();
            const baseUrl = `/api/admin/analytics`;

            // Load all data in parallel
            const [
                overviewRes,
                salesByDayRes,
                salesByHourRes,
                topProductsRes,
                topCategoriesRes,
                campaignsRes,
                heroSlidesRes,
            ] = await Promise.all([
                apiRequest(`${baseUrl}?${queryParams}`),
                apiRequest(`${baseUrl}/sales-by-day?${queryParams}`),
                apiRequest(`${baseUrl}/sales-by-hour?${queryParams}`),
                apiRequest(`${baseUrl}/top-products?${queryParams}&limit=10`),
                apiRequest(`${baseUrl}/top-categories?${queryParams}&limit=10`),
                apiRequest(`${baseUrl}/campaigns?${queryParams}`),
                apiRequest(`${baseUrl}/hero-slides`),
            ]);

            if (overviewRes.ok) {
                const data = await overviewRes.json();
                if (data.success) setOverview(data.data);
            }

            if (salesByDayRes.ok) {
                const data = await salesByDayRes.json();
                if (data.success) setSalesByDay(data.data || []);
            }

            if (salesByHourRes.ok) {
                const data = await salesByHourRes.json();
                if (data.success) setSalesByHour(data.data || []);
            }

            if (topProductsRes.ok) {
                const data = await topProductsRes.json();
                if (data.success) setTopProducts(data.data || []);
            }

            if (topCategoriesRes.ok) {
                const data = await topCategoriesRes.json();
                if (data.success) setTopCategories(data.data || []);
            }

            if (campaignsRes.ok) {
                const data = await campaignsRes.json();
                if (data.success) setCampaigns(data.data || []);
            }

            if (heroSlidesRes.ok) {
                const data = await heroSlidesRes.json();
                if (data.success) setHeroSlides(data.data);
            }
        } catch (err) {
            console.error('Failed to load analytics data:', err);
            setError('خطا در بارگذاری اطلاعات');
        } finally {
            setLoading(false);
        }
    };

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
            });
        } catch {
            return dateString;
        }
    };

    const getStatusText = (status) => {
        const texts = {
            pending: 'در انتظار',
            confirmed: 'در حال آماده سازی',
            processing: 'در حال پردازش',
            shipped: 'ارسال شده',
            delivered: 'تحویل داده شده',
            cancelled: 'لغو شده',
        };
        return texts[status] || status;
    };

    if (loading) {
        return (
            <div className="w-full px-4">
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
            <div className="w-full px-4">
                <div className="flex items-center justify-center min-h-96">
                    <div className="text-center">
                        <div className="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg className="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p className="text-red-400 mb-4">{error}</p>
                        <button
                            onClick={loadData}
                            className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors"
                        >
                            تلاش مجدد
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    const tabs = [
        { id: 'overview', label: 'نمای کلی', icon: '📊' },
        { id: 'sales', label: 'فروش', icon: '💰' },
        { id: 'products', label: 'محصولات', icon: '📦' },
        { id: 'categories', label: 'دسته‌ها', icon: '🏷️' },
        { id: 'campaigns', label: 'کمپین‌ها', icon: '🎯' },
        { id: 'slides', label: 'اسلایدرها', icon: '🖼️' },
    ];

    return (
        <div className="w-full max-w-full min-w-0">
            {/* Header */}
            <div className="mb-4 sm:mb-6">
                <h1 className="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-1 sm:mb-2">تحلیل‌ها و آمار</h1>
                <p className="text-gray-400 text-sm sm:text-base">مشاهده آمار کامل فروشگاه</p>
            </div>

            {/* Filters - Mobile Collapsible */}
            <div className="mb-4 sm:mb-6">
                <button
                    onClick={() => setFiltersOpen(!filtersOpen)}
                    className="w-full bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-lg p-3 sm:p-4 flex items-center justify-between"
                >
                    <span className="text-white font-medium text-sm sm:text-base">فیلترها</span>
                    <svg
                        className={`w-5 h-5 text-gray-400 transition-transform ${filtersOpen ? 'rotate-180' : ''}`}
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {filtersOpen && (
                    <div className="mt-2 bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-lg p-4 space-y-3 w-full max-w-full min-w-0">
                        <div>
                            <label className="block text-xs sm:text-sm text-gray-400 mb-2">بازه زمانی</label>
                            <ModernSelect
                                value={dateRange}
                                onChange={(value) => {
                                    setDateRange(value);
                                    if (value !== 'custom') {
                                        setStartDate('');
                                        setEndDate('');
                                    }
                                }}
                                options={[
                                    { value: 'today', label: 'امروز' },
                                    { value: 'week', label: 'این هفته' },
                                    { value: 'month', label: 'این ماه' },
                                    { value: 'year', label: 'امسال' },
                                    { value: 'custom', label: 'سفارشی' }
                                ]}
                                placeholder="بازه زمانی را انتخاب کنید"
                            />
                        </div>

                        {dateRange === 'custom' && (
                            <>
                                <div>
                                    <label className="block text-xs sm:text-sm text-gray-400 mb-2">از تاریخ</label>
                                    <input
                                        type="date"
                                        value={startDate}
                                        onChange={(e) => setStartDate(e.target.value)}
                                        className="w-full max-w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500 box-border"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs sm:text-sm text-gray-400 mb-2">تا تاریخ</label>
                                    <input
                                        type="date"
                                        value={endDate}
                                        onChange={(e) => setEndDate(e.target.value)}
                                        className="w-full max-w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-purple-500 box-border"
                                    />
                                </div>
                            </>
                        )}

                        <div>
                            <label className="block text-xs sm:text-sm text-gray-400 mb-2">وضعیت سفارش</label>
                            <ModernSelect
                                value={status}
                                onChange={(value) => setStatus(value)}
                                options={[
                                    { value: '', label: 'همه' },
                                    { value: 'pending', label: 'در انتظار' },
                                    { value: 'confirmed', label: 'در حال آماده سازی' },
                                    { value: 'processing', label: 'در حال پردازش' },
                                    { value: 'shipped', label: 'ارسال شده' },
                                    { value: 'delivered', label: 'تحویل داده شده' },
                                    { value: 'cancelled', label: 'لغو شده' }
                                ]}
                                placeholder="وضعیت سفارش"
                            />
                        </div>
                    </div>
                )}
            </div>

            {/* Tabs - Scrollable on Mobile */}
            <div className="mb-4 sm:mb-6 w-full max-w-full min-w-0">
                <style>{`
                    .tabs-scroll::-webkit-scrollbar {
                        display: none;
                    }
                    .tabs-scroll {
                        -ms-overflow-style: none;
                        scrollbar-width: none;
                        touch-action: pan-x;
                    }
                `}</style>
                <div className="tabs-scroll flex overflow-x-auto overflow-y-hidden pb-2 space-x-2 sm:space-x-0 sm:flex-wrap sm:border-b sm:border-white/10 min-w-0 w-full">
                    {tabs.map((tab) => (
                        <button
                            key={tab.id}
                            onClick={() => setActiveTab(tab.id)}
                            className={`flex-shrink-0 min-w-fit px-4 py-2 sm:py-3 rounded-lg sm:rounded-none sm:rounded-t-lg font-medium text-xs sm:text-sm transition-colors whitespace-nowrap ${
                                activeTab === tab.id
                                    ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30 sm:border-b-2 sm:border-purple-400 sm:border-t-0 sm:border-l-0 sm:border-r-0'
                                    : 'text-gray-400 hover:text-white bg-white/5 sm:bg-transparent'
                            }`}
                        >
                            <span className="sm:hidden mr-1">{tab.icon}</span>
                            {tab.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Overview Tab */}
            {activeTab === 'overview' && overview && (
                <div className="space-y-4 sm:space-y-6 w-full max-w-full min-w-0">
                    {/* Stats Cards */}
                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 w-full max-w-full min-w-0">
                        <div className="bg-gradient-to-br from-blue-500/10 to-blue-600/10 backdrop-blur-xl rounded-xl border border-blue-500/20 p-4 sm:p-6">
                            <div className="flex flex-col">
                                <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">کل سفارش‌ها</p>
                                <p className="text-white text-lg sm:text-2xl font-bold">{formatPrice(overview.total_orders)}</p>
                            </div>
                        </div>

                        <div className="bg-gradient-to-br from-green-500/10 to-green-600/10 backdrop-blur-xl rounded-xl border border-green-500/20 p-4 sm:p-6">
                            <div className="flex flex-col">
                                <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">کل درآمد</p>
                                <p className="text-white text-lg sm:text-2xl font-bold">{formatPrice(overview.total_revenue)}</p>
                                <p className="text-green-400 text-xs mt-1">تومان</p>
                            </div>
                        </div>

                        <div className="bg-gradient-to-br from-purple-500/10 to-purple-600/10 backdrop-blur-xl rounded-xl border border-purple-500/20 p-4 sm:p-6">
                            <div className="flex flex-col">
                                <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">میانگین سفارش</p>
                                <p className="text-white text-lg sm:text-2xl font-bold">{formatPrice(overview.average_order)}</p>
                                <p className="text-purple-400 text-xs mt-1">تومان</p>
                            </div>
                        </div>

                        <div className="bg-gradient-to-br from-orange-500/10 to-orange-600/10 backdrop-blur-xl rounded-xl border border-orange-500/20 p-4 sm:p-6">
                            <div className="flex flex-col">
                                <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">کل آیتم‌ها</p>
                                <p className="text-white text-lg sm:text-2xl font-bold">{formatPrice(overview.total_items)}</p>
                            </div>
                        </div>
                    </div>

                    {/* Orders by Status */}
                    {overview.orders_by_status && Object.keys(overview.orders_by_status).length > 0 && (
                        <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6 w-full max-w-full min-w-0">
                            <h2 className="text-lg sm:text-xl font-bold text-white mb-3 sm:mb-4">سفارش‌ها بر اساس وضعیت</h2>
                            <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                                {Object.entries(overview.orders_by_status).map(([status, count]) => (
                                    <div key={status} className="bg-white/5 rounded-lg p-3 sm:p-4 text-center">
                                        <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">{getStatusText(status)}</p>
                                        <p className="text-white text-xl sm:text-2xl font-bold">{formatPrice(count)}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Sales Tab */}
            {activeTab === 'sales' && (
                <div className="space-y-4 sm:space-y-6 w-full max-w-full min-w-0">
                    {/* Sales by Day Chart */}
                    {salesByDay.length > 0 && (
                        <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6 w-full max-w-full min-w-0">
                            <h2 className="text-lg sm:text-xl font-bold text-white mb-4 sm:mb-6">فروش روزانه</h2>
                            <div className="w-full max-w-full min-w-0 overflow-x-auto" style={{ height: '250px' }}>
                                <ResponsiveContainer width="100%" height="100%" minWidth={300}>
                                    <LineChart data={salesByDay} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                                        <CartesianGrid strokeDasharray="3 3" stroke="#ffffff20" />
                                        <XAxis dataKey="date" stroke="#ffffff60" fontSize={12} />
                                        <YAxis stroke="#ffffff60" fontSize={12} />
                                        <Tooltip
                                            contentStyle={{
                                                backgroundColor: 'rgba(0, 0, 0, 0.9)',
                                                border: '1px solid rgba(255, 255, 255, 0.1)',
                                                borderRadius: '8px',
                                                fontSize: '12px',
                                            }}
                                        />
                                        <Legend wrapperStyle={{ fontSize: '12px' }} />
                                        <Line type="monotone" dataKey="revenue" stroke="#a855f7" strokeWidth={2} name="درآمد" />
                                        <Line type="monotone" dataKey="orders_count" stroke="#3b82f6" strokeWidth={2} name="سفارش" />
                                    </LineChart>
                                </ResponsiveContainer>
                            </div>
                        </div>
                    )}

                    {/* Sales by Hour */}
                    {salesByHour.length > 0 && (
                        <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6 w-full max-w-full min-w-0">
                            <h2 className="text-lg sm:text-xl font-bold text-white mb-4 sm:mb-6">فروش ساعتی</h2>
                            <div className="space-y-6 sm:space-y-8">
                                {salesByHour.map((dayData) => (
                                    <div key={dayData.date} className="mb-4 sm:mb-8 w-full max-w-full min-w-0">
                                        <h3 className="text-base sm:text-lg font-semibold text-white mb-3 sm:mb-4 text-center sm:text-right">
                                            {formatDate(dayData.date)}
                                        </h3>
                                        <div className="grid grid-cols-2 sm:grid-cols-1 gap-2 sm:gap-0 mb-3 sm:mb-0">
                                            <div className="bg-white/5 rounded-lg p-2 sm:hidden">
                                                <p className="text-gray-400 text-xs">کل درآمد</p>
                                                <p className="text-white font-bold text-sm">{formatPrice(dayData.total_revenue)} تومان</p>
                                            </div>
                                            <div className="bg-white/5 rounded-lg p-2 sm:hidden">
                                                <p className="text-gray-400 text-xs">کل سفارش</p>
                                                <p className="text-white font-bold text-sm">{formatPrice(dayData.total_orders)}</p>
                                            </div>
                                        </div>
                                        <p className="hidden sm:block text-gray-400 text-sm mb-3">
                                            کل: {formatPrice(dayData.total_revenue)} تومان ({formatPrice(dayData.total_orders)} سفارش)
                                        </p>
                                        <div className="w-full max-w-full min-w-0 overflow-x-auto" style={{ height: '200px' }}>
                                            <ResponsiveContainer width="100%" height="100%" minWidth={300}>
                                                <BarChart data={dayData.hourly} margin={{ top: 5, right: 10, left: 0, bottom: 5 }}>
                                                    <CartesianGrid strokeDasharray="3 3" stroke="#ffffff20" />
                                                    <XAxis dataKey="hour" stroke="#ffffff60" fontSize={11} />
                                                    <YAxis stroke="#ffffff60" fontSize={11} />
                                                    <Tooltip
                                                        contentStyle={{
                                                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                                                            border: '1px solid rgba(255, 255, 255, 0.1)',
                                                            borderRadius: '8px',
                                                            fontSize: '12px',
                                                        }}
                                                    />
                                                    <Legend wrapperStyle={{ fontSize: '11px' }} />
                                                    <Bar dataKey="revenue" fill="#a855f7" name="درآمد" />
                                                    <Bar dataKey="orders_count" fill="#3b82f6" name="سفارش" />
                                                </BarChart>
                                            </ResponsiveContainer>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            )}

            {/* Products Tab - Card Based for Mobile */}
            {activeTab === 'products' && (
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6 w-full max-w-full min-w-0">
                    <h2 className="text-lg sm:text-xl font-bold text-white mb-4 sm:mb-6">پرفروش‌ترین محصولات</h2>
                    {topProducts.length > 0 ? (
                        <div className="space-y-3 sm:space-y-4">
                            {topProducts.map((product) => (
                                <div key={product.product_id} className="bg-white/5 rounded-lg p-3 sm:p-4 hover:bg-white/10 transition-colors">
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-start space-x-3 space-x-reverse flex-1">
                                            <div className="w-8 h-8 sm:w-10 sm:h-10 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                                                <span className="text-purple-400 font-bold text-xs sm:text-sm">#{product.rank}</span>
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <p className="text-white font-medium text-sm sm:text-base mb-1 sm:mb-2 truncate">{product.title}</p>
                                                <div className="flex flex-wrap gap-2 sm:gap-3 text-xs sm:text-sm">
                                                    <span className="text-gray-400">تعداد: <span className="text-white">{formatPrice(product.total_quantity)}</span></span>
                                                    <span className="text-gray-400">سفارش: <span className="text-white">{formatPrice(product.orders_count)}</span></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="text-left mr-2 sm:mr-4 flex-shrink-0">
                                            <p className="text-green-400 font-medium text-sm sm:text-base">{formatPrice(product.total_revenue)}</p>
                                            <p className="text-gray-400 text-xs">تومان</p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-8">
                            <p className="text-gray-400 text-sm sm:text-base">داده‌ای یافت نشد</p>
                        </div>
                    )}
                </div>
            )}

            {/* Categories Tab - Card Based for Mobile */}
            {activeTab === 'categories' && (
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6 w-full max-w-full min-w-0">
                    <h2 className="text-lg sm:text-xl font-bold text-white mb-4 sm:mb-6">پرفروش‌ترین دسته‌بندی‌ها</h2>
                    {topCategories.length > 0 ? (
                        <div className="space-y-3 sm:space-y-4">
                            {topCategories.map((category) => (
                                <div key={category.category_id} className="bg-white/5 rounded-lg p-3 sm:p-4 hover:bg-white/10 transition-colors">
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-start space-x-3 space-x-reverse flex-1">
                                            <div className="w-8 h-8 sm:w-10 sm:h-10 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                                                <span className="text-purple-400 font-bold text-xs sm:text-sm">#{category.rank}</span>
                                            </div>
                                            <div className="flex-1 min-w-0">
                                                <p className="text-white font-medium text-sm sm:text-base mb-1 sm:mb-2">{category.name}</p>
                                                <div className="flex flex-wrap gap-2 sm:gap-3 text-xs sm:text-sm">
                                                    <span className="text-gray-400">تعداد: <span className="text-white">{formatPrice(category.total_quantity)}</span></span>
                                                    <span className="text-gray-400">سفارش: <span className="text-white">{formatPrice(category.orders_count)}</span></span>
                                                    <span className="text-gray-400">محصولات: <span className="text-white">{formatPrice(category.products_count)}</span></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div className="text-left mr-2 sm:mr-4 flex-shrink-0">
                                            <p className="text-green-400 font-medium text-sm sm:text-base">{formatPrice(category.total_revenue)}</p>
                                            <p className="text-gray-400 text-xs">تومان</p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-8">
                            <p className="text-gray-400 text-sm sm:text-base">داده‌ای یافت نشد</p>
                        </div>
                    )}
                </div>
            )}

            {/* Campaigns Tab */}
            {activeTab === 'campaigns' && (
                <div className="space-y-4 sm:space-y-6 w-full max-w-full min-w-0">
                    {campaigns.length > 0 ? (
                        campaigns.map((campaign) => (
                            <div key={campaign.campaign_id} className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6">
                                <div className="flex items-center justify-between mb-3 sm:mb-4 flex-wrap gap-2">
                                    <h3 className="text-base sm:text-xl font-bold text-white">{campaign.name}</h3>
                                    <span className="px-2 sm:px-3 py-1 bg-purple-500/20 text-purple-400 rounded-lg text-xs sm:text-sm">
                                        {campaign.type === 'percentage' ? `${campaign.discount_value}%` : `${formatPrice(campaign.discount_value)} تومان`}
                                    </span>
                                </div>
                                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                                    <div>
                                        <p className="text-gray-400 text-xs sm:text-sm mb-1">تعداد فروخته شده</p>
                                        <p className="text-white text-base sm:text-lg font-bold">{formatPrice(campaign.total_quantity)}</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-xs sm:text-sm mb-1">کل تخفیف</p>
                                        <p className="text-orange-400 text-base sm:text-lg font-bold">{formatPrice(campaign.total_discount)}</p>
                                        <p className="text-gray-400 text-xs">تومان</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-xs sm:text-sm mb-1">درآمد</p>
                                        <p className="text-green-400 text-base sm:text-lg font-bold">{formatPrice(campaign.total_revenue)}</p>
                                        <p className="text-gray-400 text-xs">تومان</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-xs sm:text-sm mb-1">تعداد فروش</p>
                                        <p className="text-white text-base sm:text-lg font-bold">{formatPrice(campaign.sales_count)}</p>
                                    </div>
                                </div>
                            </div>
                        ))
                    ) : (
                        <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6 text-center">
                            <p className="text-gray-400 text-sm sm:text-base">داده‌ای یافت نشد</p>
                        </div>
                    )}
                </div>
            )}

            {/* Hero Slides Tab */}
            {activeTab === 'slides' && heroSlides && (
                <div className="space-y-4 sm:space-y-6 w-full max-w-full min-w-0">
                    {/* Summary Cards */}
                    <div className="grid grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                        <div className="bg-gradient-to-br from-blue-500/10 to-blue-600/10 backdrop-blur-xl rounded-xl border border-blue-500/20 p-3 sm:p-6">
                            <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">کل اسلایدرها</p>
                            <p className="text-white text-lg sm:text-2xl font-bold">{formatPrice(heroSlides.total_slides)}</p>
                        </div>
                        <div className="bg-gradient-to-br from-green-500/10 to-green-600/10 backdrop-blur-xl rounded-xl border border-green-500/20 p-3 sm:p-6">
                            <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">فعال</p>
                            <p className="text-white text-lg sm:text-2xl font-bold">{formatPrice(heroSlides.active_slides)}</p>
                        </div>
                        <div className="bg-gradient-to-br from-purple-500/10 to-purple-600/10 backdrop-blur-xl rounded-xl border border-purple-500/20 p-3 sm:p-6">
                            <p className="text-gray-400 text-xs sm:text-sm mb-1 sm:mb-2">کل کلیک‌ها</p>
                            <p className="text-white text-lg sm:text-2xl font-bold">{formatPrice(heroSlides.total_clicks)}</p>
                        </div>
                    </div>

                    {/* Slides by Link Type */}
                    {heroSlides.slides_by_link_type && heroSlides.slides_by_link_type.length > 0 && (
                        <div className="space-y-4 sm:space-y-6">
                            {heroSlides.slides_by_link_type.map((group) => (
                                <div key={group.type} className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl sm:rounded-2xl border border-white/10 shadow-lg sm:shadow-2xl p-4 sm:p-6">
                                    <h3 className="text-base sm:text-xl font-bold text-white mb-3 sm:mb-4">
                                        {group.type === 'product' && 'لینک به محصول'}
                                        {group.type === 'category' && 'لینک به دسته‌بندی'}
                                        {group.type === 'campaign' && 'لینک به کمپین'}
                                        {group.type === 'custom' && 'لینک سفارشی'}
                                        <span className="text-gray-400 text-sm sm:text-base font-normal mr-2">
                                            ({group.count} اسلایدر - {formatPrice(group.total_clicks)} کلیک)
                                        </span>
                                    </h3>
                                    <div className="space-y-2 sm:space-y-3">
                                        {group.slides.map((slide) => (
                                            <div key={slide.id} className="bg-white/5 rounded-lg p-3 sm:p-4">
                                                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                                                    <div className="flex-1">
                                                        <p className="text-white font-medium text-sm sm:text-base mb-1">{slide.title || 'بدون عنوان'}</p>
                                                        {slide.link_info && (
                                                            <p className="text-gray-400 text-xs sm:text-sm">
                                                                {slide.link_info.type === 'product' && `محصول: ${slide.link_info.title}`}
                                                                {slide.link_info.type === 'category' && `دسته: ${slide.link_info.name}`}
                                                                {slide.link_info.type === 'campaign' && `کمپین: ${slide.link_info.name}`}
                                                            </p>
                                                        )}
                                                        {slide.custom_url && (
                                                            <p className="text-gray-400 text-xs sm:text-sm mt-1 break-all">URL: {slide.custom_url}</p>
                                                        )}
                                                    </div>
                                                    <div className="flex items-center justify-between sm:flex-col sm:items-end sm:text-left gap-2 sm:gap-1">
                                                        <p className="text-purple-400 font-bold text-sm sm:text-base">{formatPrice(slide.click_count)} کلیک</p>
                                                        <span className={`text-xs px-2 py-1 rounded ${slide.is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'}`}>
                                                            {slide.is_active ? 'فعال' : 'غیرفعال'}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

export default AdminAnalytics;
