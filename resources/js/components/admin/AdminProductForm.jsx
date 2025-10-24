import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import FileUpload from './FileUpload';
import ModernSelect from './ModernSelect';
import ModernCheckbox from './ModernCheckbox';
import { apiRequest, debugTokenStatus } from '../../utils/sanctumAuth';
import { showToast } from '../../utils/toast';
import { scrollToTop } from '../../utils/scrollToTop';

function AdminProductForm() {
    const navigate = useNavigate();
    const { id } = useParams();
    const isEdit = !!id;
    
    const [form, setForm] = useState({
        title: '',
        description: '',
        price: '',
        stock: '',
        category_id: '',
        is_active: true,
        has_variants: false,
        has_colors: false,
        has_sizes: false,
        is_musical: false,
        artist: '',
        technique: '',
        year: ''
    });
    
    const [variants, setVariants] = useState([]);
    const [images, setImages] = useState([]);
    const [musicTracks, setMusicTracks] = useState([]);
    const [categories, setCategories] = useState([]);
    const [colors, setColors] = useState([]);
    const [sizes, setSizes] = useState([]);
    const [loading, setLoading] = useState(false);
    const [loadingData, setLoadingData] = useState(true);

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoadingData(true);
                
                // Load categories, colors, sizes
                const [categoriesRes, colorsRes, sizesRes] = await Promise.all([
                    apiRequest('/api/admin/categories'),
                    apiRequest('/api/admin/colors'),
                    apiRequest('/api/admin/sizes')
                ]);

                if (categoriesRes.ok) {
                    const data = await categoriesRes.json();
                    if (data.success) setCategories(data.data);
                }

                if (colorsRes.ok) {
                    const data = await colorsRes.json();
                    if (data.success) setColors(data.data);
                }

                if (sizesRes.ok) {
                    const data = await sizesRes.json();
                    if (data.success) setSizes(data.data);
                }

                // Load product data if editing
                if (isEdit) {
                    const productRes = await apiRequest(`/api/admin/products/${id}`);

                    if (productRes.ok) {
                        const data = await productRes.json();
                        if (data.success) {
                            const product = data.data;
                            setForm({
                                title: product.title || '',
                                description: product.description || '',
                                price: product.price || '',
                                stock: product.stock || '',
                                category_id: product.category_id || '',
                                is_active: product.is_active ?? true,
                                has_variants: product.has_variants ?? false,
                                has_colors: product.has_colors ?? false,
                                has_sizes: product.has_sizes ?? false,
                                is_musical: product.is_musical ?? false,
                                artist: product.artist || '',
                                technique: product.technique || '',
                                year: product.year || ''
                            });
                            
                            // Load music tracks if editing
                            if (product.is_musical) {
                                apiRequest(`/api/admin/products/${id}/music-tracks`)
                                    .then(async (res) => {
                                        if (res.ok) {
                                            const data = await res.json();
                                            if (data.success) {
                                                setMusicTracks(data.data || []);
                                            }
                                        }
                                    })
                                    .catch(() => {});
                            }
                            // Convert existing images to FileUpload format
                            const existingImages = (product.images || []).map((img, index) => ({
                                id: img.id || `existing-${index}`,
                                url: img.url,
                                isNew: false,
                                preview: img.url
                            }));
                            setImages(existingImages);
                            setVariants(product.variants || []);
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

    const handleImageUpload = (e) => {
        const files = Array.from(e.target.files);
        const newImages = files.map(file => ({
            file,
            preview: URL.createObjectURL(file),
            isNew: true
        }));
        setImages(prev => [...prev, ...newImages]);
    };

    const removeImage = (index) => {
        setImages(prev => {
            const newImages = [...prev];
            if (newImages[index].isNew) {
                URL.revokeObjectURL(newImages[index].preview);
            }
            newImages.splice(index, 1);
            return newImages;
        });
    };

    const addVariant = () => {
        setVariants(prev => [...prev, {
            color_id: '',
            size_id: '',
            price: form.price,
            stock: form.stock,
            isNew: true
        }]);
    };

    const updateVariant = (index, field, value) => {
        setVariants(prev => prev.map((variant, i) => 
            i === index ? { ...variant, [field]: value } : variant
        ));
    };

    const removeVariant = (index) => {
        setVariants(prev => prev.filter((_, i) => i !== index));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        // Validate required fields
        if (!form.title || !form.title.trim()) {
            showToast('عنوان محصول الزامی است', 'error');
            setLoading(false);
            return;
        }
        
        if (!form.price || form.price <= 0) {
            showToast('قیمت محصول الزامی است', 'error');
            setLoading(false);
            return;
        }
        
        if (!form.stock || form.stock < 0) {
            showToast('موجودی محصول الزامی است', 'error');
            setLoading(false);
            return;
        }

        try {
            // Debug token status before making request
            console.log('=== Before API Request ===');
            debugTokenStatus();
            console.log('========================');

            const formData = new FormData();

            // Add form data - ensure all required fields are properly set
            formData.append('title', form.title.trim());
            formData.append('description', form.description || '');
            formData.append('price', form.price.toString());
            formData.append('stock', form.stock.toString());
            if (form.category_id) {
                formData.append('category_id', String(form.category_id));
            }
            formData.append('is_active', form.is_active ? '1' : '0');
            formData.append('has_variants', form.has_variants ? '1' : '0');
            formData.append('has_colors', form.has_colors ? '1' : '0');
            formData.append('has_sizes', form.has_sizes ? '1' : '0');
            formData.append('is_musical', form.is_musical ? '1' : '0');
            if (form.artist) formData.append('artist', form.artist);
            if (form.technique) formData.append('technique', form.technique);
            if (form.year) formData.append('year', form.year.toString());

            // Add images
            const newImages = images.filter(img => img.isNew && img.file);
            newImages.forEach((image, index) => {
                formData.append(`images[${index}]`, image.file);
            });

            // Add image IDs to keep existing images
            const existingImageIds = images.filter(img => !img.isNew && img.id).map(img => img.id);
            existingImageIds.forEach((imageId, index) => {
                formData.append(`existing_images[${index}]`, imageId);
            });

            // Add variants
            variants.forEach((variant, index) => {
                formData.append(`variants[${index}][color_id]`, variant.color_id || '');
                formData.append(`variants[${index}][size_id]`, variant.size_id || '');
                formData.append(`variants[${index}][price]`, variant.price || '0');
                formData.append(`variants[${index}][stock]`, variant.stock || '0');
            });

            // Debug: Log FormData contents
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            const url = isEdit ? `/api/admin/products/${id}` : '/api/admin/products';
            // For Laravel, send multipart as POST + _method override to ensure fields/files are parsed
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            const res = await apiRequest(url, {
                method: 'POST',
                body: formData
            });

            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    showToast(isEdit ? 'محصول با موفقیت به‌روزرسانی شد' : 'محصول با موفقیت ایجاد شد', 'success');
                    // Stay on page for update (SPA UX) and refresh local state from server
                    if (isEdit) {
                        const product = data.data;
                        setForm(prev => ({
                            ...prev,
                            title: product.title || prev.title,
                            description: product.description || prev.description,
                            price: product.price ?? prev.price,
                            stock: product.stock ?? prev.stock,
                            category_id: product.category_id ?? prev.category_id,
                            is_active: product.is_active ?? prev.is_active,
                            has_variants: product.has_variants ?? prev.has_variants,
                            has_colors: product.has_colors ?? prev.has_colors,
                            has_sizes: product.has_sizes ?? prev.has_sizes
                        }));
                        // Sync images/variants UI
                        const existingImages = (product.images || []).map((img, index) => ({
                            id: img.id || `existing-${index}`,
                            url: img.url || (img.path ? (img.path.startsWith('http') ? img.path : `/storage/${img.path}`) : ''),
                            isNew: false,
                            preview: img.url || (img.path ? (img.path.startsWith('http') ? img.path : `/storage/${img.path}`) : '')
                        }));
                        setImages(existingImages);
                        setVariants(product.variants || []);
                    } else {
                        // For create, redirect back to list
                        navigate('/admin/products');
                        scrollToTop();
                    }
                } else {
                    showToast(data.message || 'خطا در ذخیره محصول', 'error');
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
                    showToast(errorData.message || 'خطا در ذخیره محصول', 'error');
                }
            }
        } catch (error) {
            console.error('Failed to save product:', error);
            showToast('خطا در ذخیره محصول', 'error');
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
                    {isEdit ? 'ویرایش محصول' : 'محصول جدید'}
                </h1>
                <p className="text-gray-400">
                    {isEdit ? 'اطلاعات محصول را ویرایش کنید' : 'اطلاعات محصول جدید را وارد کنید'}
                </p>
            </div>

            <form onSubmit={handleSubmit} className="space-y-8">
                {/* Basic Information */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                    <h2 className="text-xl font-bold text-white mb-6">اطلاعات پایه</h2>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-white font-medium mb-2">عنوان محصول</label>
                            <input
                                type="text"
                                name="title"
                                value={form.title}
                                onChange={handleInputChange}
                                required
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="عنوان محصول را وارد کنید"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">دسته‌بندی</label>
                            <ModernSelect
                                name="category_id"
                                options={[
                                    { value: '', label: 'انتخاب دسته‌بندی' },
                                    ...categories.map(category => ({
                                        value: category.id,
                                        label: category.name
                                    }))
                                ]}
                                value={form.category_id}
                                onChange={(value) => setForm(prev => ({ ...prev, category_id: value }))}
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">قیمت (تومان)</label>
                            <input
                                type="number"
                                name="price"
                                value={form.price}
                                onChange={handleInputChange}
                                required
                                min="0"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="قیمت محصول"
                            />
                        </div>

                        <div>
                            <label className="block text-white font-medium mb-2">موجودی</label>
                            <input
                                type="number"
                                name="stock"
                                value={form.stock}
                                onChange={handleInputChange}
                                required
                                min="0"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="تعداد موجودی"
                            />
                        </div>
                    </div>

                    <div className="mt-6">
                        <label className="block text-white font-medium mb-2">توضیحات</label>
                        <textarea
                            name="description"
                            value={form.description}
                            onChange={handleInputChange}
                            rows="4"
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            placeholder="توضیحات محصول را وارد کنید"
                        />
                    </div>

                    {/* Artwork Details */}
                    <div className="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label className="block text-white font-medium mb-2">هنرمند</label>
                            <input
                                type="text"
                                name="artist"
                                value={form.artist}
                                onChange={handleInputChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="نام هنرمند"
                            />
                        </div>
                        
                        <div>
                            <label className="block text-white font-medium mb-2">تکنیک</label>
                            <input
                                type="text"
                                name="technique"
                                value={form.technique}
                                onChange={handleInputChange}
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="مثلاً: آکریلیک، آبرنگ"
                            />
                        </div>
                        
                        <div>
                            <label className="block text-white font-medium mb-2">سال</label>
                            <input
                                type="number"
                                name="year"
                                value={form.year}
                                onChange={handleInputChange}
                                min="1900"
                                max="2100"
                                className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                placeholder="سال تولید"
                            />
                        </div>
                    </div>

                    <div className="mt-6 flex flex-wrap gap-6">
                        <ModernCheckbox
                            name="is_active"
                            checked={form.is_active}
                            onChange={(e) => setForm(prev => ({ ...prev, is_active: e.target.checked }))}
                            label="فعال"
                        />

                        <ModernCheckbox
                            name="is_musical"
                            checked={form.is_musical}
                            onChange={(e) => setForm(prev => ({ ...prev, is_musical: e.target.checked }))}
                            label="موزیکال"
                        />

                        <ModernCheckbox
                            name="has_variants"
                            checked={form.has_variants}
                            onChange={(e) => setForm(prev => ({ ...prev, has_variants: e.target.checked }))}
                            label="دارای تنوع"
                        />

                        <ModernCheckbox
                            name="has_colors"
                            checked={form.has_colors}
                            onChange={(e) => setForm(prev => ({ ...prev, has_colors: e.target.checked }))}
                            label="دارای نوع قاب"
                        />

                        <ModernCheckbox
                            name="has_sizes"
                            checked={form.has_sizes}
                            onChange={(e) => setForm(prev => ({ ...prev, has_sizes: e.target.checked }))}
                            label="دارای ابعاد"
                        />
                    </div>
                </div>

                {/* Music Tracks - Only show if is_musical is checked */}
                {form.is_musical && (
                    <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                        <h2 className="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <svg className="w-6 h-6 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" />
                            </svg>
                            موسیقی همراه تابلو
                        </h2>
                        
                        {musicTracks.length > 0 && (
                            <div className="mb-4 space-y-2">
                                {musicTracks.map((track, index) => (
                                    <div key={track.id} className="flex items-center justify-between bg-white/5 border border-white/10 rounded-lg p-3">
                                        <div className="flex items-center gap-3">
                                            <span className="text-gray-400 text-sm">{index + 1}</span>
                                            <div>
                                                <div className="text-white font-medium">{track.title}</div>
                                                {track.artist && <div className="text-gray-400 text-sm">{track.artist}</div>}
                                            </div>
                                        </div>
                                        <button
                                            type="button"
                                            onClick={async () => {
                                                if (confirm('آیا از حذف این آهنگ اطمینان دارید؟')) {
                                                    try {
                                                        const res = await apiRequest(`/api/admin/products/${id}/music-tracks/${track.id}`, {
                                                            method: 'DELETE'
                                                        });
                                                        if (res.ok) {
                                                            setMusicTracks(prev => prev.filter(t => t.id !== track.id));
                                                            showToast('آهنگ حذف شد', 'success');
                                                        }
                                                    } catch (error) {
                                                        showToast('خطا در حذف آهنگ', 'error');
                                                    }
                                                }
                                            }}
                                            className="text-red-400 hover:text-red-300 transition"
                                        >
                                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                        
                        <div className="text-gray-400 text-sm mb-4">
                            برای آپلود موزیک جدید، ابتدا محصول را ذخیره کنید و سپس از صفحه ویرایش، فایل‌های موزیک را اضافه نمایید.
                        </div>
                    </div>
                )}

                {/* Images */}
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                    <h2 className="text-xl font-bold text-white mb-6">تصاویر محصول</h2>
                    
                    <FileUpload
                        files={images}
                        onFilesChange={setImages}
                        multiple={true}
                        accept="image/*"
                        maxFiles={10}
                        productId={id}
                    />
                </div>

                {/* Variants */}
                {form.has_variants && (
                    <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6">
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-xl font-bold text-white">تنوع محصول</h2>
                            <button
                                type="button"
                                onClick={addVariant}
                                className="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105"
                            >
                                افزودن تنوع
                            </button>
                        </div>

                        <div className="space-y-4">
                            {variants.map((variant, index) => (
                                <div key={index} className="bg-white/5 rounded-lg p-4">
                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label className="block text-white font-medium mb-2">رنگ</label>
                                            <ModernSelect
                                                name={`variants[${index}][color_id]`}
                                                options={[
                                                    { value: '', label: 'انتخاب رنگ' },
                                                    ...colors.map(color => ({
                                                        value: color.id,
                                                        label: color.name
                                                    }))
                                                ]}
                                                value={variant.color_id}
                                                onChange={(value) => updateVariant(index, 'color_id', value)}
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-white font-medium mb-2">سایز</label>
                                            <ModernSelect
                                                name={`variants[${index}][size_id]`}
                                                options={[
                                                    { value: '', label: 'انتخاب سایز' },
                                                    ...sizes.map(size => ({
                                                        value: size.id,
                                                        label: size.name
                                                    }))
                                                ]}
                                                value={variant.size_id}
                                                onChange={(value) => updateVariant(index, 'size_id', value)}
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-white font-medium mb-2">قیمت</label>
                                            <input
                                                type="number"
                                                name={`variants[${index}][price]`}
                                                value={variant.price}
                                                onChange={(e) => updateVariant(index, 'price', e.target.value)}
                                                min="0"
                                                className="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                            />
                                        </div>

                                        <div className="flex items-end gap-2">
                                            <div className="flex-1">
                                                <label className="block text-white font-medium mb-2">موجودی</label>
                                                <input
                                                    type="number"
                                                    name={`variants[${index}][stock]`}
                                                    value={variant.stock}
                                                    onChange={(e) => updateVariant(index, 'stock', e.target.value)}
                                                    min="0"
                                                    className="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                                />
                                            </div>
                                            <button
                                                type="button"
                                                onClick={() => removeVariant(index)}
                                                className="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition-colors"
                                            >
                                                حذف
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Submit Button */}
                <div className="flex gap-4">
                    <button
                        type="button"
                        onClick={() => {
                            navigate('/admin/products');
                            scrollToTop();
                        }}
                        className="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200"
                    >
                        انصراف
                    </button>
                    <button
                        type="submit"
                        disabled={loading}
                        className="flex-1 bg-gradient-to-r from-amber-500 to-indigo-600 hover:from-amber-600 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                        {loading ? (
                            <div className="flex items-center justify-center space-x-2 space-x-reverse">
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                                <span>در حال ذخیره...</span>
                            </div>
                        ) : (
                            isEdit ? 'به‌روزرسانی محصول' : 'ایجاد محصول'
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default AdminProductForm;