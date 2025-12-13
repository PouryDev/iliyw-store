import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import ModernSelect from './ModernSelect';
import ModernCheckbox from './ModernCheckbox';
import PersianDatePicker from './PersianDatePicker';
import { apiRequest } from '../../utils/sanctumAuth';
import { adminApiRequest } from '../../utils/adminApi';
import { showToast } from '../../utils/toast';
import { scrollToTop } from '../../utils/scrollToTop';

function AdminDiscountForm() {
    const navigate = useNavigate();
    const { id } = useParams();
    const isEdit = !!id;
    
    const [form, setForm] = useState({
        code: '',
        type: 'percentage',
        value: '',
        minimum_amount: '',
        starts_at: '',
        expires_at: '',
        usage_limit: '',
        description: '',
        is_active: true
    });
    
    const [loading, setLoading] = useState(false);
    const [loadingData, setLoadingData] = useState(isEdit);

    useEffect(() => {
        if (isEdit) {
            const loadDiscount = async () => {
                try {
                    setLoadingData(true);
                    const res = await adminApiRequest(`/discount-codes/${id}`);

                    if (res.ok) {
                        const data = await res.json();
                        if (data.success) {
                            const discount = data.data;
                            setForm({
                                code: discount.code || '',
                                type: discount.type || 'percentage',
                                value: discount.value || '',
                                minimum_amount: discount.minimum_amount || '',
                                starts_at: discount.starts_at ? discount.starts_at.split('T')[0] : '',
                                expires_at: discount.expires_at ? discount.expires_at.split('T')[0] : '',
                                usage_limit: discount.usage_limit || '',
                                description: discount.description || '',
                                is_active: discount.is_active ?? true
                            });
                        }
                    }
                } catch (error) {
                    console.error('Failed to load discount:', error);
                } finally {
                    setLoadingData(false);
                }
            };

            loadDiscount();
        }
    }, [id, isEdit]);

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setForm(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const url = isEdit ? `/api/admin/discount-codes/${id}` : '/api/admin/discount-codes';
            const method = isEdit ? 'PUT' : 'POST';

            const res = await apiRequest(url, {
                method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(form)
            });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    showToast(isEdit ? 'کد تخفیف با موفقیت به‌روزرسانی شد' : 'کد تخفیف با موفقیت ایجاد شد', 'success');
                    navigate('/admin/discounts');
                    scrollToTop();
                } else {
                    showToast(data.message || 'خطا در ذخیره کد تخفیف', 'error');
                }
            } else {
                // Handle validation errors
                const errorData = await res.json();
                if (res.status === 422 && errorData.errors) {
                    // Show first validation error as toast
                    const firstError = Object.values(errorData.errors)[0];
                    if (firstError && firstError[0]) {
                        showToast(firstError[0], 'error');
                    } else {
                        showToast(errorData.message || 'لطفاً خطاهای زیر را برطرف کنید', 'error');
                    }
                } else {
                    showToast(errorData.message || 'خطا در ذخیره کد تخفیف', 'error');
                }
            }
        } catch (error) {
            console.error('Failed to save discount:', error);
            showToast('خطا در ذخیره کد تخفیف', 'error');
        } finally {
            setLoading(false);
        }
    };

    if (loadingData) {
        return (
            <div className="max-w-4xl mx-auto">
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
        <div className="max-w-4xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">
                    {isEdit ? 'ویرایش کد تخفیف' : 'کد تخفیف جدید'}
                </h1>
                <p className="text-gray-400">
                    {isEdit ? 'اطلاعات کد تخفیف را ویرایش کنید' : 'اطلاعات کد تخفیف جدید را وارد کنید'}
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-8">
                {/* Basic Information */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                    <h2 className="text-xl font-bold text-white mb-6">اطلاعات پایه</h2>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-white font-medium mb-2">کد تخفیف</label>
                            <input
                                type="text"
                                name="code"
                                value={form.code}
                                onChange={handleInputChange}
                                required
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="مثال: WELCOME20"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">نوع تخفیف</label>
                            <select
                                name="type"
                                value={form.type}
                                onChange={handleInputChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            >
                                <option value="percentage">درصدی</option>
                                <option value="fixed">مبلغی</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">
                                مقدار تخفیف {form.type === 'percentage' ? '(درصد)' : '(تومان)'}
                            </label>
                            <input
                                type="number"
                                name="value"
                                value={form.value}
                                onChange={handleInputChange}
                                required
                                min="0"
                                max={form.type === 'percentage' ? 100 : undefined}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder={form.type === 'percentage' ? '20' : '50000'}
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">حداقل مبلغ خرید (تومان)</label>
                            <input
                                type="number"
                                name="minimum_amount"
                                value={form.minimum_amount}
                                onChange={handleInputChange}
                                min="0"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="100000"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">تاریخ شروع</label>
                            <PersianDatePicker
                                value={form.starts_at}
                                onChange={(value) => setForm(prev => ({ ...prev, starts_at: value }))}
                                placeholder="تاریخ شروع را انتخاب کنید"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">تاریخ انقضا</label>
                            <PersianDatePicker
                                value={form.expires_at}
                                onChange={(value) => setForm(prev => ({ ...prev, expires_at: value }))}
                                placeholder="تاریخ انقضا را انتخاب کنید"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">حد مجاز استفاده</label>
                            <input
                                type="number"
                                name="usage_limit"
                                value={form.usage_limit}
                                onChange={handleInputChange}
                                min="1"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="100"
                            />
                        </div>

                        <div className="flex items-center">
                            <ModernCheckbox
                                checked={form.is_active}
                                onChange={(e) => setForm(prev => ({ ...prev, is_active: e.target.checked }))}
                                label="فعال"
                            />
                        </div>
                    </div>

                    <div className="mt-6">
                        <label className="block text-white font-medium mb-2">توضیحات</label>
                        <textarea
                            name="description"
                            value={form.description}
                            onChange={handleInputChange}
                            rows="3"
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            placeholder="توضیحات کد تخفیف (اختیاری)"
                        />
                    </div>
                </div>

                {/* Preview */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                    <h2 className="text-xl font-bold text-white mb-6">پیش‌نمایش</h2>
                    
                    <div className="bg-gradient-to-r from-purple-500/20 to-purple-600/20 rounded-xl p-6 border border-purple-500/30">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-white font-bold text-xl">{form.code || 'کد تخفیف'}</h3>
                            <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                form.is_active 
                                    ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                    : 'bg-red-500/20 text-red-400 border border-red-500/30'
                            }`}>
                                {form.is_active ? 'فعال' : 'غیرفعال'}
                            </span>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p className="text-gray-400">نوع تخفیف:</p>
                                <p className="text-white font-medium">
                                    {form.type === 'percentage' ? 'درصدی' : 'مبلغی'}
                                </p>
                            </div>
                            <div>
                                <p className="text-gray-400">مقدار تخفیف:</p>
                                <p className="text-white font-medium">
                                    {form.value ? (
                                        form.type === 'percentage' 
                                            ? `${form.value}%` 
                                            : `${Number(form.value).toLocaleString('fa-IR')} تومان`
                                    ) : 'تعریف نشده'}
                                </p>
                            </div>
                            <div>
                                <p className="text-gray-400">حداقل خرید:</p>
                                <p className="text-white font-medium">
                                    {form.minimum_amount 
                                        ? `${Number(form.minimum_amount).toLocaleString('fa-IR')} تومان`
                                        : 'بدون محدودیت'
                                    }
                                </p>
                            </div>
                            <div>
                                <p className="text-gray-400">حد مجاز استفاده:</p>
                                <p className="text-white font-medium">
                                    {form.usage_limit || 'نامحدود'}
                                </p>
                            </div>
                        </div>

                        {form.description && (
                            <div className="mt-4">
                                <p className="text-gray-400 text-sm">توضیحات:</p>
                                <p className="text-white">{form.description}</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Submit Button */}
                <div className="flex gap-4">
                    <button
                        type="button"
                        onClick={() => {
                            navigate('/admin/discounts');
                            scrollToTop();
                        }}
                        className="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200"
                    >
                        انصراف
                    </button>
                    <button
                        type="submit"
                        disabled={loading}
                        className="flex-1 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                        {loading ? (
                            <div className="flex items-center justify-center space-x-2 space-x-reverse">
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <span>در حال ذخیره...</span>
                            </div>
                        ) : (
                            isEdit ? 'به‌روزرسانی کد تخفیف' : 'ایجاد کد تخفیف'
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default AdminDiscountForm;
