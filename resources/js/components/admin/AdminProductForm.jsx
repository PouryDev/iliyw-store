import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import FileUpload from './FileUpload';
import ModernSelect from './ModernSelect';
import ModernCheckbox from './ModernCheckbox';
import AutocompleteSelect from './AutocompleteSelect';
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
        has_sizes: false
    });
    
    const [variants, setVariants] = useState([]);
    const [images, setImages] = useState([]);
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
                                has_sizes: product.has_sizes ?? false
                            });
                            // Convert existing images to FileUpload format
                            const existingImages = (product.images || []).map((img, index) => ({
                                id: img.id || `existing-${index}`,
                                url: img.url,
                                isNew: false,
                                preview: img.url
                            }));
                            setImages(existingImages);
                            // Normalize variant values: convert to new format with color/size objects
                            const normalizedVariants = (product.variants || []).map(variant => ({
                                ...variant,
                                color: variant.color ? { id: variant.color.id } : (variant.color_id ? { id: variant.color_id } : null),
                                size: variant.size ? { id: variant.size.id } : (variant.size_id ? { id: variant.size_id } : null),
                                color_hex_code: variant.color?.hex_code || '',
                                price: variant.price && variant.price !== 0 ? variant.price : '',
                                stock: variant.stock && variant.stock !== 0 ? variant.stock : ''
                            }));
                            setVariants(normalizedVariants);
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
            color: null,
            size: null,
            color_hex_code: '',
            price: form.price && form.price !== 0 ? form.price : '',
            stock: form.stock && form.stock !== 0 ? form.stock : '',
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
                // Color: send id if exists, otherwise send name and hex_code
                if (variant.color?.id) {
                    formData.append(`variants[${index}][color_id]`, variant.color.id);
                } else if (variant.color?.name) {
                    formData.append(`variants[${index}][color_name]`, variant.color.name);
                    if (variant.color_hex_code) {
                        formData.append(`variants[${index}][color_hex_code]`, variant.color_hex_code);
                    }
                }
                
                // Size: send id if exists, otherwise send name
                if (variant.size?.id) {
                    formData.append(`variants[${index}][size_id]`, variant.size.id);
                } else if (variant.size?.name) {
                    formData.append(`variants[${index}][size_name]`, variant.size.name);
                }
                
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
                        // Refresh colors and sizes after successful save
                        const [colorsRes, sizesRes] = await Promise.all([
                            apiRequest('/api/admin/colors'),
                            apiRequest('/api/admin/sizes')
                        ]);
                        if (colorsRes.ok) {
                            const colorsData = await colorsRes.json();
                            if (colorsData.success) setColors(colorsData.data);
                        }
                        if (sizesRes.ok) {
                            const sizesData = await sizesRes.json();
                            if (sizesData.success) setSizes(sizesData.data);
                        }
                        // Normalize variants to new format
                        const normalizedVariants = (product.variants || []).map(variant => ({
                            ...variant,
                            color: variant.color ? { id: variant.color.id } : (variant.color_id ? { id: variant.color_id } : null),
                            size: variant.size ? { id: variant.size.id } : (variant.size_id ? { id: variant.size_id } : null),
                            color_hex_code: variant.color?.hex_code || '',
                            price: variant.price && variant.price !== 0 ? variant.price : '',
                            stock: variant.stock && variant.stock !== 0 ? variant.stock : ''
                        }));
                        setVariants(normalizedVariants);
                    } else {
                        // For create, refresh colors and sizes then redirect
                        const [colorsRes, sizesRes] = await Promise.all([
                            apiRequest('/api/admin/colors'),
                            apiRequest('/api/admin/sizes')
                        ]);
                        if (colorsRes.ok) {
                            const colorsData = await colorsRes.json();
                            if (colorsData.success) setColors(colorsData.data);
                        }
                        if (sizesRes.ok) {
                            const sizesData = await sizesRes.json();
                            if (sizesData.success) setSizes(sizesData.data);
                        }
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

                    <div className="mt-6 flex flex-wrap gap-6">
                        <ModernCheckbox
                            name="is_active"
                            checked={form.is_active}
                            onChange={(e) => setForm(prev => ({ ...prev, is_active: e.target.checked }))}
                            label="فعال"
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
                            label="دارای رنگ"
                        />

                        <ModernCheckbox
                            name="has_sizes"
                            checked={form.has_sizes}
                            onChange={(e) => setForm(prev => ({ ...prev, has_sizes: e.target.checked }))}
                            label="دارای سایز"
                        />
                    </div>
                </div>

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
                                            <AutocompleteSelect
                                                options={colors.map(color => ({ id: color.id, name: color.name }))}
                                                value={variant.color}
                                                onChange={(value) => updateVariant(index, 'color', value)}
                                                placeholder="نام رنگ را تایپ کنید..."
                                            />
                                            {variant.color && (
                                                <div className="mt-2">
                                                    <label className="block text-white text-sm font-medium mb-1">کد رنگ (Hex)</label>
                                                    <div className="flex gap-2">
                                                        <input
                                                            type="text"
                                                            placeholder="#FF0000"
                                                            value={variant.color_hex_code || ''}
                                                            onChange={(e) => updateVariant(index, 'color_hex_code', e.target.value)}
                                                            pattern="^#[0-9A-Fa-f]{6}$"
                                                            className="flex-1 bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                                                        />
                                                        {variant.color_hex_code && (
                                                            <div 
                                                                className="w-10 h-10 rounded border border-white/20"
                                                                style={{ backgroundColor: variant.color_hex_code }}
                                                            />
                                                        )}
                                                    </div>
                                                </div>
                                            )}
                                        </div>

                                        <div>
                                            <label className="block text-white font-medium mb-2">سایز</label>
                                            <AutocompleteSelect
                                                options={sizes.map(size => ({ id: size.id, name: size.name }))}
                                                value={variant.size}
                                                onChange={(value) => updateVariant(index, 'size', value)}
                                                placeholder="نام سایز را تایپ کنید..."
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
                        className="flex-1 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
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