import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useSeo } from '../hooks/useSeo';
import LoadingSpinner from './LoadingSpinner';

function AccountOrderDetail() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchOrder();
    }, [id]);

    useSeo({
        title: `سفارش #${id} - فروشگاه ایلی استور`,
        description: 'جزئیات سفارش',
        canonical: window.location.origin + `/account/orders/${id}`
    });

    const fetchOrder = async () => {
        setLoading(true);
        try {
            const res = await fetch(`/api/account/orders/${id}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            setOrder(data.order);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    const formatPrice = (v) => {
        try { return Number(v || 0).toLocaleString('fa-IR'); } catch { return v; }
    };

    const getStatusBadge = (status) => {
        const badges = {
            pending: { text: 'در انتظار پرداخت', class: 'bg-yellow-500/20 text-yellow-300', icon: '⏳' },
            confirmed: { text: 'تایید شده', class: 'bg-emerald-500/20 text-emerald-300', icon: '✓' },
            paid: { text: 'پرداخت شده', class: 'bg-blue-500/20 text-blue-300', icon: '✓' },
            processing: { text: 'در حال پردازش', class: 'bg-purple-500/20 text-purple-300', icon: '🔄' },
            shipped: { text: 'ارسال شده', class: 'bg-cyan-500/20 text-cyan-300', icon: '📦' },
            delivered: { text: 'تحویل داده شده', class: 'bg-green-500/20 text-green-300', icon: '✓' },
            cancelled: { text: 'لغو شده', class: 'bg-red-500/20 text-red-300', icon: '✕' },
        };
        return badges[status] || badges.pending;
    };

    if (loading) {
        return (
            <div className="flex justify-center py-12">
                <LoadingSpinner />
            </div>
        );
    }

    if (!order) {
        return (
            <div className="glass-card rounded-2xl p-8 border border-white/10 text-center">
                <div className="text-6xl mb-4">❌</div>
                <h3 className="text-xl font-semibold text-white mb-2">سفارش یافت نشد</h3>
                <button onClick={() => navigate(-1)} className="text-cherry-400 hover:text-cherry-300">
                    بازگشت
                </button>
            </div>
        );
    }

    const badge = getStatusBadge(order.status);

    return (
        <div className="space-y-4">
            {/* Header */}
            <div className="flex items-center justify-between">
                <button
                    onClick={() => navigate(-1)}
                    className="flex items-center gap-1 text-cherry-400 hover:text-cherry-300 text-sm"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/></svg>
                    بازگشت
                </button>
            </div>

            {/* Order Info Card */}
            <div className="glass-card rounded-2xl p-5 border border-white/10">
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                    <div>
                        <h2 className="text-2xl font-bold text-white mb-1">سفارش #{order.id}</h2>
                        <p className="text-sm text-gray-400">
                            {new Date(order.created_at).toLocaleDateString('fa-IR', { 
                                year: 'numeric', month: 'long', day: 'numeric',
                                hour: '2-digit', minute: '2-digit'
                            })}
                        </p>
                    </div>
                    <div className="flex flex-col items-start md:items-end gap-2">
                        <span className={`inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold ${badge.class}`}>
                            <span>{badge.icon}</span>
                            <span>{badge.text}</span>
                        </span>
                    </div>
                </div>

                {/* Progress Bar */}
                <div className="flex items-center justify-between mb-2">
                    {['pending', 'paid', 'processing', 'shipped', 'delivered'].map((s, i) => {
                        const statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
                        const currentIndex = statuses.indexOf(order.status);
                        const isActive = i <= currentIndex;
                        return (
                            <div key={s} className="flex-1 flex items-center">
                                <div className={`w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold ${
                                    isActive ? 'bg-cherry-600 text-white' : 'bg-white/10 text-gray-500'
                                }`}>
                                    {i + 1}
                                </div>
                                {i < 4 && (
                                    <div className={`flex-1 h-1 mx-1 ${isActive ? 'bg-cherry-600' : 'bg-white/10'}`} />
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>

            {/* Order Items */}
            <div className="glass-card rounded-2xl p-5 border border-white/10">
                <h3 className="text-lg font-bold text-white mb-4">محصولات سفارش</h3>
                <div className="space-y-3">
                    {order.items?.map((item) => (
                        <div key={item.id} className="flex items-center gap-4 p-3 rounded-lg bg-white/5">
                            <img 
                                src={item.product_image || '/images/placeholder.jpg'} 
                                alt={item.product_title}
                                className="w-20 h-20 rounded-lg object-cover"
                                onError={(e) => {
                                    const img = e.currentTarget;
                                    if (img.src.includes('/images/placeholder.jpg') || img.dataset.placeholderTried === 'true') {
                                        img.style.display = 'none';
                                        return;
                                    }
                                    img.dataset.placeholderTried = 'true';
                                    img.src = '/images/placeholder.jpg';
                                }}
                            />
                            <div className="flex-1 min-w-0">
                                <h4 className="text-white font-semibold mb-1">{item.product_title}</h4>
                                {item.color_name && (
                                    <p className="text-xs text-gray-400">رنگ: {item.color_name}</p>
                                )}
                                {item.size_name && (
                                    <p className="text-xs text-gray-400">سایز: {item.size_name}</p>
                                )}
                                <p className="text-xs text-gray-400 mt-1">تعداد: {formatPrice(item.quantity)}</p>
                            </div>
                            <div className="text-left">
                                <div className="text-cherry-400 font-bold">
                                    {formatPrice(item.unit_price)} تومان
                                </div>
                                <div className="text-xs text-gray-400 mt-1">
                                    جمع: {formatPrice(item.unit_price * item.quantity)} تومان
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            {/* Order Summary */}
            <div className="glass-card rounded-2xl p-5 border border-white/10">
                <h3 className="text-lg font-bold text-white mb-4">خلاصه سفارش</h3>
                <div className="space-y-2">
                    <div className="flex justify-between text-gray-300">
                        <span>جمع محصولات:</span>
                        <span>{formatPrice(order.amount)} تومان</span>
                    </div>
                    {order.discount_amount > 0 && (
                        <div className="flex justify-between text-green-400">
                            <span>تخفیف:</span>
                            <span>- {formatPrice(order.discount_amount)} تومان</span>
                        </div>
                    )}
                    {order.delivery_fee > 0 && (
                        <div className="flex justify-between text-gray-300">
                            <span>هزینه ارسال:</span>
                            <span>{formatPrice(order.delivery_fee)} تومان</span>
                        </div>
                    )}
                    <div className="border-t border-white/10 pt-2 mt-2 flex justify-between text-white font-bold text-lg">
                        <span>مبلغ نهایی:</span>
                        <span className="text-cherry-400">{formatPrice(order.final_amount)} تومان</span>
                    </div>
                </div>
            </div>

            {/* Delivery Address */}
            {order.delivery_address && (
                <div className="glass-card rounded-2xl p-5 border border-white/10">
                    <h3 className="text-lg font-bold text-white mb-4">آدرس تحویل</h3>
                    <div className="flex items-start gap-3">
                        <div className="w-10 h-10 rounded-full bg-cherry-600/20 flex items-center justify-center text-cherry-400 flex-shrink-0">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div className="flex-1">
                            <p className="text-white mb-2">{order.delivery_address}</p>
                            <p className="text-sm text-gray-400">گیرنده: {order.customer_name}</p>
                            <p className="text-sm text-gray-400">{order.customer_phone}</p>
                        </div>
                    </div>
                </div>
            )}

            {/* Actions */}
            {order.invoice && (
                <Link 
                    to={`/account/invoices/${order.invoice.id}`}
                    className="block w-full text-center px-6 py-3 rounded-lg bg-cherry-600 hover:bg-cherry-700 text-white font-semibold transition"
                >
                    مشاهده فاکتور
                </Link>
            )}
        </div>
    );
}

export default AccountOrderDetail;

