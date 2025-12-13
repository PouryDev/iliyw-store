import React from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';
import ProductCard from './ProductCard';
import LoadingSpinner from './LoadingSpinner';

function ProductsPage() {
    const [searchParams, setSearchParams] = useSearchParams();
    const navigate = useNavigate();

    const [products, setProducts] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [loadingMore, setLoadingMore] = React.useState(false);
    const [hasMorePages, setHasMorePages] = React.useState(true);
    const [currentPage, setCurrentPage] = React.useState(1);

    const [categories, setCategories] = React.useState([]);
    const [colors, setColors] = React.useState([]);
    const [sizes, setSizes] = React.useState([]);

    const [filtersOpen, setFiltersOpen] = React.useState(false);

    const sort = searchParams.get('sort') || 'newest';
    const categoryId = searchParams.get('category_id') || '';
    const q = searchParams.get('q') || '';
    const minPrice = searchParams.get('min_price') || '';
    const maxPrice = searchParams.get('max_price') || '';
    const colorsParam = searchParams.get('colors') || '';
    const sizesParam = searchParams.get('sizes') || '';
    const selectedColors = colorsParam.split(',').filter(Boolean);
    const selectedSizes = sizesParam.split(',').filter(Boolean);

    const updateParam = (key, value) => {
        const next = new URLSearchParams(searchParams);
        if (value === '' || value == null) next.delete(key); else next.set(key, value);
        // Reset paging when filters change
        next.delete('page');
        setSearchParams(next);
        window.scrollTo({ top: 0, behavior: 'auto' });
    };

    const toggleMultiParam = (key, id) => {
        const list = (searchParams.get(key) || '').split(',').filter(Boolean);
        const idx = list.indexOf(String(id));
        if (idx >= 0) list.splice(idx, 1); else list.push(String(id));
        updateParam(key, list.join(','));
    };

    const fetchProducts = React.useCallback(async (page = 1, append = false) => {
        if (append) setLoadingMore(true); else setLoading(true);
        try {
            const params = new URLSearchParams();
            if (q) params.set('q', q);
            if (categoryId) params.set('category_id', categoryId);
            if (minPrice) params.set('min_price', minPrice);
            if (maxPrice) params.set('max_price', maxPrice);
            if (selectedColors.length) params.set('colors', selectedColors.join(','));
            if (selectedSizes.length) params.set('sizes', selectedSizes.join(','));
            if (sort) params.set('sort', sort);
            if (page > 1) params.set('page', String(page));

            const res = await apiRequest(`/api/products?${params.toString()}`);
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            if (data.success) {
                if (append) setProducts(prev => [...prev, ...data.data]);
                else setProducts(data.data);
                setHasMorePages(data.pagination.has_more_pages);
                setCurrentPage(page);
            }
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
            setLoadingMore(false);
        }
    }, [q, categoryId, minPrice, maxPrice, colorsParam, sizesParam, sort]);

    const loadMore = () => {
        if (!loadingMore && hasMorePages) {
            fetchProducts(currentPage + 1, true);
        }
    };

    


    React.useEffect(() => {
        (async () => {
            try {
                const [catRes, colRes, sizeRes] = await Promise.all([
                    apiRequest('/api/categories'),
                    apiRequest('/api/colors'),
                    apiRequest('/api/sizes'),
                ]);
                const cat = await catRes.json();
                const col = await colRes.json();
                const siz = await sizeRes.json();
                if (cat.success) setCategories(cat.data || []);
                if (col.success) setColors(col.data || []);
                if (siz.success) setSizes(siz.data || []);
            } catch {}
        })();
    }, []);

    React.useEffect(() => {
        fetchProducts(1, false);
    }, [fetchProducts]);

    React.useEffect(() => {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) loadMore();
        }, { threshold: 0.1 });
        const el = document.getElementById('products-infinite-sentinel');
        if (el) observer.observe(el);
        return () => observer.disconnect();
    }, [loadMore, loadingMore, hasMorePages]);

    const SortButton = ({ value, label }) => (
        <button
            className={`px-3 py-1.5 rounded-full text-sm font-medium border ${sort === value ? 'bg-cherry-600 text-white border-cherry-500' : 'bg-white/5 text-gray-200 border-white/10'}`}
            onClick={() => updateParam('sort', value)}
        >{label}</button>
    );

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
            {/* Sticky mobile header */}
            <div className="sticky top-0 z-30 bg-black/40 backdrop-blur border-b border-white/10">
                <div className="px-4 h-14 flex items-center justify-between">
                    <button onClick={() => navigate(-1)} className="text-gray-300">✕</button>
                    <div className="font-bold">محصولات</div>
                    <button onClick={() => setFiltersOpen(true)} className="text-cherry-400">فیلترها</button>
                </div>
                {/* Sort segmented */}
                <div className="px-4 pb-3">
                    <div className="flex gap-2 overflow-x-auto overflow-y-hidden no-scrollbar [-ms-overflow-style:none] [scrollbar-width:none] [touch-action:pan-x] touch-pan-x" style={{ WebkitOverflowScrolling: 'touch' }}>
                        <SortButton value="newest" label="جدیدترین" />
                        <SortButton value="best_seller" label="پرفروش" />
                        <SortButton value="cheapest" label="ارزان‌ترین" />
                        <SortButton value="priciest" label="گران‌ترین" />
                    </div>
                </div>
                {/* Category chips */}
                {categories.length > 0 && (
                    <div className="px-4 pb-3">
                        <div className="flex gap-2 overflow-x-auto overflow-y-hidden no-scrollbar [-ms-overflow-style:none] [scrollbar-width:none] [touch-action:pan-x] touch-pan-x" style={{ WebkitOverflowScrolling: 'touch' }}>
                            <button
                                className={`px-3 py-1 rounded-full text-sm border ${!categoryId ? 'bg-white/10 border-white/20' : 'border-white/10'}`}
                                onClick={() => updateParam('category_id', '')}
                            >همه</button>
                            {categories.map(cat => (
                                <button
                                    key={cat.id}
                                    className={`px-3 py-1 rounded-full text-sm border whitespace-nowrap ${String(categoryId) === String(cat.id) ? 'bg-cherry-600 text-white border-cherry-500' : 'bg-white/5 text-gray-200 border-white/10'}`}
                                    onClick={() => updateParam('category_id', String(cat.id))}
                                >{cat.name}</button>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Grid */}
            <section className="px-4 py-4">
                <div className="max-w-7xl mx-auto">
                    {loading && products.length === 0 ? (
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-5">
                            {Array.from({ length: 8 }).map((_, i) => (
                                <div key={i} className="h-64 rounded-2xl bg-white/5 animate-pulse" />
                            ))}
                        </div>
                    ) : (
                        <>
                            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-5">
                                {products.map((product, index) => (
                                    <ProductCard key={product.id} product={product} index={index} />
                                ))}
                            </div>
                            {hasMorePages && (
                                <div className="flex justify-center py-6">
                                    {loadingMore ? <LoadingSpinner /> : <div id="products-infinite-sentinel" className="h-4" />}
                                </div>
                            )}
                        </>
                    )}
                </div>
            </section>

            {/* Bottom sheet filters */}
            {filtersOpen && (
                <div className="fixed inset-0 z-40">
                    <div className="absolute inset-0 bg-black/40" onClick={() => setFiltersOpen(false)} />
                    <div className="absolute inset-x-0 bottom-0 bg-[#0f1118] border-t border-white/10 rounded-t-2xl p-4 max-h-[80vh] overflow-y-auto">
                        <div className="h-1 w-12 bg-white/20 rounded-full mx-auto mb-4" />
                        <div className="flex items-center justify-between mb-3">
                            <div className="font-bold">فیلترها</div>
                            <button className="text-cherry-400" onClick={() => { setFiltersOpen(false); }}>اعمال</button>
                        </div>

                        {/* Price */}
                        <div className="mb-4">
                            <div className="text-sm text-gray-300 mb-2">محدوده قیمت (تومان)</div>
                            <div className="flex items-center gap-2">
                                <input type="number" inputMode="numeric" className="w-1/2 bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm" placeholder="حداقل" value={minPrice} onChange={e => updateParam('min_price', e.target.value)} />
                                <input type="number" inputMode="numeric" className="w-1/2 bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm" placeholder="حداکثر" value={maxPrice} onChange={e => updateParam('max_price', e.target.value)} />
                            </div>
                        </div>

                        {/* Colors */}
                        {colors.length > 0 && (
                            <div className="mb-4">
                                <div className="text-sm text-gray-300 mb-2">رنگ</div>
                                <div className="flex gap-2 flex-wrap">
                                    {colors.map(c => (
                                        <button key={c.id} onClick={() => toggleMultiParam('colors', c.id)} className={`px-2.5 py-1.5 rounded-full text-xs border ${selectedColors.includes(String(c.id)) ? 'bg-cherry-600 text-white border-cherry-500' : 'bg-white/5 text-gray-200 border-white/10'}`}>{c.name}</button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Sizes */}
                        {sizes.length > 0 && (
                            <div className="mb-2">
                                <div className="text-sm text-gray-300 mb-2">سایز</div>
                                <div className="flex gap-2 flex-wrap">
                                    {sizes.map(s => (
                                        <button key={s.id} onClick={() => toggleMultiParam('sizes', s.id)} className={`px-2.5 py-1.5 rounded-full text-xs border ${selectedSizes.includes(String(s.id)) ? 'bg-cherry-600 text-white border-cherry-500' : 'bg-white/5 text-gray-200 border-white/10'}`}>{s.name}</button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}

export default ProductsPage;


