import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import ModernSelect from './ModernSelect';
import { adminApiRequest } from '../../utils/adminApi';

function AdminCategoryManagement() {
    const navigate = useNavigate();
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');

    useEffect(() => {
        const loadCategories = async () => {
            try {
                setLoading(true);
                const res = await adminApiRequest('/categories');
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        setCategories(data.data);
                    }
                }
            } catch (error) {
                console.error('Failed to load categories:', error);
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'error', message: 'خطا در بارگذاری دسته‌بندی‌ها' } 
                }));
            } finally {
                setLoading(false);
            }
        };

        loadCategories();
    }, []);

    const handleDeleteCategory = async (categoryId) => {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این دسته‌بندی را حذف کنید؟')) {
            return;
        }

        try {
            const res = await adminApiRequest(`/categories/${categoryId}`, { method: 'DELETE' });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    setCategories(categories.filter(c => c.id !== categoryId));
                    window.dispatchEvent(new CustomEvent('toast:show', { 
                        detail: { type: 'success', message: 'دسته‌بندی با موفقیت حذف شد' } 
                    }));
                } else {
                    window.dispatchEvent(new CustomEvent('toast:show', { 
                        detail: { type: 'error', message: data.message || 'خطا در حذف دسته‌بندی' } 
                    }));
                }
            } else {
                const errorData = await res.json();
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'error', message: errorData.message || 'خطا در حذف دسته‌بندی' } 
                }));
            }
        } catch (error) {
            console.error('Failed to delete category:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در حذف دسته‌بندی' } 
            }));
        }
    };

    const toggleCategoryStatus = async (categoryId, currentStatus) => {
        try {
            const res = await adminApiRequest(`/categories/${categoryId}/toggle`, { method: 'PATCH' });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    setCategories(categories.map(c => 
                        c.id === categoryId 
                            ? { ...c, is_active: !currentStatus }
                            : c
                    ));
                    window.dispatchEvent(new CustomEvent('toast:show', { 
                        detail: { type: 'success', message: 'وضعیت دسته‌بندی تغییر کرد' } 
                    }));
                }
            }
        } catch (error) {
            console.error('Failed to toggle category:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در تغییر وضعیت' } 
            }));
        }
    };

    const filteredCategories = categories.filter(category => {
        const matchesSearch = category.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            category.description?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            category.slug?.toLowerCase().includes(searchTerm.toLowerCase());
        
        const matchesStatus = filterStatus === 'all' || 
                            (filterStatus === 'active' && category.is_active) ||
                            (filterStatus === 'inactive' && !category.is_active);
        
        return matchesSearch && matchesStatus;
    });

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
                        <h1 className="text-3xl font-bold text-white mb-2">مدیریت دسته‌بندی‌ها</h1>
                        <p className="text-gray-400">مدیریت و ویرایش دسته‌بندی‌های فروشگاه</p>
                    </div>
                    <button
                        onClick={() => navigate('/admin/categories/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg flex items-center justify-center space-x-2 space-x-reverse min-h-[44px]"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>دسته‌بندی جدید</span>
                    </button>
                </div>
            </div>

            {/* Filters */}
            <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 p-4 sm:p-6 mb-8">
                <div className="flex flex-col sm:flex-row gap-4">
                    <div className="flex-1">
                        <input
                            type="text"
                            placeholder="جستجو در دسته‌بندی‌ها..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 min-h-[44px]"
                        />
                    </div>
                    <div className="sm:w-48">
                        <ModernSelect
                            value={filterStatus}
                            onChange={(value) => setFilterStatus(value)}
                            options={[
                                { value: 'all', label: 'همه دسته‌بندی‌ها' },
                                { value: 'active', label: 'فعال' },
                                { value: 'inactive', label: 'غیرفعال' }
                            ]}
                            placeholder="فیلتر وضعیت"
                        />
                    </div>
                </div>
            </div>

            {/* Categories List */}
            <div className="space-y-4 sm:space-y-6">
                {filteredCategories.map((category) => (
                    <div key={category.id} className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-2xl p-4 sm:p-6">
                        <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 sm:gap-6">
                            {/* Category Info */}
                            <div className="flex-1">
                                <div className="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 mb-4">
                                    <h3 className="text-white font-bold text-lg sm:text-xl">{category.name}</h3>
                                    <span className={`px-3 py-1 rounded-full text-sm font-medium self-start ${
                                        category.is_active 
                                            ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                            : 'bg-red-500/20 text-red-400 border border-red-500/30'
                                    }`}>
                                        {category.is_active ? 'فعال' : 'غیرفعال'}
                                    </span>
                                </div>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p className="text-gray-400 text-sm mb-1">Slug</p>
                                        <p className="text-white font-medium break-all">{category.slug}</p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm mb-1">تعداد محصولات</p>
                                        <p className="text-white font-medium">
                                            {category.products_count || 0} محصول
                                        </p>
                                    </div>
                                </div>

                                {category.description && (
                                    <div className="mb-4">
                                        <p className="text-gray-400 text-sm mb-1">توضیحات</p>
                                        <p className="text-white text-sm leading-relaxed">{category.description}</p>
                                    </div>
                                )}
                            </div>

                            {/* Actions */}
                            <div className="flex flex-col sm:flex-row gap-2 sm:gap-2">
                                <button
                                    onClick={() => navigate(`/admin/categories/${category.id}/edit`)}
                                    className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm min-h-[44px]"
                                >
                                    ویرایش
                                </button>
                                <button
                                    onClick={() => toggleCategoryStatus(category.id, category.is_active)}
                                    className={`font-semibold py-3 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm min-h-[44px] ${
                                        category.is_active
                                            ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white'
                                            : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white'
                                    }`}
                                >
                                    {category.is_active ? 'غیرفعال کردن' : 'فعال کردن'}
                                </button>
                                <button
                                    onClick={() => handleDeleteCategory(category.id)}
                                    className="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm min-h-[44px]"
                                >
                                    حذف
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Empty State */}
            {filteredCategories.length === 0 && !loading && (
                <div className="text-center py-12">
                    <div className="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">دسته‌بندی‌ای یافت نشد</h3>
                    <p className="text-gray-400 mb-6">
                        {searchTerm || filterStatus !== 'all' 
                            ? 'هیچ دسته‌بندی‌ای با فیلترهای انتخابی یافت نشد' 
                            : 'هنوز دسته‌بندی‌ای اضافه نکرده‌اید'
                        }
                    </p>
                    {!searchTerm && filterStatus === 'all' && (
                        <button
                            onClick={() => navigate('/admin/categories/create')}
                            className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg min-h-[44px]"
                        >
                            اولین دسته‌بندی را اضافه کنید
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}

export default AdminCategoryManagement;

