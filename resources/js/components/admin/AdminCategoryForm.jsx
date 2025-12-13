import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import ModernCheckbox from './ModernCheckbox';
import { apiRequest } from '../../utils/sanctumAuth';
import { adminApiRequest } from '../../utils/adminApi';

function AdminCategoryForm() {
    const navigate = useNavigate();
    const { id } = useParams();
    const isEdit = !!id;
    
    const [form, setForm] = useState({
        name: '',
        slug: '',
        description: '',
        is_active: true
    });
    
    const [loading, setLoading] = useState(false);
    const [loadingData, setLoadingData] = useState(isEdit);
    const [autoGenerateSlug, setAutoGenerateSlug] = useState(true);

    useEffect(() => {
        if (isEdit) {
            const loadCategory = async () => {
                try {
                    setLoadingData(true);
                    const res = await adminApiRequest(`/categories/${id}`);

                    if (res.ok) {
                        const data = await res.json();
                        if (data.success) {
                            const category = data.data;
                            setForm({
                                name: category.name || '',
                                slug: category.slug || '',
                                description: category.description || '',
                                is_active: category.is_active ?? true
                            });
                            setAutoGenerateSlug(false);
                        }
                    }
                } catch (error) {
                    console.error('Failed to load category:', error);
                    window.dispatchEvent(new CustomEvent('toast:show', { 
                        detail: { type: 'error', message: 'خطا در بارگذاری دسته‌بندی' } 
                    }));
                } finally {
                    setLoadingData(false);
                }
            };

            loadCategory();
        }
    }, [id, isEdit]);

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        setForm(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));

        // Auto-generate slug when name changes
        if (name === 'name' && autoGenerateSlug) {
            const slug = value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            setForm(prev => ({ ...prev, slug }));
        }
    };

    const handleSlugChange = (e) => {
        setForm(prev => ({ ...prev, slug: e.target.value }));
        setAutoGenerateSlug(false);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const url = isEdit ? `/api/admin/categories/${id}` : '/api/admin/categories';
            const method = isEdit ? 'PUT' : 'POST';

            const submitData = {
                ...form,
                slug: form.slug || undefined // Send undefined if empty to let backend generate it
            };

            const res = await apiRequest(url, {
                method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(submitData)
            });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('toast:show', { 
                        detail: { 
                            type: 'success', 
                            message: isEdit ? 'دسته‌بندی با موفقیت به‌روزرسانی شد' : 'دسته‌بندی با موفقیت ایجاد شد' 
                        } 
                    }));
                    navigate('/admin/categories');
                    window.scrollTo(0, 0);
                } else {
                    window.dispatchEvent(new CustomEvent('toast:show', { 
                        detail: { type: 'error', message: data.message || 'خطا در ذخیره دسته‌بندی' } 
                    }));
                }
            } else {
                // Handle validation errors
                const errorData = await res.json();
                if (res.status === 422 && errorData.errors) {
                    // Show first validation error as toast
                    const firstError = Object.values(errorData.errors)[0];
                    if (firstError && firstError[0]) {
                        window.dispatchEvent(new CustomEvent('toast:show', { 
                            detail: { type: 'error', message: firstError[0] } 
                        }));
                    } else {
                        window.dispatchEvent(new CustomEvent('toast:show', { 
                            detail: { type: 'error', message: errorData.message || 'لطفاً خطاهای زیر را برطرف کنید' } 
                        }));
                    }
                } else {
                    window.dispatchEvent(new CustomEvent('toast:show', { 
                        detail: { type: 'error', message: errorData.message || 'خطا در ذخیره دسته‌بندی' } 
                    }));
                }
            }
        } catch (error) {
            console.error('Failed to save category:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در ذخیره دسته‌بندی' } 
            }));
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
                    {isEdit ? 'ویرایش دسته‌بندی' : 'دسته‌بندی جدید'}
                </h1>
                <p className="text-gray-400">
                    {isEdit ? 'اطلاعات دسته‌بندی را ویرایش کنید' : 'اطلاعات دسته‌بندی جدید را وارد کنید'}
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6 sm:space-y-8">
                {/* Basic Information */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-4 sm:p-6">
                    <h2 className="text-xl font-bold text-white mb-6">اطلاعات پایه</h2>
                    
                    <div className="space-y-6">
                        <div>
                            <label className="block text-white font-medium mb-2">نام دسته‌بندی *</label>
                            <input
                                type="text"
                                name="name"
                                value={form.name}
                                onChange={handleInputChange}
                                required
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 min-h-[44px]"
                                placeholder="مثال: لباس زنانه"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">Slug</label>
                            <input
                                type="text"
                                name="slug"
                                value={form.slug}
                                onChange={handleSlugChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 min-h-[44px]"
                                placeholder="به صورت خودکار از نام تولید می‌شود"
                            />
                            <p className="text-gray-400 text-sm mt-2">
                                اگر خالی بگذارید، به صورت خودکار از نام تولید می‌شود
                            </p>
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">توضیحات</label>
                            <textarea
                                name="description"
                                value={form.description}
                                onChange={handleInputChange}
                                rows="4"
                                maxLength="1000"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 resize-none"
                                placeholder="توضیحات دسته‌بندی (اختیاری)"
                            />
                            <p className="text-gray-400 text-sm mt-2">
                                {form.description.length}/1000 کاراکتر
                            </p>
                        </div>

                        <div className="flex items-center pt-2">
                            <ModernCheckbox
                                checked={form.is_active}
                                onChange={(e) => setForm(prev => ({ ...prev, is_active: e.target.checked }))}
                                label="فعال"
                            />
                        </div>
                    </div>
                </div>

                {/* Preview */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-4 sm:p-6">
                    <h2 className="text-xl font-bold text-white mb-6">پیش‌نمایش</h2>
                    
                    <div className="bg-gradient-to-r from-purple-500/20 to-purple-600/20 rounded-xl p-4 sm:p-6 border border-purple-500/30">
                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 mb-4">
                            <h3 className="text-white font-bold text-lg sm:text-xl">
                                {form.name || 'نام دسته‌بندی'}
                            </h3>
                            <span className={`px-3 py-1 rounded-full text-sm font-medium self-start ${
                                form.is_active 
                                    ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                    : 'bg-red-500/20 text-red-400 border border-red-500/30'
                            }`}>
                                {form.is_active ? 'فعال' : 'غیرفعال'}
                            </span>
                        </div>

                        <div className="space-y-3 text-sm">
                            <div>
                                <p className="text-gray-400">Slug:</p>
                                <p className="text-white font-medium break-all">
                                    {form.slug || '(خودکار تولید می‌شود)'}
                                </p>
                            </div>
                            {form.description && (
                                <div>
                                    <p className="text-gray-400">توضیحات:</p>
                                    <p className="text-white">{form.description}</p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Submit Button */}
                <div className="flex flex-col sm:flex-row gap-4">
                    <button
                        type="button"
                        onClick={() => {
                            navigate('/admin/categories');
                            window.scrollTo(0, 0);
                        }}
                        className="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 min-h-[44px]"
                    >
                        انصراف
                    </button>
                    <button
                        type="submit"
                        disabled={loading}
                        className="flex-1 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 min-h-[44px]"
                    >
                        {loading ? (
                            <div className="flex items-center justify-center space-x-2 space-x-reverse">
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <span>در حال ذخیره...</span>
                            </div>
                        ) : (
                            isEdit ? 'به‌روزرسانی دسته‌بندی' : 'ایجاد دسته‌بندی'
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default AdminCategoryForm;

