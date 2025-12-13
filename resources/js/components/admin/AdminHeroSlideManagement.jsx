import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { adminApiRequest } from '../../utils/adminApi';

function AdminHeroSlideManagement() {
    const navigate = useNavigate();
    const [slides, setSlides] = useState([]);
    const [loading, setLoading] = useState(true);
    const [draggedIndex, setDraggedIndex] = useState(null);

    useEffect(() => {
        loadSlides();
    }, []);

    const loadSlides = async () => {
        try {
            setLoading(true);
            const res = await adminApiRequest('/hero-slides');
            
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    setSlides(data.data);
                }
            }
        } catch (error) {
            console.error('Failed to load slides:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleDelete = async (slideId) => {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این اسلاید را حذف کنید؟')) {
            return;
        }

        try {
            const res = await adminApiRequest(`/hero-slides/${slideId}`, { method: 'DELETE' });

            if (res.ok) {
                setSlides(slides.filter(s => s.id !== slideId));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'اسلاید با موفقیت حذف شد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to delete slide:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در حذف اسلاید' } 
            }));
        }
    };

    const toggleStatus = async (slideId, currentStatus) => {
        try {
            const res = await adminApiRequest(`/hero-slides/${slideId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ is_active: !currentStatus })
            });

            if (res.ok) {
                setSlides(slides.map(s => 
                    s.id === slideId 
                        ? { ...s, is_active: !currentStatus }
                        : s
                ));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'وضعیت اسلاید تغییر کرد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to toggle slide:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در تغییر وضعیت' } 
            }));
        }
    };

    const handleDragStart = (index) => {
        setDraggedIndex(index);
    };

    const handleDragOver = (e) => {
        e.preventDefault();
    };

    const handleDrop = async (dropIndex) => {
        if (draggedIndex === null || draggedIndex === dropIndex) {
            setDraggedIndex(null);
            return;
        }

        const newSlides = [...slides];
        const draggedSlide = newSlides[draggedIndex];
        newSlides.splice(draggedIndex, 1);
        newSlides.splice(dropIndex, 0, draggedSlide);

        // Update sort_order
        const updatedSlides = newSlides.map((slide, index) => ({
            id: slide.id,
            sort_order: index
        }));

        try {
            const res = await adminApiRequest('/hero-slides/update-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ slides: updatedSlides })
            });

            if (res.ok) {
                setSlides(newSlides);
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'ترتیب اسلایدها به‌روزرسانی شد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to update order:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در به‌روزرسانی ترتیب' } 
            }));
        }

        setDraggedIndex(null);
    };

    const getLinkLabel = (slide) => {
        if (slide.link_type === 'custom') {
            return slide.custom_url || 'URL سفارشی';
        }
        if (slide.linkable) {
            if (slide.link_type === 'product') {
                return `محصول: ${slide.linkable.title}`;
            }
            if (slide.link_type === 'category') {
                return `دسته‌بندی: ${slide.linkable.name}`;
            }
            if (slide.link_type === 'campaign') {
                return `کمپین: ${slide.linkable.name}`;
            }
        }
        return 'بدون لینک';
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
                        <h1 className="text-3xl font-bold text-white mb-2">مدیریت اسلایدهای Hero</h1>
                        <p className="text-gray-400">مدیریت و ویرایش اسلایدهای صفحه اصلی</p>
                    </div>
                    <button
                        onClick={() => navigate('/admin/hero-slides/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg flex items-center space-x-2 space-x-reverse"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>اسلاید جدید</span>
                    </button>
                </div>
            </div>

            {/* Slides List */}
            <div className="space-y-4">
                {slides.map((slide, index) => (
                    <div
                        key={slide.id}
                        draggable
                        onDragStart={() => handleDragStart(index)}
                        onDragOver={handleDragOver}
                        onDrop={() => handleDrop(index)}
                        className={`bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-2xl p-6 cursor-move transition-all ${
                            draggedIndex === index ? 'opacity-50' : 'hover:border-white/20'
                        }`}
                    >
                        <div className="flex flex-col lg:flex-row gap-6">
                            {/* Slide Image */}
                            <div className="flex-shrink-0">
                                {slide.image_url ? (
                                    <img
                                        src={slide.image_url}
                                        alt={slide.title || 'اسلاید'}
                                        className="w-32 h-32 object-cover rounded-lg"
                                    />
                                ) : (
                                    <div className="w-32 h-32 bg-gray-800 rounded-lg flex items-center justify-center">
                                        <svg className="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                )}
                            </div>

                            {/* Slide Info */}
                            <div className="flex-1">
                                <div className="flex items-center gap-4 mb-4">
                                    <h3 className="text-white font-bold text-xl">
                                        {slide.title || 'اسلاید بدون عنوان'}
                                    </h3>
                                    <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                        slide.is_active 
                                            ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                            : 'bg-red-500/20 text-red-400 border border-red-500/30'
                                    }`}>
                                        {slide.is_active ? 'فعال' : 'غیرفعال'}
                                    </span>
                                    <span className="px-3 py-1 rounded-full text-sm font-medium bg-purple-500/20 text-purple-400 border border-purple-500/30">
                                        ترتیب: {slide.sort_order + 1}
                                    </span>
                                </div>

                                {slide.subtitle && (
                                    <p className="text-gray-300 mb-2">{slide.subtitle}</p>
                                )}

                                {slide.description && (
                                    <p className="text-gray-400 text-sm mb-4 line-clamp-2">{slide.description}</p>
                                )}

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p className="text-gray-400 text-sm">نوع لینک</p>
                                        <p className="text-white font-medium">
                                            {slide.link_type === 'product' ? 'محصول' :
                                             slide.link_type === 'category' ? 'دسته‌بندی' :
                                             slide.link_type === 'campaign' ? 'کمپین' : 'سفارشی'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">لینک</p>
                                        <p className="text-white font-medium text-sm truncate">
                                            {getLinkLabel(slide)}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">تعداد کلیک</p>
                                        <p className="text-white font-medium">
                                            {slide.click_count || 0} کلیک
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">متن دکمه</p>
                                        <p className="text-white font-medium">
                                            {slide.button_text || 'بدون دکمه'}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex flex-col gap-2">
                                <button
                                    onClick={() => navigate(`/admin/hero-slides/${slide.id}/edit`)}
                                    className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                >
                                    ویرایش
                                </button>
                                <button
                                    onClick={() => toggleStatus(slide.id, slide.is_active)}
                                    className={`font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm ${
                                        slide.is_active
                                            ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white'
                                            : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white'
                                    }`}
                                >
                                    {slide.is_active ? 'غیرفعال' : 'فعال'}
                                </button>
                                <button
                                    onClick={() => handleDelete(slide.id)}
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
            {slides.length === 0 && !loading && (
                <div className="text-center py-12">
                    <div className="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">اسلایدی یافت نشد</h3>
                    <p className="text-gray-400 mb-6">هنوز اسلایدی ایجاد نکرده‌اید</p>
                    <button
                        onClick={() => navigate('/admin/hero-slides/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg"
                    >
                        اولین اسلاید را ایجاد کنید
                    </button>
                </div>
            )}
        </div>
    );
}

export default AdminHeroSlideManagement;

