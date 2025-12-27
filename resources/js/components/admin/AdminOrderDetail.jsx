import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { apiRequest } from '../../utils/sanctumAuth';

function AdminOrderDetail() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchOrderDetails();
    }, [id]);

    const fetchOrderDetails = async () => {
        try {
            setLoading(true);
            const response = await apiRequest(`/api/admin/orders/${id}`);
            
            if (response.ok) {
                const data = await response.json();
                setOrder(data.data);
            } else {
                setError('خطا در بارگذاری جزئیات سفارش');
            }
        } catch (err) {
            setError('خطا در ارتباط با سرور');
        } finally {
            setLoading(false);
        }
    };

    const updateOrderStatus = async (newStatus) => {
        try {
            const response = await apiRequest(`/api/admin/orders/${id}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            });

            if (response.ok) {
                setOrder(prev => ({ ...prev, status: newStatus }));
            } else {
                alert('خطا در به‌روزرسانی وضعیت سفارش');
            }
        } catch (err) {
            alert('خطا در ارتباط با سرور');
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'pending': return 'text-yellow-400';
            case 'confirmed': return 'text-emerald-400';
            case 'processing': return 'text-blue-400';
            case 'shipped': return 'text-purple-400';
            case 'delivered': return 'text-green-400';
            case 'cancelled': return 'text-red-400';
            default: return 'text-gray-400';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'pending': return 'در انتظار';
            case 'confirmed': return 'در حال آماده سازی';
            case 'processing': return 'در حال پردازش';
            case 'shipped': return 'ارسال شده';
            case 'delivered': return 'تحویل داده شده';
            case 'cancelled': return 'لغو شده';
            default: return status;
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
                <div className="sticky top-0 z-50 bg-gray-900/95 backdrop-blur-md border-b border-white/10 px-4 py-3">
                    <div className="flex items-center justify-between">
                        <button
                            onClick={() => navigate('/admin/orders')}
                            className="flex items-center gap-2 text-white/80 hover:text-white transition-colors"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span className="text-sm">بازگشت</span>
                        </button>
                        <h1 className="text-lg font-bold text-white">در حال بارگذاری...</h1>
                        <div className="w-16"></div>
                    </div>
                </div>
                <div className="flex items-center justify-center h-64">
                    <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
                <div className="sticky top-0 z-50 bg-gray-900/95 backdrop-blur-md border-b border-white/10 px-4 py-3">
                    <div className="flex items-center justify-between">
                        <button
                            onClick={() => navigate('/admin/orders')}
                            className="flex items-center gap-2 text-white/80 hover:text-white transition-colors"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span className="text-sm">بازگشت</span>
                        </button>
                        <h1 className="text-lg font-bold text-white">خطا</h1>
                        <div className="w-16"></div>
                    </div>
                </div>
                <div className="p-4">
                    <div className="bg-red-500/10 border border-red-500/20 rounded-xl p-6 text-center">
                        <p className="text-red-400 mb-4">{error}</p>
                        <button
                            onClick={() => navigate('/admin/orders')}
                            className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors text-sm"
                        >
                            بازگشت به لیست سفارشات
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    if (!order) {
        return (
            <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
                <div className="sticky top-0 z-50 bg-gray-900/95 backdrop-blur-md border-b border-white/10 px-4 py-3">
                    <div className="flex items-center justify-between">
                        <button
                            onClick={() => navigate('/admin/orders')}
                            className="flex items-center gap-2 text-white/80 hover:text-white transition-colors"
                        >
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                            </svg>
                            <span className="text-sm">بازگشت</span>
                        </button>
                        <h1 className="text-lg font-bold text-white">سفارش یافت نشد</h1>
                        <div className="w-16"></div>
                    </div>
                </div>
                <div className="p-4">
                    <div className="bg-gray-500/10 border border-gray-500/20 rounded-xl p-6 text-center">
                        <p className="text-gray-400 mb-4">سفارش یافت نشد</p>
                        <button
                            onClick={() => navigate('/admin/orders')}
                            className="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors text-sm"
                        >
                            بازگشت به لیست سفارشات
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
            {/* Mobile Header */}
            <div className="sticky top-0 z-50 bg-gray-900/95 backdrop-blur-md border-b border-white/10 px-4 py-3">
                <div className="flex items-center justify-between">
                    <button
                        onClick={() => navigate('/admin/orders')}
                        className="flex items-center gap-2 text-white/80 hover:text-white transition-colors"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span className="text-sm">بازگشت</span>
                    </button>
                    <h1 className="text-lg font-bold text-white">
                        سفارش #{order.id}
                    </h1>
                    <div className="w-16"></div> {/* Spacer for centering */}
                </div>
            </div>

            <div className="p-4 space-y-4">
                {/* Order Status Card */}
                <div className="bg-white/5 border border-white/10 rounded-xl p-4">
                    <div className="flex items-center justify-between mb-3">
                        <h2 className="text-base font-semibold text-white">وضعیت سفارش</h2>
                        <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(order.status)} bg-current/10`}>
                            {getStatusText(order.status)}
                        </span>
                    </div>
                    <div className="text-sm text-gray-400">
                        تاریخ: {new Date(order.created_at).toLocaleDateString('fa-IR')}
                    </div>
                </div>

                {/* Customer Information */}
                <div className="bg-white/5 border border-white/10 rounded-xl p-4">
                    <h2 className="text-base font-semibold text-white mb-3">اطلاعات مشتری</h2>
                    <div className="space-y-2">
                        <div className="flex justify-between">
                            <span className="text-gray-400 text-sm">نام:</span>
                            <span className="text-white text-sm">{order.customer_name}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-gray-400 text-sm">شماره تماس:</span>
                            <span className="text-white text-sm">{order.customer_phone}</span>
                        </div>
                        <div className="pt-2 border-t border-white/10">
                            <span className="text-gray-400 text-sm block mb-1">آدرس:</span>
                            <span className="text-white text-sm leading-relaxed">{order.customer_address}</span>
                        </div>
                        {order.user && (
                            <div className="flex justify-between">
                                <span className="text-gray-400 text-sm">کاربر:</span>
                                <span className="text-white text-sm">{order.user.name}</span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Order Summary */}
                <div className="bg-white/5 border border-white/10 rounded-xl p-4">
                    <h2 className="text-base font-semibold text-white mb-3">خلاصه سفارش</h2>
                    <div className="space-y-2">
                        <div className="flex justify-between">
                            <span className="text-gray-400 text-sm">مبلغ اصلی:</span>
                            <span className="text-white text-sm">{order.original_amount?.toLocaleString('fa-IR')} تومان</span>
                        </div>
                        {order.campaign_discount_amount > 0 && (
                            <div className="flex justify-between">
                                <span className="text-gray-400 text-sm">تخفیف کمپین:</span>
                                <span className="text-green-400 text-sm">-{order.campaign_discount_amount.toLocaleString('fa-IR')} تومان</span>
                            </div>
                        )}
                        {order.discount_amount > 0 && (
                            <div className="flex justify-between">
                                <span className="text-gray-400 text-sm">تخفیف کد:</span>
                                <span className="text-green-400 text-sm">-{order.discount_amount.toLocaleString('fa-IR')} تومان</span>
                            </div>
                        )}
                        <div className="flex justify-between">
                            <span className="text-gray-400 text-sm">هزینه ارسال:</span>
                            <span className="text-white text-sm">{order.delivery_fee?.toLocaleString('fa-IR')} تومان</span>
                        </div>
                        {order.delivery_method && (
                            <div className="flex justify-between">
                                <span className="text-gray-400 text-sm">روش ارسال:</span>
                                <span className="text-white text-sm">{order.delivery_method.title}</span>
                            </div>
                        )}
                        <div className="border-t border-white/10 pt-2 mt-2">
                            <div className="flex justify-between text-base font-semibold">
                                <span className="text-white">مبلغ پرداختی:</span>
                                <span className="text-white">{order.final_amount?.toLocaleString('fa-IR')} تومان</span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Order Items */}
                <div className="bg-white/5 border border-white/10 rounded-xl p-4">
                    <h2 className="text-base font-semibold text-white mb-3">محصولات سفارش</h2>
                    <div className="space-y-3">
                        {order.items?.map((item) => (
                            <div key={item.id} className="bg-white/5 rounded-lg p-3">
                                <div className="flex gap-3">
                                    {item.product?.images?.[0] && (
                                        <img
                                            src={item.product.images[0].url}
                                            alt={item.product.name}
                                            className="w-12 h-12 object-cover rounded-lg flex-shrink-0"
                                        />
                                    )}
                                    <div className="flex-1 min-w-0">
                                        <h3 className="text-white font-medium text-sm leading-tight mb-1">
                                            {item.product?.name}
                                        </h3>
                                        <div className="text-xs text-gray-400 mb-2">
                                            {item.color && <span>رنگ: {item.color.name}</span>}
                                            {item.size && <span className="mr-2">سایز: {item.size.name}</span>}
                                        </div>
                                        <div className="flex justify-between items-center">
                                            <span className="text-xs text-gray-400">
                                                {item.quantity} × {item.price?.toLocaleString('fa-IR')} تومان
                                            </span>
                                            <span className="text-sm font-semibold text-white">
                                                {(item.quantity * item.price)?.toLocaleString('fa-IR')} تومان
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Receipt */}
                {order.receipt_path && (
                    <div className="bg-white/5 border border-white/10 rounded-xl p-4">
                        <h2 className="text-base font-semibold text-white mb-3">رسید پرداخت</h2>
                        <div className="flex justify-center">
                            <img
                                src={`/storage/${order.receipt_path}`}
                                alt="رسید پرداخت"
                                className="max-w-full h-auto rounded-lg"
                            />
                        </div>
                    </div>
                )}

                {/* Status Actions */}
                <div className="bg-white/5 border border-white/10 rounded-xl p-4">
                    <h2 className="text-base font-semibold text-white mb-3">تغییر وضعیت سفارش</h2>
                    <div className="grid grid-cols-2 gap-2">
                        {order.status === 'pending' && (
                            <button
                                onClick={() => updateOrderStatus('processing')}
                                className="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg transition-colors text-sm font-medium"
                            >
                                تایید سفارش
                            </button>
                        )}
                        {order.status === 'processing' && (
                            <button
                                onClick={() => updateOrderStatus('shipped')}
                                className="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg transition-colors text-sm font-medium"
                            >
                                ارسال سفارش
                            </button>
                        )}
                        {order.status === 'confirmed' && (
                            <button
                                onClick={() => updateOrderStatus('shipped')}
                                className="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg transition-colors text-sm font-medium"
                            >
                                ارسال سفارش
                            </button>
                        )}
                        {order.status === 'shipped' && (
                            <button
                                onClick={() => updateOrderStatus('delivered')}
                                className="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition-colors text-sm font-medium"
                            >
                                تحویل داده شده
                            </button>
                        )}
                        {(order.status === 'pending' || order.status === 'processing' || order.status === 'confirmed') && (
                            <button
                                onClick={() => updateOrderStatus('cancelled')}
                                className="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition-colors text-sm font-medium"
                            >
                                لغو سفارش
                            </button>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default AdminOrderDetail;
