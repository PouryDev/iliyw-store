import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';
import ProductCard from './ProductCard';
import LoadingSpinner from './LoadingSpinner';
import { useSeo } from '../hooks/useSeo';

function CategoryPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [category, setCategory] = useState(null);
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);

    // SEO
    useSeo({
        title: category ? `${category.name} - ایلی استور` : 'دسته‌بندی محصولات - ایلی استور',
        description: category ? `خرید ${category.name} با کیفیت از فروشگاه ایلی استور. اکسسوری و تابلو با قیمت مناسب.` : 'دسته‌بندی محصولات فروشگاه ایلی استور',
        keywords: category ? `${category.name}, اکسسوری, تابلو, ایلی استور, خرید آنلاین` : 'دسته‌بندی محصولات, اکسسوری, تابلو, ایلی استور',
        image: '/images/logo.png',
        canonical: window.location.origin + `/categories/${id}`
    });

    const fetchCategory = useCallback(async () => {
        try {
            const res = await apiRequest('/api/categories');
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            const cat = data.data?.find(c => c.id === Number(id));
            if (!cat) {
                navigate('/404', { replace: true });
                return;
            }
            setCategory(cat);
        } catch (e) {
            navigate('/404', { replace: true });
        }
    }, [id, navigate]);

    const fetchProducts = useCallback(async (page = 1, append = false) => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            params.set('category_id', id);
            if (page > 1) params.set('page', page);
            
            const res = await apiRequest(`/api/products?${params.toString()}`);
            
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            
            if (data.success) {
                if (append) {
                    setProducts(prev => [...prev, ...data.data]);
                } else {
                    setProducts(data.data);
                }
                setHasMorePages(data.pagination.has_more_pages);
                setCurrentPage(page);
            }
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    }, [id]);

    useEffect(() => {
        fetchCategory();
        fetchProducts(1);
    }, [fetchCategory, fetchProducts]);

    const loadMore = () => {
        if (!loading && hasMorePages) {
            fetchProducts(currentPage + 1, true);
        }
    };

    useSeo({
        title: category ? `${category.name} - فروشگاه ایلی استور` : 'دسته‌بندی - ایلی استور',
        description: category ? `مشاهده و خرید ${category.name} با بهترین قیمت` : 'دسته‌بندی محصولات',
        keywords: category ? `${category.name}, خرید ${category.name}, خرید آنلاین` : '',
        canonical: window.location.origin + window.location.pathname
    });

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white anim-page">
            {/* Header */}
            <section className="relative py-10 md:py-14 px-4">
                <div className="max-w-7xl mx-auto">
                    <div className="rounded-2xl glass-card soft-shadow p-5 md:p-7 border border-white/10">
                        <button onClick={() => navigate(-1)} className="text-cherry-400 hover:text-cherry-300 text-sm mb-3 flex items-center gap-1">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/></svg>
                            بازگشت
                        </button>
                        {category ? (
                            <>
                                <h1 className="text-2xl md:text-3xl font-extrabold text-white">{category.name}</h1>
                                <p className="text-sm text-gray-300 mt-1">مشاهده همه محصولات</p>
                            </>
                        ) : (
                            <div className="text-gray-300">در حال بارگذاری...</div>
                        )}
                    </div>
                </div>
            </section>

            {/* Products Grid */}
            <section className="px-4 pb-8">
                <div className="max-w-7xl mx-auto">
                    {loading && products.length === 0 ? (
                        <div className="flex justify-center py-12">
                            <LoadingSpinner />
                        </div>
                    ) : products.length === 0 ? (
                        <div className="text-center py-12">
                            <div className="text-6xl mb-4">📦</div>
                            <h3 className="text-xl font-semibold text-white mb-2">محصولی یافت نشد</h3>
                            <p className="text-gray-400">در این دسته‌بندی محصولی وجود ندارد</p>
                        </div>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                                {products.map((product, index) => (
                                    <ProductCard key={product.id} product={product} index={index} />
                                ))}
                            </div>

                            {hasMorePages && (
                                <div className="text-center mt-8">
                                    <button
                                        onClick={loadMore}
                                        disabled={loading}
                                        className="bg-cherry-600 hover:bg-cherry-700 disabled:opacity-50 text-white px-6 py-3 rounded-lg font-semibold transition-colors"
                                    >
                                        {loading ? (
                                            <span className="flex items-center gap-2">
                                                <LoadingSpinner size="sm" />
                                                <span>در حال بارگذاری...</span>
                                            </span>
                                        ) : (
                                            'مشاهده بیشتر'
                                        )}
                                    </button>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </section>
        </div>
    );
}

export default CategoryPage;

