import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import ModernSelect from './ModernSelect';
import { apiRequest } from '../../utils/sanctumAuth';

function AdminProductManagement() {
    const navigate = useNavigate();
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all');

    useEffect(() => {
        const loadProducts = async () => {
            try {
                setLoading(true);
                const res = await apiRequest('/api/admin/products');
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        setProducts(data.data);
                    }
                }
            } catch (error) {
                console.error('Failed to load products:', error);
            } finally {
                setLoading(false);
            }
        };

        loadProducts();
    }, []);

    const formatPrice = (value) => {
        try { 
            return Number(value || 0).toLocaleString('fa-IR'); 
        } catch { 
            return value || '0'; 
        }
    };

    const handleDeleteProduct = async (productId) => {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این محصول را حذف کنید؟')) {
            return;
        }

        try {
            const res = await apiRequest(`/api/admin/products/${productId}`, {
                method: 'DELETE',
            });

            if (res.ok) {
                setProducts(products.filter(p => p.id !== productId));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'محصول با موفقیت حذف شد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to delete product:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در حذف محصول' } 
            }));
        }
    };

    const filteredProducts = products.filter(product => {
        const matchesSearch = product.title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                            product.description?.toLowerCase().includes(searchTerm.toLowerCase());
        
        const matchesStatus = filterStatus === 'all' || 
                            (filterStatus === 'active' && product.is_active) ||
                            (filterStatus === 'inactive' && !product.is_active);
        
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
                        <h1 className="text-3xl font-bold text-white mb-2">مدیریت محصولات</h1>
                        <p className="text-gray-400">مدیریت و ویرایش محصولات فروشگاه</p>
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

            {/* Products Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {filteredProducts.map((product) => (
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
                                    onClick={() => handleDeleteProduct(product.id)}
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
            {filteredProducts.length === 0 && !loading && (
                <div className="text-center py-12">
                    <div className="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">محصولی یافت نشد</h3>
                    <p className="text-gray-400 mb-6">
                        {searchTerm || filterStatus !== 'all' 
                            ? 'هیچ محصولی با فیلترهای انتخابی یافت نشد' 
                            : 'هنوز محصولی اضافه نکرده‌اید'
                        }
                    </p>
                    {!searchTerm && filterStatus === 'all' && (
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
