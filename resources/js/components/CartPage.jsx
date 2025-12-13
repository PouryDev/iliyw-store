import React from 'react';
import { Link } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';
import VariantSelectorModal from './VariantSelectorModal';

function CartPage() {
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const [items, setItems] = React.useState([]);
    const [total, setTotal] = React.useState(0);
    const [originalTotal, setOriginalTotal] = React.useState(0);
    const [totalDiscount, setTotalDiscount] = React.useState(0);
    const [count, setCount] = React.useState(0);
    const [removingKey, setRemovingKey] = React.useState(null);
    const [variantModal, setVariantModal] = React.useState({ isOpen: false, product: null, quantity: 1 });

    const fetchCart = React.useCallback(async () => {
        setLoading(true);
        setError(null);
        try {
            const res = await apiRequest('/api/cart/json');
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            setItems(data.items || []);
            setTotal(data.total || 0);
            setOriginalTotal(data.original_total || data.total || 0);
            setTotalDiscount(data.total_discount || 0);
            setCount(data.count || 0);
            window.dispatchEvent(new Event('cart:update'));
        } catch (e) {
            setError('خطا در دریافت سبد خرید');
        } finally {
            setLoading(false);
        }
    }, []);

    React.useEffect(() => {
        fetchCart();
    }, [fetchCart]);

    async function handleRemove(cartKey) {
        setRemovingKey(cartKey);
        try {
            const res = await apiRequest(`/api/cart/remove/${encodeURIComponent(cartKey)}`, {
                method: 'DELETE',
            });
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            setItems(data.items || []);
            setTotal(data.total || 0);
            setOriginalTotal(data.original_total || data.total || 0);
            setTotalDiscount(data.total_discount || 0);
            setCount(data.count || 0);
            window.dispatchEvent(new Event('cart:update'));
        } catch (e) {
            // noop: keep current state, could show toast
        } finally {
            setRemovingKey(null);
        }
    }

    async function decrementItem(item) {
        setRemovingKey(item.key);
        try {
            const removeRes = await apiRequest(`/api/cart/remove/${encodeURIComponent(item.key)}`, {
                method: 'DELETE',
            });
            if (!removeRes.ok) throw new Error('failed');
            let state = await removeRes.json();
            if ((item.quantity || 0) - 1 > 0) {
                const addRes = await apiRequest(`/api/cart/add/${item.slug}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        quantity: (item.quantity - 1),
                        color_id: item.color_id || null,
                        size_id: item.size_id || null
                    })
                });
                if (!addRes.ok) throw new Error('failed');
                state = await addRes.json();
            }
            setItems(state.items || []);
            setTotal(state.total || 0);
            setOriginalTotal(state.original_total || state.total || 0);
            setTotalDiscount(state.total_discount || 0);
            setCount(state.count || 0);
            try { localStorage.setItem('cart', JSON.stringify(state.items || [])); } catch { }
            window.dispatchEvent(new Event('cart:update'));
        } catch (e) {
        } finally {
            setRemovingKey(null);
        }
    }

    async function incrementItem(item) {
        setRemovingKey(item.key);
        try {
            const res = await apiRequest(`/api/cart/add/${item.slug}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ quantity: 1 })
            });
            
            if (!res.ok) {
                const errorData = await res.json();
                // If it's a variant selection error, open variant modal
                if (res.status === 400 && errorData.message?.includes('رنگ و سایز')) {
                    setVariantModal({
                        isOpen: true,
                        product: item.product,
                        quantity: item.quantity + 1
                    });
                    return;
                }
                throw new Error('failed');
            }
            
            const state = await res.json();
            setItems(state.items || []);
            setTotal(state.total || 0);
            setOriginalTotal(state.original_total || state.total || 0);
            setTotalDiscount(state.total_discount || 0);
            setCount(state.count || 0);
            try { localStorage.setItem('cart', JSON.stringify(state.items || [])); } catch { }
            window.dispatchEvent(new Event('cart:update'));
        } catch (e) {
            console.error('Increment error:', e);
        } finally {
            setRemovingKey(null);
        }
    }

    function formatPrice(value) {
        try { return Number(value || 0).toLocaleString('fa-IR'); } catch { return value; }
    }

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
            {/* Header */}
            <div className="sticky top-0 z-30 bg-black/20 backdrop-blur-md border-b border-white/10">
                <div className="max-w-md mx-auto px-4 py-4">
                    <h1 className="text-xl font-bold text-white text-center">سبد خرید</h1>
                </div>
            </div>

            {/* Content */}
            <div className="max-w-md mx-auto px-4 py-6">
                {loading ? (
                    <div className="flex items-center justify-center py-12">
                        <div className="w-8 h-8 border-2 border-cherry-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                ) : error ? (
                    <div className="text-center py-12">
                        <div className="text-red-400 mb-2">⚠️</div>
                        <div className="text-red-400">{error}</div>
                    </div>
                ) : count === 0 ? (
                    <div className="text-center py-16">
                        <div className="text-6xl mb-4">🛒</div>
                        <div className="text-gray-400 text-lg">سبد خرید خالی است</div>
                        <Link to="/" className="inline-block mt-4 bg-cherry-600 hover:bg-cherry-500 text-white px-6 py-2 rounded-full text-sm font-medium transition-colors">
                            شروع خرید
                        </Link>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {items.map((item) => (
                            <div key={item.key} className="bg-white/5 backdrop-blur-sm rounded-2xl p-4 border border-white/10">
                                <div className="flex items-center gap-3">
                                    {/* Product Image */}
                                    <div className="w-16 h-16 rounded-xl bg-gradient-to-br from-cherry-500/20 to-violet-500/20 flex items-center justify-center text-2xl flex-shrink-0">
                                        🛍️
                                    </div>

                                    {/* Product Info */}
                                    <div className="flex-1 min-w-0">
                                        <h3 className="font-semibold text-white text-sm leading-tight truncate">{item.title}</h3>
                                        {item.variant_display_name && (
                                            <div className="text-xs text-gray-400 mt-0.5">{item.variant_display_name}</div>
                                        )}
                                        {item.campaign && (
                                            <div className="text-xs text-green-400 mt-0.5">🎉 {item.campaign.name}</div>
                                        )}
                                        <div className="flex items-center gap-2 mt-1">
                                            {item.original_price && item.original_price !== item.price && (
                                                <span className="text-xs text-gray-500 line-through">{formatPrice(item.original_price)}</span>
                                            )}
                                            <span className="text-xs text-cherry-400">{formatPrice(item.price)} تومان</span>
                                        </div>
                                    </div>

                                    {/* Price */}
                                    <div className="text-right">
                                        <div className="font-bold text-white text-sm">{formatPrice(item.total)} تومان</div>
                                        {item.total_discount > 0 && (
                                            <div className="text-xs text-green-400">صرفه‌جویی: {formatPrice(item.total_discount)}</div>
                                        )}
                                    </div>
                                </div>

                                {/* Controls */}
                                <div className="flex items-center justify-between mt-3">
                                    {/* Quantity Controls */}
                                    <div className="flex items-center gap-2">
                                        <button
                                            onClick={() => decrementItem(item)}
                                            disabled={removingKey === item.key}
                                            className="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white text-sm disabled:opacity-50 flex items-center justify-center"
                                        >
                                            −
                                        </button>
                                        <div className="w-8 text-center text-white text-sm font-medium">
                                            {item.quantity}
                                        </div>
                                        <button
                                            onClick={() => incrementItem(item)}
                                            disabled={removingKey === item.key}
                                            className="w-8 h-8 rounded-full bg-cherry-600 hover:bg-cherry-500 text-white text-sm disabled:opacity-50 flex items-center justify-center"
                                        >
                                            +
                                        </button>
                                    </div>

                                    {/* Remove Button */}
                                    <button
                                        onClick={() => handleRemove(item.key)}
                                        disabled={removingKey === item.key}
                                        className="text-red-400 hover:text-red-300 disabled:opacity-50 text-xs px-2 py-1 rounded-full hover:bg-red-400/10 transition-colors"
                                    >
                                        {removingKey === item.key ? 'حذف...' : 'حذف'}
                                    </button>
                                </div>
                            </div>
                        ))}

                        {/* Campaign Discount Summary */}
                        {totalDiscount > 0 && (
                            <div className="bg-green-500/10 backdrop-blur-sm rounded-2xl p-4 border border-green-500/20">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <span className="text-green-400 text-lg">🎉</span>
                                        <span className="text-green-400 font-medium">تخفیف کمپین</span>
                                    </div>
                                    <div className="text-green-400 font-bold">{formatPrice(totalDiscount)} تومان</div>
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Mobile Checkout Bar */}
            {count > 0 && (
                <div className="fixed bottom-0 left-0 right-0 z-40 bg-black/90 backdrop-blur-md border-t border-white/10">
                    <div className="max-w-md mx-auto px-4 py-4">
                        <div className="space-y-2">
                            {totalDiscount > 0 && (
                                <div className="flex items-center justify-between text-sm">
                                    <span className="text-gray-400">قیمت اصلی:</span>
                                    <span className="text-gray-400 line-through">{formatPrice(originalTotal)} تومان</span>
                                </div>
                            )}
                            <div className="flex items-center justify-between gap-4">
                                <div>
                                    <div className="text-xs text-gray-400">جمع کل</div>
                                    <div className="text-white font-bold text-lg">{formatPrice(total)} تومان</div>
                                </div>
                                <Link
                                    to="/checkout"
                                    className="bg-cherry-600 hover:bg-cherry-500 text-white rounded-2xl py-3 px-8 font-semibold transition-colors shadow-lg"
                                >
                                    ادامه خرید
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            )}
            
            {/* Variant Selector Modal */}
            <VariantSelectorModal
                product={variantModal.product}
                isOpen={variantModal.isOpen}
                onClose={() => setVariantModal({ isOpen: false, product: null, quantity: 1 })}
                onSuccess={() => {
                    fetchCart(); // Refresh cart after successful addition
                }}
                currentQuantity={variantModal.quantity}
            />
        </div>
    );
}

export default CartPage;