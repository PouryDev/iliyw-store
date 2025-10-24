import React, { useState, useEffect, useCallback } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';
import ProductCard from './ProductCard';
import LoadingSpinner from './LoadingSpinner';
import SearchDropdown from './SearchDropdown';
import { useSeo } from '../hooks/useSeo';

function ShopPage() {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [searchQuery, setSearchQuery] = useState('');
    const [campaigns, setCampaigns] = useState([]);
    const [categories, setCategories] = useState([]);
    const [bestSellers, setBestSellers] = useState([]);
    const [searchParams, setSearchParams] = useSearchParams();
    const navigate = useNavigate();

    // SEO
    useSeo({
        title: 'فروشگاه آنلاین تابلوهای هنری iliyw - خرید تابلو با کیفیت بالا',
        description: 'فروشگاه آنلاین تابلوهای هنری iliyw با تمرکز روی کیفیت، هنر اصیل و تجربه خرید آسان. تابلوهای مدرن، کلاسیک، موزیکال و تابلوهای هنری با قیمت مناسب.',
        keywords: 'فروشگاه تابلو, خرید آنلاین تابلو, تابلو مدرن, تابلو موزیکال, تابلو دکوری, تابلو هنری, iliyw',
        image: '/images/logo.png',
        canonical: window.location.origin + '/products'
    });

    // Function to get emoji for category
    const getCategoryEmoji = (categoryName) => {
        const emojiMap = {
            'لباس': '👕',
            'شلوار': '👖',
            'کفش': '👟',
            'کیف': '👜',
            'ساعت': '⌚',
            'عینک': '🕶️',
            'جواهرات': '💍',
            'اکسسوری': '🎀',
            'مردانه': '👔',
            'زنانه': '👗',
            'بچه': '👶',
            'ورزشی': '🏃',
            'رسمی': '🤵',
            'کژوال': '👕',
            'تابستانی': '☀️',
            'زمستانی': '❄️'
        };
        
        // Try to find exact match first
        if (emojiMap[categoryName]) {
            return emojiMap[categoryName];
        }
        
        // Try to find partial match
        for (const [key, emoji] of Object.entries(emojiMap)) {
            if (categoryName.includes(key) || key.includes(categoryName)) {
                return emoji;
            }
        }
        
        // Default emoji
        return '🛍️';
    };

    // Initialize search query from URL
    useEffect(() => {
        const q = searchParams.get('q');
        if (q) {
            setSearchQuery(q);
        }
    }, [searchParams]);

    // Fetch products
    const fetchProducts = useCallback(async (page = 1, query = '', append = false) => {
        setLoading(true);
        
        try {
            const params = new URLSearchParams();
            if (query) params.set('q', query);
            if (page > 1) params.set('page', page);
            
            const response = await apiRequest(`/api/products?${params.toString()}`);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data.success) {
                if (append) {
                    setProducts(prev => [...prev, ...data.data]);
                } else {
                    setProducts(data.data);
                }
                setHasMorePages(data.pagination.has_more_pages);
                setCurrentPage(page);
            }
        } catch (error) {
            console.error('Error fetching products:', error);
        } finally {
            setLoading(false);
        }
    }, []);

    // Fetch campaigns
    const fetchCampaigns = useCallback(async () => {
        try {
            const response = await apiRequest('/api/campaigns/active');
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data.success) {
                setCampaigns(data.data);
            }
        } catch (error) {
            console.error('Error fetching campaigns:', error);
        }
    }, []);

    // Fetch categories
    const fetchCategories = useCallback(async () => {
        try {
            const res = await apiRequest('/api/categories');
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            if (data.success) setCategories(data.data || []);
        } catch {}
    }, []);

    // Fetch best sellers (fallback to random if not available)
    const fetchBestSellers = useCallback(async () => {
        try {
            const res = await apiRequest('/api/products?per_page=8&sort=random');
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            if (data.success) setBestSellers(data.data || []);
        } catch {}
    }, []);

    // Load initial products and campaigns
    useEffect(() => {
        fetchProducts(1, searchQuery);
        fetchCampaigns();
        fetchCategories();
        fetchBestSellers();
    }, [fetchProducts, fetchCampaigns, fetchCategories, fetchBestSellers, searchQuery]);

    // Handle search from URL parameters
    const handleSearchFromURL = (query) => {
        setSearchQuery(query);
        setCurrentPage(1);
        setProducts([]);
        fetchProducts(1, query);
    };

    // Load more products (infinite scroll)
    const loadMore = () => {
        if (!loading && hasMorePages) {
            fetchProducts(currentPage + 1, searchQuery, true);
        }
    };

    // Handle search from URL on initial load
    useEffect(() => {
        const q = searchParams.get('q');
        if (q && q !== searchQuery) {
            handleSearchFromURL(q);
        }
    }, [searchParams]);

    // Intersection Observer for infinite scroll
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && !loading && hasMorePages) {
                    loadMore();
                }
            },
            { threshold: 0.1 }
        );

        const loadMoreBtn = document.getElementById('load-more-btn');
        if (loadMoreBtn) {
            observer.observe(loadMoreBtn);
        }

        return () => observer.disconnect();
    }, [loading, hasMorePages, loadMore]);

    return (
        <div className="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white">
            {/* Categories Carousel */}
            {categories.length > 0 && (
                <section className="px-4 py-4">
                    <div className="max-w-7xl mx-auto">
                        <div className="flex gap-2 overflow-x-auto snap-x snap-mandatory pb-2 [-ms-overflow-style:none] [scrollbar-width:none]" style={{ WebkitOverflowScrolling: 'touch' }}>
                            {categories.map((category) => (
                                <Link
                                    key={category.id}
                                    to={`/category/${category.id}`}
                                    className="snap-start shrink-0 flex-none"
                                >
                                    <div className="flex items-center gap-2 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-full px-4 py-2 transition-all duration-200 min-w-fit">
                                        <span className="text-lg">{getCategoryEmoji(category.name)}</span>
                                        <span className="text-white text-sm font-medium whitespace-nowrap">{category.name}</span>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>
            )}
            {/* Search Bar */}
            <section className="px-4 mb-6">
                <div className="max-w-7xl mx-auto">
                    <SearchDropdown initialQuery={searchQuery} />
                </div>
            </section>

            

            {/* Best Sellers Carousel */}
            {bestSellers.length > 0 && (
                <section className="px-4 mb-8">
                    <div className="max-w-7xl mx-auto">
                        <h2 className="text-white font-bold text-lg mb-4">محبوب‌ترین‌ها</h2>
                        <div className="flex gap-3 overflow-x-auto snap-x snap-mandatory pb-3 [-ms-overflow-style:none] [scrollbar-width:none]" style={{ WebkitOverflowScrolling: 'touch' }}>
                            {bestSellers.map((p, i) => (
                                <div key={p.id} className="snap-start w-[200px] sm:w-72 shrink-0 flex-none">
                                    <ProductCard product={p} index={i} />
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Instagram Banner */}
            <section className="px-4 mb-8">
                <div className="max-w-7xl mx-auto">
                    <a 
                        href="https://instagram.com/iliywstore" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        className="block group"
                    >
                        <div className="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-500 via-indigo-500 to-indigo-500 p-1 shadow-2xl">
                            <div className="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 rounded-xl p-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex-1">
                                        <div className="flex items-center gap-3 mb-3">
                                            <div className="w-12 h-12 bg-gradient-to-r from-indigo-500 to-indigo-500 rounded-full flex items-center justify-center">
                                                <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 className="text-white font-bold text-lg">iliywstore</h3>
                                                <p className="text-gray-300 text-sm">اینستاگرام رسمی</p>
                                            </div>
                                        </div>
                                        <h2 className="text-white font-bold text-xl mb-2">
                                            🎉 جدیدترین محصولات و تخفیف‌های ویژه
                                        </h2>
                                        <p className="text-gray-300 text-sm mb-4">
                                            برای دیدن آخرین محصولات، استایل‌های جدید و تخفیف‌های ویژه ما را در اینستاگرام دنبال کنید
                                        </p>
                                        <div className="flex items-center gap-2 text-pink-400 font-semibold text-sm group-hover:text-pink-300 transition-colors">
                                            <span>فالو کنید</span>
                                            <svg className="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div className="hidden md:block">
                                        <div className="relative">
                                            <div className="w-24 h-24 bg-gradient-to-r from-indigo-500/20 to-indigo-500/20 rounded-full flex items-center justify-center">
                                                <div className="text-4xl">📱</div>
                                            </div>
                                            <div className="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-r from-indigo-500 to-indigo-500 rounded-full flex items-center justify-center animate-pulse">
                                                <span className="text-white text-xs font-bold">+</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </section>

            {/* Category Carousels */}
            {categories.slice(0, 5).map((cat) => (
                <section key={cat.id} className="px-4 mb-8">
                    <div className="max-w-7xl mx-auto">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-white font-bold text-lg">{cat.name}</h2>
                            <Link 
                                to={`/category/${cat.id}`} 
                                className="text-sm text-amber-400 hover:text-amber-300 transition-colors"
                            >
                                مشاهده همه
                            </Link>
                        </div>
                        <CategoryCarousel categoryId={cat.id} />
                    </div>
                </section>
            ))}

            {/* Campaigns Banners Carousel */}
            {campaigns.length > 0 && (
                <section className="px-4 mb-10">
                    <div className="max-w-7xl mx-auto">
                        <h2 className="text-white font-bold text-lg mb-4">کمپین‌ها</h2>
                        <div className="flex gap-3 overflow-x-auto snap-x snap-mandatory pb-3 [-ms-overflow-style:none] [scrollbar-width:none]" style={{ WebkitOverflowScrolling: 'touch' }}>
                            {campaigns.map((campaign) => (
                                <div key={campaign.id} className="snap-start w-[320px] sm:w-96 shrink-0 flex-none">
                                    <BannerCard campaign={campaign} />
                                </div>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* Products Grid */}
            <section className="px-4">
                <div className="max-w-7xl mx-auto">
                    {loading && products.length === 0 ? (
                        <div className="flex justify-center py-12">
                            <LoadingSpinner />
                        </div>
                    ) : products.length === 0 ? (
                        <div className="text-center py-12">
                            <div className="text-6xl mb-4">🔍</div>
                            <h3 className="text-xl font-semibold text-white mb-2">محصولی یافت نشد</h3>
                            <p className="text-gray-400 mb-4">
                                متأسفانه محصولی با این کلمات پیدا نشد
                            </p>
                            <button
                                onClick={() => {
                                    setSearchQuery('');
                                    setCurrentPage(1);
                                    setProducts([]);
                                    const newSearchParams = new URLSearchParams();
                                    setSearchParams(newSearchParams);
                                    fetchProducts(1, '');
                                }}
                                className="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                            >
                                پاک کردن جستجو
                            </button>
                        </div>
                    ) : (
                        <>
                            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                                {products.map((product, index) => (
                                    <ProductCard
                                        key={product.id}
                                        product={product}
                                        index={index}
                                    />
                                ))}
                            </div>

                            {/* Load More Button */}
                            {hasMorePages && (
                                <div id="load-more-container" className="text-center mt-8">
                                    <button
                                        id="load-more-btn"
                                        onClick={loadMore}
                                        disabled={loading}
                                        className="bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200"
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


// Campaign Card Component
function CampaignCard({ campaign }) {
    return (
        <div className="bg-gradient-to-r from-amber-600/20 to-indigo-600/20 border border-amber-500/30 rounded-xl p-6 backdrop-blur-sm">
            <div className="flex items-center justify-between mb-4">
                <h3 className="text-xl font-bold text-white">{campaign.name}</h3>
                <span className="bg-amber-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                    {campaign.discount_percentage}% تخفیف
                </span>
            </div>
            {campaign.description && (
                <p className="text-gray-300 text-sm mb-4">{campaign.description}</p>
            )}
            <div className="text-xs text-gray-400">
                تا {new Date(campaign.ends_at).toLocaleDateString('fa-IR')}
            </div>
        </div>
    );
}

export default ShopPage;

// Category carousel that fetches products by category
function CategoryCarousel({ categoryId }) {
    const [items, setItems] = React.useState([]);
    React.useEffect(() => {
        (async () => {
            try {
                const res = await apiRequest(`/api/products?per_page=10&category_id=${categoryId}`);
                if (!res.ok) throw new Error('failed');
                const data = await res.json();
                if (data.success) setItems(data.data || []);
            } catch {}
        })();
    }, [categoryId]);
    return (
        <div className="flex gap-3 overflow-x-auto snap-x snap-mandatory pb-3 [-ms-overflow-style:none] [scrollbar-width:none]" style={{ WebkitOverflowScrolling: 'touch' }}>
            {items.map((p, i) => (
                <div key={p.id} className="snap-start w-[200px] sm:w-72 shrink-0 flex-none">
                    <ProductCard product={p} index={i} />
                </div>
            ))}
        </div>
    );
}

function BannerCard({ campaign }) {
    return (
        <div className="rounded-2xl overflow-hidden glass-card soft-shadow border border-white/10">
            <div className="h-36 bg-gradient-to-r from-amber-600/30 to-indigo-600/30 flex items-center justify-center text-white font-bold text-center px-4">
                {campaign.name}
            </div>
            {campaign.description && (
                <div className="p-3 text-gray-300 text-sm">{campaign.description}</div>
            )}
        </div>
    );
}
