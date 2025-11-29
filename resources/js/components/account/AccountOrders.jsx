import React, { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { apiRequest } from '../../utils/sanctumAuth';

function AccountOrders() {
    const { user } = useAuth();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedOrder, setSelectedOrder] = useState(null);

    useEffect(() => {
        fetchOrders();
    }, []);

    const fetchOrders = async () => {
        try {
            const res = await apiRequest('/api/orders');
            if (res.ok) {
                const data = await res.json();
                setOrders(data.data || []);
            }
        } catch (error) {
            console.error('Error fetching orders:', error);
        } finally {
            setLoading(false);
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'pending': return 'text-yellow-400 bg-yellow-500/20 border-yellow-500/30';
            case 'confirmed': return 'text-emerald-400 bg-emerald-500/20 border-emerald-500/30';
            case 'processing': return 'text-blue-400 bg-blue-500/20 border-blue-500/30';
            case 'shipped': return 'text-purple-400 bg-purple-500/20 border-purple-500/30';
            case 'delivered': return 'text-green-400 bg-green-500/20 border-green-500/30';
            case 'cancelled': return 'text-red-400 bg-red-500/20 border-red-500/30';
            default: return 'text-gray-400 bg-gray-500/20 border-gray-500/30';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'pending': return 'در انتظار پردازش';
            case 'confirmed': return 'در حال آماده سازی';
            case 'processing': return 'در حال پردازش';
            case 'shipped': return 'ارسال شده';
            case 'delivered': return 'تحویل داده شده';
            case 'cancelled': return 'لغو شده';
            default: return status;
        }
    };

    const getProductImageUrl = (product) => {
        const path = product?.images?.[0]?.url || product?.images?.[0]?.path;
        if (!path) return null;
        if (/^https?:\/\//i.test(path)) return path;
        return path.startsWith('/storage/') ? path : `/storage/${path}`;
    };

    if (loading) {
        return (
            <div className="max-w-4xl mx-auto">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-white mb-2">سفارش‌های من</h1>
                    <p className="text-gray-400">تاریخچه سفارش‌های شما</p>
                </div>
                <div className="flex items-center justify-center py-20">
                    <div className="w-8 h-8 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-4xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">سفارش‌های من</h1>
                <p className="text-gray-400">تاریخچه سفارش‌های شما</p>
            </div>

            {/* Orders List */}
            {orders.length === 0 ? (
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-12 text-center">
                    <div className="w-20 h-20 bg-gray-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">هنوز سفارشی ثبت نکرده‌اید</h3>
                    <p className="text-gray-400 mb-6">اولین سفارش خود را ثبت کنید</p>
                    <button
                        onClick={() => window.location.href = '/'}
                        className="bg-gradient-to-r from-cherry-500 to-cherry-600 hover:from-cherry-600 hover:to-cherry-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg"
                    >
                        شروع خرید
                    </button>
                </div>
            ) : (
                <div className="space-y-4">
                    {orders.map((order) => (
                        <div
                            key={order.id}
                            className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 hover:shadow-3xl transition-all duration-200"
                        >
                            <div className="flex items-center justify-between mb-4">
                                <div className="flex items-center space-x-4 space-x-reverse">
                                    <div className="w-12 h-12 bg-cherry-500/20 rounded-xl flex items-center justify-center">
                                        <svg className="w-6 h-6 text-cherry-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-white font-semibold text-lg">سفارش #{order.order_number || order.id}</h3>
                                        <p className="text-gray-400 text-sm">
                                            {new Date(order.created_at).toLocaleDateString('fa-IR')}
                                        </p>
                                    </div>
                                </div>
                                <div className="text-left">
                                    <div className={`px-3 py-1 rounded-lg text-sm font-medium border ${getStatusColor(order.status)}`}>
                                        {getStatusText(order.status)}
                                    </div>
                                    <p className="text-white font-bold text-lg mt-2">
                                        {order.total_amount?.toLocaleString('fa-IR')} تومان
                                    </p>
                                </div>
                            </div>

                            {/* Order Items */}
                            <div className="space-y-3 mb-4">
                                {order.items?.map((item, index) => {
                                    const imgUrl = getProductImageUrl(item.product);
                                    return (
                                        <div key={index} className="flex items-center space-x-4 space-x-reverse bg-white/5 rounded-xl p-3">
                                            <div className="w-12 h-12 bg-gray-500/20 rounded-lg flex items-center justify-center overflow-hidden">
                                                {imgUrl ? (
                                                    <img src={imgUrl} alt={item.product?.title || 'Product'} className="w-12 h-12 object-cover" onError={(e)=>{ e.currentTarget.style.display='none'; }} />
                                                ) : (
                                                    <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                )}
                                            </div>
                                            <div className="flex-1">
                                                <h4 className="text-white font-medium">{item.product?.title || 'محصول'}</h4>
                                                <p className="text-gray-400 text-sm">
                                                    {item.quantity} عدد • {Number(item.unit_price || item.price || 0).toLocaleString('fa-IR')} تومان
                                                </p>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Order Actions */}
                            <div className="flex items-center justify-between pt-4 border-t border-white/10">
                                <div className="text-sm text-gray-400">
                                    <p>تعداد آیتم: {order.items?.length || 0}</p>
                                </div>
                                <button
                                    onClick={() => setSelectedOrder(order)}
                                    className="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                                >
                                    جزئیات بیشتر
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Order Detail Modal */}
            {selectedOrder && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div className="fixed inset-0 bg-black/50" onClick={() => setSelectedOrder(null)}></div>
                    <div className="relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-white/10 shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-6">
                                <h2 className="text-2xl font-bold text-white">جزئیات سفارش</h2>
                                <button
                                    onClick={() => setSelectedOrder(null)}
                                    className="text-gray-400 hover:text-white transition-colors"
                                >
                                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {/* Order Info */}
                            <div className="space-y-4 mb-6">
                                <div className="bg-white/5 rounded-xl p-4">
                                    <h3 className="text-white font-semibold mb-2">اطلاعات سفارش</h3>
                                    <div className="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span className="text-gray-400">شماره سفارش:</span>
                                            <span className="text-white mr-2">#{selectedOrder.order_number || selectedOrder.id}</span>
                                        </div>
                                        <div>
                                            <span className="text-gray-400">تاریخ:</span>
                                            <span className="text-white mr-2">
                                                {new Date(selectedOrder.created_at).toLocaleDateString('fa-IR')}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="text-gray-400">وضعیت:</span>
                                            <span className={`mr-2 px-2 py-1 rounded text-xs ${getStatusColor(selectedOrder.status)}`}>
                                                {getStatusText(selectedOrder.status)}
                                            </span>
                                        </div>
                                        <div>
                                            <span className="text-gray-400">مبلغ کل:</span>
                                            <span className="text-white mr-2">
                                                {Number(selectedOrder.total_amount || 0).toLocaleString('fa-IR')} تومان
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

export default AccountOrders;
