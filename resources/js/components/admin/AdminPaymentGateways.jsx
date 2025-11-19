import React, { useState, useEffect } from 'react';
import { apiRequest } from '../../utils/sanctumAuth';
import { showToast } from '../../utils/toast';

function AdminPaymentGateways() {
    const [gateways, setGateways] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [showConfigForm, setShowConfigForm] = useState(false);
    const [editingGateway, setEditingGateway] = useState(null);
    const [configGateway, setConfigGateway] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        type: '',
        display_name: '',
        description: '',
        is_active: true,
        sort_order: 0
    });
    const [configData, setConfigData] = useState({});

    useEffect(() => {
        fetchGateways();
    }, []);

    const fetchGateways = async () => {
        try {
            setLoading(true);
            const response = await apiRequest('/api/admin/payment-gateways');
            
            if (response.ok) {
                const data = await response.json();
                setGateways(data.data);
            } else {
                setError('خطا در بارگذاری درگاه‌های پرداخت');
            }
        } catch (err) {
            setError('خطا در ارتباط با سرور');
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const url = editingGateway 
                ? `/api/admin/payment-gateways/${editingGateway.id}`
                : '/api/admin/payment-gateways';
            
            const method = editingGateway ? 'PUT' : 'POST';
            
            const response = await apiRequest(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData),
            });

            if (response.ok) {
                showToast(editingGateway ? 'درگاه پرداخت با موفقیت به‌روزرسانی شد' : 'درگاه پرداخت با موفقیت ایجاد شد', 'success');
                await fetchGateways();
                setShowForm(false);
                setEditingGateway(null);
                setFormData({
                    name: '',
                    type: '',
                    display_name: '',
                    description: '',
                    is_active: true,
                    sort_order: 0
                });
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'خطا در ذخیره درگاه پرداخت', 'error');
            }
        } catch (err) {
            showToast('خطا در ارتباط با سرور', 'error');
        }
    };

    const handleConfigSubmit = async (e) => {
        e.preventDefault();
        
        try {
            const response = await apiRequest(`/api/admin/payment-gateways/${configGateway.id}/config`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ config: configData }),
            });

            if (response.ok) {
                showToast('تنظیمات درگاه با موفقیت به‌روزرسانی شد', 'success');
                await fetchGateways();
                setShowConfigForm(false);
                setConfigGateway(null);
                setConfigData({});
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'خطا در به‌روزرسانی تنظیمات', 'error');
            }
        } catch (err) {
            showToast('خطا در ارتباط با سرور', 'error');
        }
    };

    const handleEdit = (gateway) => {
        setEditingGateway(gateway);
        setFormData({
            name: gateway.name,
            type: gateway.type,
            display_name: gateway.display_name,
            description: gateway.description || '',
            is_active: gateway.is_active,
            sort_order: gateway.sort_order
        });
        setShowForm(true);
    };

    const handleConfig = (gateway) => {
        setConfigGateway(gateway);
        setConfigData(gateway.config || {});
        setShowConfigForm(true);
    };

    const handleToggle = async (gateway) => {
        try {
            const response = await apiRequest(`/api/admin/payment-gateways/${gateway.id}/toggle`, {
                method: 'PATCH'
            });

            if (response.ok) {
                showToast(gateway.is_active ? 'درگاه پرداخت غیرفعال شد' : 'درگاه پرداخت فعال شد', 'success');
                await fetchGateways();
            } else {
                showToast('خطا در تغییر وضعیت درگاه', 'error');
            }
        } catch (err) {
            showToast('خطا در ارتباط با سرور', 'error');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این درگاه پرداخت را حذف کنید؟')) {
            return;
        }

        try {
            const response = await apiRequest(`/api/admin/payment-gateways/${id}`, {
                method: 'DELETE'
            });

            if (response.ok) {
                showToast('درگاه پرداخت با موفقیت حذف شد', 'success');
                await fetchGateways();
            } else {
                const errorData = await response.json();
                showToast(errorData.message || 'خطا در حذف درگاه پرداخت', 'error');
            }
        } catch (err) {
            showToast('خطا در ارتباط با سرور', 'error');
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center min-h-96">
                <div className="text-center">
                    <div className="w-12 h-12 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
                    <p className="text-gray-400">در حال بارگذاری...</p>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-6xl mx-auto">
            {/* Header */}
            <div className="mb-6 lg:mb-8">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl lg:text-3xl font-bold text-white mb-2">مدیریت درگاه‌های پرداخت</h1>
                        <p className="text-gray-400 text-sm lg:text-base">مدیریت و تنظیم درگاه‌های پرداخت فروشگاه</p>
                    </div>
                    <button
                        onClick={() => {
                            setEditingGateway(null);
                            setFormData({
                                name: '',
                                type: '',
                                display_name: '',
                                description: '',
                                is_active: true,
                                sort_order: 0
                            });
                            setShowForm(true);
                        }}
                        className="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-500 hover:to-purple-600 text-white px-4 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-purple-500/20 flex items-center justify-center gap-2 font-medium"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                        افزودن درگاه جدید
                    </button>
                </div>
            </div>

            {error && (
                <div className="mb-6 bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 backdrop-blur-xl">
                    {error}
                </div>
            )}

            {/* Desktop Table View */}
            <div className="hidden lg:block bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 overflow-hidden shadow-2xl">
                <table className="w-full">
                    <thead className="bg-white/5">
                        <tr>
                            <th className="px-6 py-4 text-right text-sm font-semibold text-white">نام</th>
                            <th className="px-6 py-4 text-right text-sm font-semibold text-white">نوع</th>
                            <th className="px-6 py-4 text-right text-sm font-semibold text-white">وضعیت</th>
                            <th className="px-6 py-4 text-right text-sm font-semibold text-white">ترتیب</th>
                            <th className="px-6 py-4 text-right text-sm font-semibold text-white">عملیات</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-white/10">
                        {gateways.map((gateway) => (
                            <tr key={gateway.id} className="hover:bg-white/5 transition-colors">
                                <td className="px-6 py-4 text-white font-medium">{gateway.display_name}</td>
                                <td className="px-6 py-4 text-gray-300">{gateway.type}</td>
                                <td className="px-6 py-4">
                                    <span className={`px-3 py-1 rounded-full text-xs font-medium border ${
                                        gateway.is_active 
                                            ? 'bg-green-500/20 text-green-400 border-green-500/30' 
                                            : 'bg-red-500/20 text-red-400 border-red-500/30'
                                    }`}>
                                        {gateway.is_active ? 'فعال' : 'غیرفعال'}
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-gray-300">{gateway.sort_order}</td>
                                <td className="px-6 py-4">
                                    <div className="flex items-center gap-2">
                                        <button
                                            onClick={() => handleToggle(gateway)}
                                            className="text-blue-400 hover:text-blue-300 text-sm px-3 py-1.5 rounded-lg hover:bg-blue-500/10 transition-colors"
                                        >
                                            {gateway.is_active ? 'غیرفعال' : 'فعال'}
                                        </button>
                                        <button
                                            onClick={() => handleConfig(gateway)}
                                            className="text-yellow-400 hover:text-yellow-300 text-sm px-3 py-1.5 rounded-lg hover:bg-yellow-500/10 transition-colors"
                                        >
                                            تنظیمات
                                        </button>
                                        <button
                                            onClick={() => handleEdit(gateway)}
                                            className="text-green-400 hover:text-green-300 text-sm px-3 py-1.5 rounded-lg hover:bg-green-500/10 transition-colors"
                                        >
                                            ویرایش
                                        </button>
                                        <button
                                            onClick={() => handleDelete(gateway.id)}
                                            className="text-red-400 hover:text-red-300 text-sm px-3 py-1.5 rounded-lg hover:bg-red-500/10 transition-colors"
                                        >
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Mobile Card View */}
            <div className="lg:hidden space-y-4">
                {gateways.map((gateway) => (
                    <div 
                        key={gateway.id} 
                        className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-2xl overflow-hidden"
                    >
                        <div className="p-4 space-y-3">
                            {/* Header */}
                            <div className="flex items-start justify-between">
                                <div className="flex-1">
                                    <h3 className="text-white font-semibold text-lg mb-1">{gateway.display_name}</h3>
                                    <p className="text-gray-400 text-sm">{gateway.type}</p>
                                </div>
                                <span className={`px-3 py-1 rounded-full text-xs font-medium border shrink-0 ${
                                    gateway.is_active 
                                        ? 'bg-green-500/20 text-green-400 border-green-500/30' 
                                        : 'bg-red-500/20 text-red-400 border-red-500/30'
                                }`}>
                                    {gateway.is_active ? 'فعال' : 'غیرفعال'}
                                </span>
                            </div>

                            {/* Details */}
                            <div className="flex items-center gap-4 text-sm text-gray-300">
                                <div className="flex items-center gap-1">
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span>ترتیب: {gateway.sort_order}</span>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="grid grid-cols-2 gap-2 pt-2 border-t border-white/10">
                                <button
                                    onClick={() => handleToggle(gateway)}
                                    className="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-blue-500/10 text-blue-400 border border-blue-500/20 hover:bg-blue-500/20 active:scale-95"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                    {gateway.is_active ? 'غیرفعال' : 'فعال'}
                                </button>
                                <button
                                    onClick={() => handleConfig(gateway)}
                                    className="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 hover:bg-yellow-500/20 active:scale-95"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    تنظیمات
                                </button>
                                <button
                                    onClick={() => handleEdit(gateway)}
                                    className="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20 active:scale-95"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    ویرایش
                                </button>
                                <button
                                    onClick={() => handleDelete(gateway.id)}
                                    className="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 active:scale-95"
                                >
                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    حذف
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Gateway Form Modal */}
            {showForm && (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-white/10 shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                        <div className="sticky top-0 bg-gradient-to-br from-gray-800 to-gray-900 border-b border-white/10 px-6 py-4 flex items-center justify-between">
                            <h2 className="text-xl font-bold text-white">
                                {editingGateway ? 'ویرایش درگاه پرداخت' : 'افزودن درگاه پرداخت'}
                            </h2>
                            <button
                                onClick={() => {
                                    setShowForm(false);
                                    setEditingGateway(null);
                                }}
                                className="text-gray-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10"
                            >
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <form onSubmit={handleSubmit} className="p-6 space-y-4">
                            <div>
                                <label className="block text-sm text-gray-300 mb-2 font-medium">نام</label>
                                <input
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm text-gray-300 mb-2 font-medium">نوع (type)</label>
                                <select
                                    value={formData.type}
                                    onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all"
                                    required
                                    disabled={!!editingGateway}
                                    style={{ colorScheme: 'dark' }}
                                >
                                    <option value="">انتخاب کنید</option>
                                    <option value="zarinpal">زرین‌پال</option>
                                    <option value="card_to_card">کارت به کارت</option>
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm text-gray-300 mb-2 font-medium">نام نمایشی</label>
                                <input
                                    type="text"
                                    value={formData.display_name}
                                    onChange={(e) => setFormData({ ...formData, display_name: e.target.value })}
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all"
                                    required
                                />
                            </div>
                            <div>
                                <label className="block text-sm text-gray-300 mb-2 font-medium">توضیحات</label>
                                <textarea
                                    value={formData.description}
                                    onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all resize-none"
                                    rows="3"
                                />
                            </div>
                            <div>
                                <label className="block text-sm text-gray-300 mb-2 font-medium">ترتیب نمایش</label>
                                <input
                                    type="number"
                                    value={formData.sort_order}
                                    onChange={(e) => setFormData({ ...formData, sort_order: parseInt(e.target.value) || 0 })}
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all"
                                />
                            </div>
                            <div className="flex items-center gap-3 p-4 bg-white/5 rounded-xl border border-white/10">
                                <input
                                    type="checkbox"
                                    checked={formData.is_active}
                                    onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                    className="w-5 h-5 rounded border-white/20 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-0"
                                />
                                <label className="text-sm text-gray-300 font-medium">فعال</label>
                            </div>
                            <div className="flex gap-3 pt-2">
                                <button
                                    type="submit"
                                    className="flex-1 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-500 hover:to-purple-600 text-white px-4 py-3 rounded-xl transition-all duration-200 font-medium shadow-lg shadow-purple-500/20"
                                >
                                    ذخیره
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowForm(false);
                                        setEditingGateway(null);
                                    }}
                                    className="flex-1 bg-white/5 hover:bg-white/10 text-white px-4 py-3 rounded-xl transition-all duration-200 font-medium border border-white/10"
                                >
                                    انصراف
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}

            {/* Config Form Modal */}
            {showConfigForm && configGateway && (
                <div className="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
                    <div className="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-white/10 shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
                        <div className="sticky top-0 bg-gradient-to-br from-gray-800 to-gray-900 border-b border-white/10 px-6 py-4 flex items-center justify-between">
                            <h2 className="text-xl font-bold text-white">
                                تنظیمات {configGateway.display_name}
                            </h2>
                            <button
                                onClick={() => {
                                    setShowConfigForm(false);
                                    setConfigGateway(null);
                                }}
                                className="text-gray-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10"
                            >
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <form onSubmit={handleConfigSubmit} className="p-6 space-y-4">
                            {configGateway.type === 'zarinpal' && (
                                <>
                                    <div>
                                        <label className="block text-sm text-gray-300 mb-2 font-medium">Merchant ID</label>
                                        <input
                                            type="text"
                                            value={configData.merchant_id || ''}
                                            onChange={(e) => setConfigData({ ...configData, merchant_id: e.target.value })}
                                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all"
                                            placeholder="مرچنت کد زرین‌پال"
                                        />
                                    </div>
                                    <div className="flex items-center gap-3 p-4 bg-white/5 rounded-xl border border-white/10">
                                        <input
                                            type="checkbox"
                                            checked={configData.sandbox || false}
                                            onChange={(e) => setConfigData({ ...configData, sandbox: e.target.checked })}
                                            className="w-5 h-5 rounded border-white/20 bg-white/5 text-purple-600 focus:ring-purple-500 focus:ring-offset-0"
                                        />
                                        <label className="text-sm text-gray-300 font-medium">Sandbox Mode</label>
                                    </div>
                                </>
                            )}
                            {configGateway.type === 'card_to_card' && (
                                <>
                                    <div>
                                        <label className="block text-sm text-gray-300 mb-2 font-medium">شماره کارت</label>
                                        <input
                                            type="text"
                                            value={configData.card_number || ''}
                                            onChange={(e) => setConfigData({ ...configData, card_number: e.target.value })}
                                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all"
                                            placeholder="6037991553211859"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm text-gray-300 mb-2 font-medium">نام دارنده کارت</label>
                                        <input
                                            type="text"
                                            value={configData.card_holder || ''}
                                            onChange={(e) => setConfigData({ ...configData, card_holder: e.target.value })}
                                            className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition-all"
                                            placeholder="نام دارنده کارت"
                                        />
                                    </div>
                                </>
                            )}
                            <div className="flex gap-3 pt-2">
                                <button
                                    type="submit"
                                    className="flex-1 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-500 hover:to-purple-600 text-white px-4 py-3 rounded-xl transition-all duration-200 font-medium shadow-lg shadow-purple-500/20"
                                >
                                    ذخیره تنظیمات
                                </button>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setShowConfigForm(false);
                                        setConfigGateway(null);
                                    }}
                                    className="flex-1 bg-white/5 hover:bg-white/10 text-white px-4 py-3 rounded-xl transition-all duration-200 font-medium border border-white/10"
                                >
                                    انصراف
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}

export default AdminPaymentGateways;
