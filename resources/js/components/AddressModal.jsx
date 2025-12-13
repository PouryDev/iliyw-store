import React from 'react';
import { useAuth } from '../contexts/AuthContext';
import { apiRequest } from '../utils/sanctumAuth';

function AddressModal({ open, onClose, onSave, address = null, loading = false }) {
    const { user } = useAuth();
    const [form, setForm] = React.useState({
        title: '',
        address: '',
        postal_code: '',
        city: '',
        province: '',
        recipient_name: '',
        recipient_phone: '',
        is_default: false
    });

    React.useEffect(() => {
        if (open) {
            if (address) {
                // Editing existing address
                setForm({
                    title: address.title || '',
                    address: address.address || '',
                    postal_code: address.postal_code || '',
                    city: address.city || '',
                    province: address.province || '',
                    recipient_name: address.recipient_name || '',
                    recipient_phone: address.recipient_phone || '',
                    is_default: address.is_default || false
                });
            } else {
                // Creating new address - pre-populate with user data
                setForm({
                    title: '',
                    address: '',
                    postal_code: '',
                    city: '',
                    province: '',
                    recipient_name: user?.name || '',
                    recipient_phone: user?.phone || '',
                    is_default: false
                });
            }
        }
    }, [open, address, user]);

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setForm(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const url = address ? `/api/addresses/${address.id}` : '/api/addresses';
            const method = address ? 'PUT' : 'POST';
            
            const res = await apiRequest(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(form)
            });
            
            if (res.ok) {
                const data = await res.json();
                // Show success toast
                window.dispatchEvent(new CustomEvent('toast:show', {
                    detail: { 
                        type: 'success', 
                        message: address ? 'آدرس با موفقیت ویرایش شد' : 'آدرس با موفقیت اضافه شد' 
                    }
                }));
                onSave?.(data.data);
                onClose?.();
            } else {
                const errorData = await res.json();
                throw new Error(errorData.message || 'خطا در ذخیره آدرس');
            }
        } catch (error) {
            console.error('Failed to save address:', error);
            // Show error toast
            window.dispatchEvent(new CustomEvent('toast:show', {
                detail: { 
                    type: 'error', 
                    message: error.message || 'خطا در ذخیره آدرس' 
                }
            }));
        }
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-end md:items-center justify-center">
            {/* Backdrop */}
            <div 
                className="absolute inset-0 bg-black/60 backdrop-blur" 
                onClick={onClose}
            />
            
            {/* Modal */}
            <div className="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-t-3xl md:rounded-2xl w-full md:max-w-md mx-auto shadow-2xl border border-white/10 max-h-[90vh] md:max-h-[80vh] flex flex-col">
                {/* Header */}
                <div className="px-4 py-4 border-b border-white/10 flex-shrink-0">
                    <div className="flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-bold text-white">
                                {address ? 'ویرایش آدرس' : 'افزودن آدرس جدید'}
                            </h2>
                            <p className="text-xs text-gray-400 mt-1">
                                آدرس خود را برای استفاده در سفارشات ذخیره کنید
                            </p>
                        </div>
                        <button
                            onClick={onClose}
                            className="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors"
                        >
                            <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {/* Form - Scrollable */}
                <form onSubmit={handleSubmit} className="px-4 py-4 space-y-4 overflow-y-auto flex-1">
                    {/* Title */}
                    <div>
                        <label className="block text-sm font-medium text-gray-300 mb-2">
                            نام آدرس (اختیاری)
                        </label>
                        <input
                            type="text"
                            name="title"
                            value={form.title}
                            onChange={handleChange}
                            placeholder="مثل: خانه، محل کار"
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500/50 focus:border-transparent text-sm"
                        />
                    </div>

                    {/* Address */}
                    <div>
                        <label className="block text-sm font-medium text-gray-300 mb-2">
                            آدرس کامل *
                        </label>
                        <textarea
                            name="address"
                            value={form.address}
                            onChange={handleChange}
                            rows={3}
                            required
                            placeholder="آدرس کامل خود را وارد کنید..."
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500/50 focus:border-transparent resize-none text-sm"
                        />
                    </div>

                    {/* City and Province */}
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-sm font-medium text-gray-300 mb-2">
                                شهر
                            </label>
                            <input
                                type="text"
                                name="city"
                                value={form.city}
                                onChange={handleChange}
                                placeholder="تهران"
                                className="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500/50 focus:border-transparent text-sm"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-300 mb-2">
                                استان
                            </label>
                            <input
                                type="text"
                                name="province"
                                value={form.province}
                                onChange={handleChange}
                                placeholder="تهران"
                                className="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500/50 focus:border-transparent text-sm"
                            />
                        </div>
                    </div>

                    {/* Recipient Name and Phone */}
                    <div className="grid grid-cols-2 gap-3">
                        <div>
                            <label className="block text-sm font-medium text-gray-300 mb-2">
                                نام گیرنده
                            </label>
                            <input
                                type="text"
                                name="recipient_name"
                                value={form.recipient_name}
                                onChange={handleChange}
                                placeholder={user?.name ? `نام شما: ${user.name}` : "نام گیرنده"}
                                className="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500/50 focus:border-transparent text-sm"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-300 mb-2">
                                شماره گیرنده
                            </label>
                            <input
                                type="text"
                                name="recipient_phone"
                                value={form.recipient_phone}
                                onChange={handleChange}
                                placeholder={user?.phone ? `شماره شما: ${user.phone}` : "09123456789"}
                                className="w-full bg-white/5 border border-white/10 rounded-xl px-3 py-2.5 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500/50 focus:border-transparent text-sm"
                            />
                        </div>
                    </div>

                    {/* Postal Code */}
                    <div>
                        <label className="block text-sm font-medium text-gray-300 mb-2">
                            کد پستی
                        </label>
                        <input
                            type="text"
                            name="postal_code"
                            value={form.postal_code}
                            onChange={handleChange}
                            placeholder="1234567890"
                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-500/50 focus:border-transparent text-sm"
                        />
                    </div>

                    {/* Default Address - Custom Checkbox */}
                    <div className="flex items-start gap-3 p-3 bg-white/5 rounded-xl border border-white/10">
                        <div className="relative flex-shrink-0 mt-1">
                            <input
                                type="checkbox"
                                name="is_default"
                                checked={form.is_default}
                                onChange={handleChange}
                                className="sr-only"
                                id="is_default"
                            />
                            <label
                                htmlFor="is_default"
                                className={`w-6 h-6 rounded-lg border-2 cursor-pointer transition-all duration-200 flex items-center justify-center ${
                                    form.is_default 
                                        ? 'bg-gradient-to-r from-cherry-600 to-violet-600 border-cherry-600 shadow-lg shadow-cherry-600/30' 
                                        : 'bg-white/10 border-white/20 hover:border-white/40'
                                }`}
                            >
                                {form.is_default && (
                                    <svg className="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
                                    </svg>
                                )}
                            </label>
                        </div>
                        <div className="flex-1">
                            <label htmlFor="is_default" className="text-sm font-medium text-white cursor-pointer">
                                تنظیم به عنوان آدرس پیش‌فرض
                            </label>
                            <p className="text-xs text-gray-400 mt-1">
                                این آدرس به عنوان آدرس پیش‌فرض برای سفارشات استفاده خواهد شد
                            </p>
                        </div>
                    </div>
                </form>

                {/* Actions - Fixed at bottom */}
                <div className="px-4 py-4 border-t border-white/10 flex-shrink-0 bg-gradient-to-t from-gray-900/90 to-transparent">
                    <div className="flex gap-3">
                        <button
                            type="button"
                            onClick={onClose}
                            className="flex-1 py-3 px-4 bg-white/10 hover:bg-white/20 text-white rounded-xl transition-colors text-sm font-medium"
                        >
                            انصراف
                        </button>
                        <button
                            type="submit"
                            disabled={loading || !form.address.trim()}
                            onClick={handleSubmit}
                            className="flex-1 py-3 px-4 bg-gradient-to-r from-cherry-600 to-violet-600 hover:from-cherry-500 hover:to-violet-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl transition-all duration-200 text-sm font-medium shadow-lg"
                        >
                            {loading ? (
                                <span className="flex items-center justify-center gap-2">
                                    <div className="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                    در حال ذخیره...
                                </span>
                            ) : (
                                address ? 'ویرایش آدرس' : 'ذخیره آدرس'
                            )}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default AddressModal;
