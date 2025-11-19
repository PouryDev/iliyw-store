import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { adminApiRequest } from '../../utils/adminApi';

function AdminDiscountManagement() {
    const navigate = useNavigate();
    const [discounts, setDiscounts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadDiscounts = async () => {
            try {
                setLoading(true);
                const res = await adminApiRequest('/discount-codes');
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        setDiscounts(data.data);
                    }
                }
            } catch (error) {
                console.error('Failed to load discounts:', error);
            } finally {
                setLoading(false);
            }
        };

        loadDiscounts();
    }, []);

    const formatPrice = (value) => {
        try { 
            return Number(value || 0).toLocaleString('fa-IR'); 
        } catch { 
            return value || '0'; 
        }
    };

    const handleDeleteDiscount = async (discountId) => {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این کد تخفیف را حذف کنید؟')) {
            return;
        }

        try {
            const res = await adminApiRequest(`/discount-codes/${discountId}`, { method: 'DELETE' });

            if (res.ok) {
                setDiscounts(discounts.filter(d => d.id !== discountId));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'کد تخفیف با موفقیت حذف شد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to delete discount:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در حذف کد تخفیف' } 
            }));
        }
    };

    const toggleDiscountStatus = async (discountId, currentStatus) => {
        try {
            const res = await adminApiRequest(`/discount-codes/${discountId}/toggle`, { method: 'PATCH' });

            if (res.ok) {
                setDiscounts(discounts.map(d => 
                    d.id === discountId 
                        ? { ...d, is_active: !currentStatus }
                        : d
                ));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'وضعیت کد تخفیف تغییر کرد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to toggle discount:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در تغییر وضعیت' } 
            }));
        }
    };

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
                        <h1 className="text-3xl font-bold text-white mb-2">مدیریت کدهای تخفیف</h1>
                        <p className="text-gray-400">مدیریت و ویرایش کدهای تخفیف فروشگاه</p>
                    </div>
                    <button
                        onClick={() => navigate('/admin/discounts/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg flex items-center space-x-2 space-x-reverse"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>کد تخفیف جدید</span>
                    </button>
                </div>
            </div>

            {/* Discounts List */}
            <div className="space-y-6">
                {discounts.map((discount) => (
                    <div key={discount.id} className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-2xl p-6">
                        <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            {/* Discount Info */}
                            <div className="flex-1">
                                <div className="flex items-center gap-4 mb-4">
                                    <h3 className="text-white font-bold text-xl">{discount.code}</h3>
                                    <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                        discount.is_active 
                                            ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                            : 'bg-red-500/20 text-red-400 border border-red-500/30'
                                    }`}>
                                        {discount.is_active ? 'فعال' : 'غیرفعال'}
                                    </span>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p className="text-gray-400 text-sm">نوع تخفیف</p>
                                        <p className="text-white font-medium">
                                            {discount.type === 'percentage' ? 'درصدی' : 'مبلغی'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">مقدار تخفیف</p>
                                        <p className="text-white font-medium">
                                            {discount.type === 'percentage' 
                                                ? `${discount.value}%` 
                                                : `${formatPrice(discount.value)} تومان`
                                            }
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">حداقل خرید</p>
                                        <p className="text-white font-medium">
                                            {discount.minimum_amount ? `${formatPrice(discount.minimum_amount)} تومان` : 'بدون محدودیت'}
                                        </p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p className="text-gray-400 text-sm">تاریخ شروع</p>
                                        <p className="text-white font-medium">
                                            {discount.starts_at ? new Date(discount.starts_at).toLocaleDateString('fa-IR') : 'فوری'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">تاریخ انقضا</p>
                                        <p className="text-white font-medium">
                                            {discount.expires_at ? new Date(discount.expires_at).toLocaleDateString('fa-IR') : 'بدون انقضا'}
                                        </p>
                                    </div>
                                </div>

                                {discount.description && (
                                    <div className="mt-4">
                                        <p className="text-gray-400 text-sm">توضیحات</p>
                                        <p className="text-white">{discount.description}</p>
                                    </div>
                                )}

                                <div className="mt-4">
                                    <p className="text-gray-400 text-sm">استفاده شده</p>
                                    <p className="text-white font-medium">
                                        {discount.usage_count || 0} از {discount.usage_limit || 'نامحدود'}
                                    </p>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex flex-col sm:flex-row gap-2">
                                <button
                                    onClick={() => navigate(`/admin/discounts/${discount.id}/edit`)}
                                    className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                >
                                    ویرایش
                                </button>
                                <button
                                    onClick={() => toggleDiscountStatus(discount.id, discount.is_active)}
                                    className={`font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm ${
                                        discount.is_active
                                            ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white'
                                            : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white'
                                    }`}
                                >
                                    {discount.is_active ? 'غیرفعال کردن' : 'فعال کردن'}
                                </button>
                                <button
                                    onClick={() => handleDeleteDiscount(discount.id)}
                                    className="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                >
                                    حذف
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Empty State */}
            {discounts.length === 0 && !loading && (
                <div className="text-center py-12">
                    <div className="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">کد تخفیفی یافت نشد</h3>
                    <p className="text-gray-400 mb-6">هنوز کد تخفیفی ایجاد نکرده‌اید</p>
                    <button
                        onClick={() => navigate('/admin/discounts/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg"
                    >
                        اولین کد تخفیف را ایجاد کنید
                    </button>
                </div>
            )}
        </div>
    );
}

export default AdminDiscountManagement;
