import React, { useState, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import ModernSelect from './ModernSelect';
import { apiRequest } from '../../utils/sanctumAuth';
import { useInfiniteScroll } from '../../hooks/useInfiniteScroll';

function AdminProductManagement() {
    const navigate = useNavigate();
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');

    // Debounce search term
    const [debouncedSearch, setDebouncedSearch] = useState('');
    React.useEffect(() => {
        const timer = setTimeout(() => {
            setDebouncedSearch(searchTerm);
        }, 500);
        return () => clearTimeout(timer);
    }, [searchTerm]);

    // Fetch function for infinite scroll
    const fetchProducts = async (page, perPage, search, filters) => {
        const params = new URLSearchParams({
            page: page.toString(),
            per_page: perPage.toString(),
        });

        if (search) {
            params.append('search', search);
        }

        // Note: is_active filter is done client-side since API doesn't support it

        const res = await apiRequest(`/api/admin/products?${params.toString()}`);
        
        if (res.ok) {
            const data = await res.json();
            if (data.success) {
                // Client-side status filtering
                let filteredData = data.data;
                if (filters.status && filters.status !== 'all') {
                    filteredData = filteredData.filter(p => 
                        filters.status === 'active' ? p.is_active : !p.is_active
                    );
                }
                
                return {
                    data: filteredData,
                    pagination: data.pagination
                };
            }
        }
        
        throw new Error('Failed to load products');
    };

    const { items: products, loading, hasMore, error, total, observerTarget, refresh } = useInfiniteScroll(
        fetchProducts,
        {
            perPage: 20,
            search: debouncedSearch,
            filters: { status: filterStatus }
        }
    );

    const formatPrice = (value) => {
        try { 
            return Number(value || 0).toLocaleString('fa-IR'); 
        } catch { 
            return value || '0'; 
        }
    };

    const handleToggleProduct = async (productId) => {
        const product = products.find(p => p.id === productId);
        if (!product) return;

        const newStatus = !product.is_active;
        const actionText = newStatus ? 'فعال' : 'غیرفعال';

        try {
            const res = await apiRequest(`/api/admin/products/${productId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    title: product.title,
                    description: product.description || '',
                    price: product.price,
                    stock: product.stock,
                    category_id: product.category_id || null,
                    has_variants: product.has_variants || false,
                    has_colors: product.has_colors || false,
                    has_sizes: product.has_sizes || false,
                    is_active: newStatus,
                }),
            });

            if (res.ok) {
                // Refresh the list to get updated data
                refresh();
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: `محصول با موفقیت ${actionText} شد` } 
                }));
            }
        } catch (error) {
            console.error('Failed to toggle product status:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: `خطا در ${actionText} کردن محصول` } 
            }));
        }
    };

    if (loading && products.length === 0) {
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
                        <h1 className="text-3xl font-bold text-white mb-2">مدیریت محصولات</h1>
                        <p className="text-gray-400">
                            مدیریت و ویرایش محصولات فروشگاه
                            {total > 0 && <span className="mr-2">({total.toLocaleString('fa-IR')} محصول)</span>}
                        </p>
                    </div>
                    <button
                        onClick={() => navigate('/admin/products/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg flex items-center space-x-2 space-x-reverse"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>محصول جدید</span>
                    </button>
                </div>
            </div>

            {/* Filters */}
            <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 p-6 mb-8">
                <div className="flex flex-col sm:flex-row gap-4">
                    <div className="flex-1">
                        <input
                            type="text"
                            placeholder="جستجو در محصولات..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                        />
                    </div>
                    <div className="sm:w-48">
                        <ModernSelect
                            value={filterStatus}
                            onChange={(value) => setFilterStatus(value)}
                            options={[
                                { value: 'all', label: 'همه محصولات' },
                                { value: 'active', label: 'فعال' },
                                { value: 'inactive', label: 'غیرفعال' }
                            ]}
                            placeholder="فیلتر وضعیت"
                        />
                    </div>
                </div>
            </div>

            {/* Error Message */}
            {error && (
                <div className="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                    <p className="text-red-400">{error}</p>
                </div>
            )}

            {/* Products Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {products.map((product) => (
                    <div key={product.id} className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-2xl overflow-hidden hover:shadow-purple-500/20 transition-all duration-200">
                        {/* Product Image */}
                        <div className="aspect-square bg-gray-800 relative overflow-hidden">
                            {product.images && product.images.length > 0 ? (
                                <img 
                                    src={product.images[0].url} 
                                    alt={product.title}
                                    className="w-full h-full object-cover"
                                />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center text-6xl">📦</div>
                            )}
                            
                            {/* Status Badge */}
                            <div className="absolute top-3 right-3">
                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                    product.is_active 
                                        ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                        : 'bg-red-500/20 text-red-400 border border-red-500/30'
                                }`}>
                                    {product.is_active ? 'فعال' : 'غیرفعال'}
                                </span>
                            </div>
                        </div>

                        {/* Product Info */}
                        <div className="p-6">
                            <h3 className="text-white font-bold text-lg mb-2 line-clamp-2">{product.title}</h3>
                            <p className="text-gray-400 text-sm mb-4 line-clamp-3">{product.description}</p>
                            
                            <div className="flex items-center justify-between mb-4">
                                <div>
                                    <p className="text-purple-400 font-bold text-lg">{formatPrice(product.price)} تومان</p>
                                    <p className="text-gray-400 text-sm">موجودی: {formatPrice(product.stock)}</p>
                                </div>
                                {product.has_variants && (
                                    <span className="px-2 py-1 bg-blue-500/20 text-blue-400 rounded-full text-xs">
                                        دارای تنوع
                                    </span>
                                )}
                            </div>

                            {/* Actions */}
                            <div className="flex gap-2">
                                <button
                                    onClick={() => navigate(`/admin/products/${product.id}/edit`)}
                                    className="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                >
                                    ویرایش
                                </button>
                                <button
                                    onClick={() => handleToggleProduct(product.id)}
                                    className={`font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm ${
                                        product.is_active
                                            ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white'
                                            : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white'
                                    }`}
                                >
                                    {product.is_active ? 'غیرفعال' : 'فعال'}
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Infinite Scroll Trigger */}
            {hasMore && (
                <div ref={observerTarget} className="flex justify-center py-8">
                    {loading && (
                        <div className="text-center">
                            <div className="w-8 h-8 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-2"></div>
                            <p className="text-gray-400 text-sm">در حال بارگذاری بیشتر...</p>
                        </div>
                    )}
                </div>
            )}

            {/* End of List Message */}
            {!hasMore && products.length > 0 && (
                <div className="text-center py-8">
                    <p className="text-gray-400 text-sm">همه محصولات نمایش داده شد</p>
                </div>
            )}

            {/* Empty State */}
            {products.length === 0 && !loading && (
                <div className="text-center py-12">
                    <div className="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">محصولی یافت نشد</h3>
                    <p className="text-gray-400 mb-6">
                        {debouncedSearch || filterStatus !== 'all' 
                            ? 'هیچ محصولی با فیلترهای انتخابی یافت نشد' 
                            : 'هنوز محصولی اضافه نکرده‌اید'
                        }
                    </p>
                    {!debouncedSearch && filterStatus === 'all' && (
                        <button
                            onClick={() => navigate('/admin/products/create')}
                            className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg"
                        >
                            اولین محصول را اضافه کنید
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}

export default AdminProductManagement;
