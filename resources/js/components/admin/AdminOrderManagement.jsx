import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import ModernSelect from './ModernSelect';
import { adminApiRequest } from '../../utils/adminApi';

function AdminOrderManagement() {
    const navigate = useNavigate();
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filterStatus, setFilterStatus] = useState('all');

    useEffect(() => {
        const loadOrders = async () => {
            try {
                setLoading(true);
                const res = await adminApiRequest('/orders');
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        setOrders(data.data);
                    }
                }
            } catch (error) {
                console.error('Failed to load orders:', error);
            } finally {
                setLoading(false);
            }
        };

        loadOrders();
    }, []);

    const formatPrice = (value) => {
        try { 
            return Number(value || 0).toLocaleString('fa-IR'); 
        } catch { 
            return value || '0'; 
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'pending': return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
            case 'confirmed': return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
            case 'processing': return 'bg-blue-500/20 text-blue-400 border-blue-500/30';
            case 'shipped': return 'bg-purple-500/20 text-purple-400 border-purple-500/30';
            case 'delivered': return 'bg-green-500/20 text-green-400 border-green-500/30';
            case 'cancelled': return 'bg-red-500/20 text-red-400 border-red-500/30';
            default: return 'bg-gray-500/20 text-gray-400 border-gray-500/30';
        }
    };

    const getStatusText = (status) => {
        switch (status) {
            case 'pending': return 'در انتظار';
            case 'confirmed': return 'تایید شده';
            case 'processing': return 'در حال پردازش';
            case 'shipped': return 'ارسال شده';
            case 'delivered': return 'تحویل داده شده';
            case 'cancelled': return 'لغو شده';
            default: return status;
        }
    };

    const updateOrderStatus = async (orderId, newStatus) => {
        try {
            const res = await adminApiRequest(`/orders/${orderId}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: newStatus })
            });

            if (res.ok) {
                setOrders(orders.map(order => 
                    order.id === orderId 
                        ? { ...order, status: newStatus }
                        : order
                ));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'وضعیت سفارش به‌روزرسانی شد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to update order status:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در به‌روزرسانی وضعیت' } 
            }));
        }
    };

    const filteredOrders = orders.filter(order => 
        filterStatus === 'all' || order.status === filterStatus
    );

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

    return (
        <div className="max-w-6xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-white mb-2">مدیریت سفارش‌ها</h1>
                        <p className="text-gray-400">مشاهده و مدیریت سفارش‌های مشتریان</p>
                    </div>
                </div>
            </div>

            {/* Filter */}
            <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 p-6 mb-8">
                <div className="flex flex-col sm:flex-row gap-4">
                    <div className="sm:w-48">
                        <ModernSelect
                            value={filterStatus}
                            onChange={(value) => setFilterStatus(value)}
                            options={[
                                { value: 'all', label: 'همه سفارش‌ها' },
                                { value: 'pending', label: 'در انتظار' },
                                { value: 'processing', label: 'در حال پردازش' },
                                { value: 'shipped', label: 'ارسال شده' },
                                { value: 'delivered', label: 'تحویل داده شده' },
                                { value: 'cancelled', label: 'لغو شده' }
                            ]}
                            placeholder="فیلتر وضعیت"
                        />
                    </div>
                </div>
            </div>

            {/* Orders List */}
            <div className="space-y-6">
                {filteredOrders.map((order) => (
                    <div key={order.id} className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-2xl p-6">
                        <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            {/* Order Info */}
                            <div className="flex-1">
                                <div className="flex items-center gap-4 mb-4">
                                    <h3 className="text-white font-bold text-xl">سفارش #{order.id}</h3>
                                    <span className={`px-3 py-1 rounded-full text-sm font-medium border ${getStatusColor(order.status)}`}>
                                        {getStatusText(order.status)}
                                    </span>
                                </div>

                                {/* Customer Info */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p className="text-gray-400 text-sm">مشتری</p>
                                        <p className="text-white font-medium">{order.user?.name || 'نامشخص'}</p>
                                        <p className="text-gray-400 text-sm">{order.user?.phone || order.user?.email}</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">تاریخ سفارش</p>
                                        <p className="text-white font-medium">
                                            {new Date(order.created_at).toLocaleDateString('fa-IR')}
                                        </p>
                                    </div>
                                </div>

                                {/* Order Items */}
                                <div className="mb-4">
                                    <p className="text-gray-400 text-sm mb-2">محصولات سفارش:</p>
                                    <div className="space-y-2">
                                        {order.items?.map((item, index) => (
                                            <div key={index} className="bg-white/5 rounded-lg p-3 flex items-center justify-between">
                                                <div className="flex items-center space-x-3 space-x-reverse">
                                                    {item.product?.images && item.product.images.length > 0 ? (
                                                        <img 
                                                            src={item.product.images[0].url} 
                                                            alt={item.product.title}
                                                            className="w-12 h-12 object-cover rounded"
                                                        />
                                                    ) : (
                                                        <div className="w-12 h-12 bg-gray-600 rounded flex items-center justify-center text-sm">📦</div>
                                                    )}
                                                    <div>
                                                        <p className="text-white font-medium">{item.product?.title}</p>
                                                        <p className="text-gray-400 text-sm">
                                                            تعداد: {item.quantity} • 
                                                            {item.color && ` رنگ: ${item.color.name}`} • 
                                                            {item.size && ` سایز: ${item.size.name}`}
                                                        </p>
                                                    </div>
                                                </div>
                                                <p className="text-purple-400 font-medium">
                                                    {formatPrice(item.price)} تومان
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Order Summary */}
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p className="text-gray-400 text-sm">مبلغ کل</p>
                                        <p className="text-white font-bold text-lg">{formatPrice(order.total_amount)} تومان</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">هزینه ارسال</p>
                                        <p className="text-white font-medium">{formatPrice(order.delivery_fee || 0)} تومان</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">روش ارسال</p>
                                        <p className="text-white font-medium">{order.delivery_method?.name || 'نامشخص'}</p>
                                    </div>
                                </div>

                                {/* Delivery Address */}
                                {order.delivery_address && (
                                    <div className="mb-4">
                                        <p className="text-gray-400 text-sm mb-2">آدرس تحویل:</p>
                                        <div className="bg-white/5 rounded-lg p-3">
                                            <p className="text-white">{order.delivery_address.address}</p>
                                            <p className="text-gray-400 text-sm">
                                                {order.delivery_address.city}، {order.delivery_address.province}
                                            </p>
                                        </div>
                                    </div>
                                )}

                                {/* Payment Info */}
                                {order.payment_receipt && (
                                    <div className="mb-4">
                                        <p className="text-gray-400 text-sm mb-2">فیش واریزی:</p>
                                        <div className="bg-white/5 rounded-lg p-3">
                                            <img 
                                                src={order.payment_receipt} 
                                                alt="Payment Receipt"
                                                className="w-32 h-32 object-cover rounded cursor-pointer hover:scale-105 transition-transform"
                                                onClick={() => window.open(order.payment_receipt, '_blank')}
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Actions */}
                            <div className="flex flex-col gap-2">
                                <button
                                    onClick={() => navigate(`/admin/orders/${order.id}`)}
                                    className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                >
                                    مشاهده جزئیات
                                </button>
                                
                                {order.status === 'pending' && (
                                    <button
                                        onClick={() => updateOrderStatus(order.id, 'processing')}
                                        className="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                    >
                                        تایید سفارش
                                    </button>
                                )}
                                
                                {order.status === 'processing' && (
                                    <button
                                        onClick={() => updateOrderStatus(order.id, 'shipped')}
                                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                    >
                                        ارسال سفارش
                                    </button>
                                )}
                                
                                {order.status === 'shipped' && (
                                    <button
                                        onClick={() => updateOrderStatus(order.id, 'delivered')}
                                        className="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                    >
                                        تحویل داده شد
                                    </button>
                                )}
                                
                                {(order.status === 'pending' || order.status === 'processing') && (
                                    <button
                                        onClick={() => updateOrderStatus(order.id, 'cancelled')}
                                        className="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                    >
                                        لغو سفارش
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Empty State */}
            {filteredOrders.length === 0 && !loading && (
                <div className="text-center py-12">
                    <div className="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">سفارشی یافت نشد</h3>
                    <p className="text-gray-400">
                        {filterStatus !== 'all' 
                            ? 'هیچ سفارشی با وضعیت انتخابی یافت نشد' 
                            : 'هنوز سفارشی ثبت نشده است'
                        }
                    </p>
                </div>
            )}
        </div>
    );
}

export default AdminOrderManagement;
