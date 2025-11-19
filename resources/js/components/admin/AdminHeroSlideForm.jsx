import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import ModernCheckbox from './ModernCheckbox';
import { adminApiRequest } from '../../utils/adminApi';
import { showToast } from '../../utils/toast';
import { scrollToTop } from '../../utils/scrollToTop';

function AdminHeroSlideForm() {
    const navigate = useNavigate();
    const { id } = useParams();
    const isEdit = !!id;
    
    const [form, setForm] = useState({
        title: '',
        subtitle: '',
        description: '',
        link_type: 'custom',
        linkable_id: '',
        custom_url: '',
        button_text: '',
        is_active: true,
        sort_order: 0
    });
    
    const [imageFile, setImageFile] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [products, setProducts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [campaigns, setCampaigns] = useState([]);
    const [loading, setLoading] = useState(false);
    const [loadingData, setLoadingData] = useState(true);

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoadingData(true);
                
                // Load products
                const productsRes = await adminApiRequest('/products');
                if (productsRes.ok) {
                    const data = await productsRes.json();
                    if (data.success) {
                        setProducts(data.data);
                    }
                }

                // Load categories
                const categoriesRes = await adminApiRequest('/categories');
                if (categoriesRes.ok) {
                    const data = await categoriesRes.json();
                    if (data.success) {
                        setCategories(data.data);
                    }
                }

                // Load campaigns
                const campaignsRes = await adminApiRequest('/campaigns');
                if (campaignsRes.ok) {
                    const data = await campaignsRes.json();
                    if (data.success) {
                        setCampaigns(data.data);
                    }
                }

                // Load slide data if editing
                if (isEdit) {
                    const slideRes = await adminApiRequest(`/hero-slides/${id}`);
                    if (slideRes.ok) {
                        const data = await slideRes.json();
                        if (data.success) {
                            const slide = data.data;
                            setForm({
                                title: slide.title || '',
                                subtitle: slide.subtitle || '',
                                description: slide.description || '',
                                link_type: slide.link_type || 'custom',
                                linkable_id: slide.linkable_id || '',
                                custom_url: slide.custom_url || '',
                                button_text: slide.button_text || '',
                                is_active: slide.is_active ?? true,
                                sort_order: slide.sort_order || 0
                            });
                            if (slide.image_url) {
                                setImagePreview(slide.image_url);
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to load data:', error);
            } finally {
                setLoadingData(false);
            }
        };

        loadData();
    }, [id, isEdit]);

    const handleInputChange = (e) => {
        const { name, value, type, checked } = e.target;
        
        // Reset related fields when link_type changes
        if (name === 'link_type') {
            setForm(prev => ({
                ...prev,
                [name]: value,
                linkable_id: '',
                custom_url: ''
            }));
        } else {
            setForm(prev => ({
                ...prev,
                [name]: type === 'checkbox' ? checked : value
            }));
        }
    };

    const handleImageChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setImageFile(file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setImagePreview(reader.result);
            };
            reader.readAsDataURL(file);
        }
    };

    const getLinkableOptions = () => {
        switch (form.link_type) {
            case 'product':
                return products;
            case 'category':
                return categories;
            case 'campaign':
                return campaigns;
            default:
                return [];
        }
    };

    const getLinkableLabel = (item) => {
        if (form.link_type === 'product') {
            return item.title;
        }
        if (form.link_type === 'category') {
            return item.name;
        }
        if (form.link_type === 'campaign') {
            return item.title || item.name;
        }
        return '';
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const formData = new FormData();
            formData.append('title', form.title);
            formData.append('subtitle', form.subtitle);
            formData.append('description', form.description);
            formData.append('link_type', form.link_type);
            formData.append('button_text', form.button_text);
            formData.append('is_active', form.is_active ? '1' : '0');
            formData.append('sort_order', form.sort_order);

            if (form.link_type === 'custom') {
                if (form.custom_url) {
                    formData.append('custom_url', form.custom_url);
                }
            } else {
                if (form.linkable_id) {
                    formData.append('linkable_id', form.linkable_id);
                }
            }

            if (imageFile) {
                formData.append('image', imageFile);
            }

            // For Laravel, use POST with _method override for PUT requests
            // This ensures FormData is properly parsed
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            // Debug: Log FormData contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            const url = isEdit ? `/api/admin/hero-slides/${id}` : '/api/admin/hero-slides';

            const res = await adminApiRequest(url, {
                method: 'POST',
                body: formData
            });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    showToast(isEdit ? 'اسلاید با موفقیت به‌روزرسانی شد' : 'اسلاید با موفقیت ایجاد شد', 'success');
                    navigate('/admin/hero-slides');
                    scrollToTop();
                } else {
                    showToast(data.message || 'خطا در ذخیره اسلاید', 'error');
                }
            } else {
                const errorData = await res.json();
                if (res.status === 422 && errorData.errors) {
                    const firstError = Object.values(errorData.errors)[0];
                    if (firstError && firstError[0]) {
                        showToast(firstError[0], 'error');
                    } else {
                        showToast(errorData.message || 'لطفاً خطاهای زیر را برطرف کنید', 'error');
                    }
                } else {
                    showToast(errorData.message || 'خطا در ذخیره اسلاید', 'error');
                }
            }
        } catch (error) {
            console.error('Failed to save slide:', error);
            showToast('خطا در ذخیره اسلاید', 'error');
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
        <div className="max-w-4xl mx-auto relative">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">
                    {isEdit ? 'ویرایش اسلاید' : 'اسلاید جدید'}
                </h1>
                <p className="text-gray-400">
                    {isEdit ? 'اطلاعات اسلاید را ویرایش کنید' : 'اطلاعات اسلاید جدید را وارد کنید'}
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-8">
                {/* Basic Information */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 relative z-10">
                    <h2 className="text-xl font-bold text-white mb-6">اطلاعات پایه</h2>
                    
                    <div className="space-y-6">
                        <div>
                            <label className="block text-white font-medium mb-2">عنوان</label>
                            <input
                                type="text"
                                name="title"
                                value={form.title}
                                onChange={handleInputChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="عنوان اسلاید (اختیاری)"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">زیرعنوان</label>
                            <input
                                type="text"
                                name="subtitle"
                                value={form.subtitle}
                                onChange={handleInputChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="زیرعنوان اسلاید (اختیاری)"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">توضیحات</label>
                            <textarea
                                name="description"
                                value={form.description}
                                onChange={handleInputChange}
                                rows="3"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="توضیحات اسلاید (اختیاری)"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">تصویر اسلاید *</label>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={handleImageChange}
                                required={!isEdit || !imagePreview}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-500 file:text-white hover:file:bg-purple-600 transition-all duration-200"
                            />
                            {imagePreview && (
                                <div className="mt-4">
                                    <img
                                        src={imagePreview}
                                        alt="Preview"
                                        className="w-full max-w-md h-64 object-cover rounded-xl"
                                    />
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Link Configuration */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 relative z-10">
                    <h2 className="text-xl font-bold text-white mb-6">تنظیمات لینک</h2>
                    
                    <div className="space-y-6">
                        <div>
                            <label className="block text-white font-medium mb-2">نوع لینک</label>
                            <select
                                name="link_type"
                                value={form.link_type}
                                onChange={handleInputChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            >
                                <option value="custom">URL سفارشی</option>
                                <option value="product">محصول</option>
                                <option value="category">دسته‌بندی</option>
                                <option value="campaign">کمپین</option>
                            </select>
                        </div>

                        {form.link_type === 'custom' ? (
                            <div>
                                <label className="block text-white font-medium mb-2">URL سفارشی</label>
                                <input
                                    type="url"
                                    name="custom_url"
                                    value={form.custom_url}
                                    onChange={handleInputChange}
                                    required
                                    className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                    placeholder="https://example.com"
                                />
                            </div>
                        ) : (
                            <div>
                                <label className="block text-white font-medium mb-2">
                                    {form.link_type === 'product' ? 'محصول' :
                                     form.link_type === 'category' ? 'دسته‌بندی' : 'کمپین'}
                                </label>
                                <select
                                    name="linkable_id"
                                    value={form.linkable_id}
                                    onChange={handleInputChange}
                                    required
                                    className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="">انتخاب کنید...</option>
                                    {getLinkableOptions().map((item) => (
                                        <option key={item.id} value={item.id}>
                                            {getLinkableLabel(item)}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        )}

                        <div>
                            <label className="block text-white font-medium mb-2">متن دکمه</label>
                            <input
                                type="text"
                                name="button_text"
                                value={form.button_text}
                                onChange={handleInputChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="متن دکمه (اختیاری)"
                            />
                        </div>
                    </div>
                </div>

                {/* Settings */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 relative z-10">
                    <h2 className="text-xl font-bold text-white mb-6">تنظیمات</h2>
                    
                    <div className="space-y-6">
                        <div className="flex items-center">
                            <ModernCheckbox
                                checked={form.is_active}
                                onChange={(e) => setForm(prev => ({ ...prev, is_active: e.target.checked }))}
                                label="فعال"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">ترتیب نمایش</label>
                            <input
                                type="number"
                                name="sort_order"
                                value={form.sort_order}
                                onChange={handleInputChange}
                                min="0"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="0"
                            />
                        </div>
                    </div>
                </div>

                {/* Submit Button */}
                <div className="flex gap-4">
                    <button
                        type="button"
                        onClick={() => {
                            navigate('/admin/hero-slides');
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
                            isEdit ? 'به‌روزرسانی اسلاید' : 'ایجاد اسلاید'
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default AdminHeroSlideForm;

