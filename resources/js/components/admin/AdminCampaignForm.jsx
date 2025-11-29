import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import ModernCheckbox from './ModernCheckbox';
import ModernSelect from './ModernSelect';
import PersianDatePicker from './PersianDatePicker';
import { apiRequest } from '../../utils/sanctumAuth';
import { adminApiRequest } from '../../utils/adminApi';
import { showToast } from '../../utils/toast';
import { scrollToTop } from '../../utils/scrollToTop';

function AdminCampaignForm() {
    const navigate = useNavigate();
    const { id } = useParams();
    const isEdit = !!id;
    
    const [form, setForm] = useState({
        title: '',
        description: '',
        discount_type: 'percentage',
        discount_value: '',
        starts_at: '',
        expires_at: '',
        is_active: true
    });
    
    const [selectedProducts, setSelectedProducts] = useState([]);
    const [allProducts, setAllProducts] = useState([]);
    const [searchTerm, setSearchTerm] = useState('');
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
                        setAllProducts(data.data);
                    }
                }

                // Load campaign data if editing
                if (isEdit) {
                    const campaignRes = await adminApiRequest(`/campaigns/${id}`);

                    if (campaignRes.ok) {
                        const data = await campaignRes.json();
                        if (data.success) {
                            const campaign = data.data;
                            setForm({
                                title: campaign.title || '',
                                description: campaign.description || '',
                                discount_type: campaign.discount_type || 'percentage',
                                discount_value: campaign.discount_value || '',
                                starts_at: campaign.starts_at ? campaign.starts_at.split('T')[0] : '',
                                expires_at: campaign.expires_at ? campaign.expires_at.split('T')[0] : '',
                                is_active: campaign.is_active ?? true
                            });
                            setSelectedProducts(campaign.products || []);
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
        setForm(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value
        }));
    };

    const filteredProducts = allProducts.filter(product =>
        product.title.toLowerCase().includes(searchTerm.toLowerCase()) &&
        !selectedProducts.some(selected => selected.id === product.id)
    );

    const addProduct = (product) => {
        setSelectedProducts(prev => [...prev, product]);
        setSearchTerm('');
    };

    const removeProduct = (productId) => {
        setSelectedProducts(prev => prev.filter(p => p.id !== productId));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            const url = isEdit ? `/api/admin/campaigns/${id}` : '/api/admin/campaigns';
            const method = isEdit ? 'PUT' : 'POST';

            const requestData = {
                ...form,
                product_ids: selectedProducts.map(p => p.id)
            };

            const res = await apiRequest(url, {
                method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    showToast(isEdit ? 'کمپین با موفقیت به‌روزرسانی شد' : 'کمپین با موفقیت ایجاد شد', 'success');
                    navigate('/admin/campaigns');
                    scrollToTop();
                } else {
                    showToast(data.message || 'خطا در ذخیره کمپین', 'error');
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
                    showToast(errorData.message || 'خطا در ذخیره کمپین', 'error');
                }
            }
        } catch (error) {
            console.error('Failed to save campaign:', error);
            showToast('خطا در ذخیره کمپین', 'error');
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
                    {isEdit ? 'ویرایش کمپین' : 'کمپین جدید'}
                </h1>
                <p className="text-gray-400">
                    {isEdit ? 'اطلاعات کمپین را ویرایش کنید' : 'اطلاعات کمپین جدید را وارد کنید'}
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-8">
                {/* Basic Information */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 relative z-10">
                    <h2 className="text-xl font-bold text-white mb-6">اطلاعات پایه</h2>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-white font-medium mb-2">عنوان کمپین</label>
                            <input
                                type="text"
                                name="title"
                                value={form.title}
                                onChange={handleInputChange}
                                required
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="عنوان کمپین را وارد کنید"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">نوع تخفیف</label>
                            <ModernSelect
                                name="discount_type"
                                value={form.discount_type}
                                onChange={(value) => {
                                    setForm(prev => ({
                                        ...prev,
                                        discount_type: value
                                    }));
                                }}
                                options={[
                                    { value: 'percentage', label: 'درصدی' },
                                    { value: 'fixed', label: 'مبلغی' }
                                ]}
                                placeholder="نوع تخفیف را انتخاب کنید"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">
                                مقدار تخفیف {form.discount_type === 'percentage' ? '(درصد)' : '(تومان)'}
                            </label>
                            <input
                                type="number"
                                name="discount_value"
                                value={form.discount_value}
                                onChange={handleInputChange}
                                required
                                min="0"
                                max={form.discount_type === 'percentage' ? 100 : undefined}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder={form.discount_type === 'percentage' ? '20' : '50000'}
                            />
                        </div>

                        <div className="flex items-center">
                            <ModernCheckbox
                                checked={form.is_active}
                                onChange={(e) => setForm(prev => ({ ...prev, is_active: e.target.checked }))}
                                label="فعال"
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
                    </div>

                    <div className="mt-6">
                        <label className="block text-white font-medium mb-2">توضیحات</label>
                        <textarea
                            name="description"
                            value={form.description}
                            onChange={handleInputChange}
                            rows="3"
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            placeholder="توضیحات کمپین (اختیاری)"
                        />
                    </div>
                </div>

                {/* Product Selection */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 relative z-10">
                    <h2 className="text-xl font-bold text-white mb-6">انتخاب محصولات</h2>
                    
                    {/* Search */}
                    <div className="mb-6">
                        <input
                            type="text"
                            placeholder="جستجو در محصولات..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                        />
                    </div>

                    {/* Selected Products */}
                    {selectedProducts.length > 0 && (
                        <div className="mb-6">
                            <h3 className="text-white font-medium mb-3">محصولات انتخاب شده ({selectedProducts.length})</h3>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                {selectedProducts.map((product) => (
                                    <div key={product.id} className="bg-purple-500/20 border border-purple-500/30 rounded-lg p-3 flex items-center justify-between">
                                        <div className="flex items-center space-x-3 space-x-reverse">
                                            {product.images && product.images.length > 0 ? (
                                                <img 
                                                    src={product.images[0].url} 
                                                    alt={product.title}
                                                    className="w-10 h-10 object-cover rounded"
                                                />
                                            ) : (
                                                <div className="w-10 h-10 bg-gray-600 rounded flex items-center justify-center text-xs">📦</div>
                                            )}
                                            <span className="text-white text-sm font-medium truncate">{product.title}</span>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => removeProduct(product.id)}
                                            className="text-red-400 hover:text-red-300 p-1"
                                        >
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Available Products */}
                    {searchTerm && (
                        <div>
                            <h3 className="text-white font-medium mb-3">محصولات موجود</h3>
                            <div className="max-h-60 overflow-y-auto space-y-2 relative z-0">
                                {filteredProducts.map((product) => (
                                    <div key={product.id} className="bg-white/5 border border-white/10 rounded-lg p-3 flex items-center justify-between hover:bg-white/10 transition-colors">
                                        <div className="flex items-center space-x-3 space-x-reverse">
                                            {product.images && product.images.length > 0 ? (
                                                <img 
                                                    src={product.images[0].url} 
                                                    alt={product.title}
                                                    className="w-10 h-10 object-cover rounded"
                                                />
                                            ) : (
                                                <div className="w-10 h-10 bg-gray-600 rounded flex items-center justify-center text-xs">📦</div>
                                            )}
                                            <div>
                                                <span className="text-white text-sm font-medium">{product.title}</span>
                                                <p className="text-gray-400 text-xs">{Number(product.price).toLocaleString('fa-IR')} تومان</p>
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => addProduct(product)}
                                            className="bg-purple-500 hover:bg-purple-600 text-white px-3 py-1 rounded text-sm transition-colors"
                                        >
                                            افزودن
                                        </button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {!searchTerm && (
                        <div className="text-center py-8">
                            <div className="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <p className="text-gray-400">برای انتخاب محصولات، نام محصول را جستجو کنید</p>
                        </div>
                    )}
                </div>

                {/* Submit Button */}
                <div className="flex gap-4">
                    <button
                        type="button"
                        onClick={() => {
                            navigate('/admin/campaigns');
                            scrollToTop();
                        }}
                        className="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200"
                    >
                        انصراف
                    </button>
                    <button
                        type="submit"
                        disabled={loading || selectedProducts.length === 0}
                        className="flex-1 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                        {loading ? (
                            <div className="flex items-center justify-center space-x-2 space-x-reverse">
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <span>در حال ذخیره...</span>
                            </div>
                        ) : (
                            isEdit ? 'به‌روزرسانی کمپین' : 'ایجاد کمپین'
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default AdminCampaignForm;
