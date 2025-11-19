import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';
import ProductCard from './ProductCard';
import LoadingSpinner from './LoadingSpinner';
import { useSeo } from '../hooks/useSeo';

function CampaignPage() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [campaign, setCampaign] = useState(null);
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [hasMorePages, setHasMorePages] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);

    const fetchCampaignProducts = useCallback(async (page = 1, append = false) => {
        setLoading(true);
        try {
            const params = new URLSearchParams();
            if (page > 1) params.set('page', page);
            
            const res = await apiRequest(`/api/campaigns/${id}/products?${params.toString()}`);
            
            if (!res.ok) {
                if (res.status === 404) {
                    navigate('/404', { replace: true });
                }
                throw new Error('failed');
            }
            
            const data = await res.json();
            
            if (data.success) {
                setCampaign(data.campaign);
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
    }, [id, navigate]);

    useEffect(() => {
        fetchCampaignProducts(1);
    }, [fetchCampaignProducts]);

    const loadMore = () => {
        if (!loading && hasMorePages) {
            fetchCampaignProducts(currentPage + 1, true);
        }
    };

    const formatPrice = (v) => {
        try { return Number(v || 0).toLocaleString('fa-IR'); } catch { return v; }
    };

    useSeo({
        title: campaign ? `${campaign.name} - فروشگاه ایلی استور` : 'کمپین - ایلی استور',
        description: campaign?.description || 'مشاهده محصولات کمپین',
        keywords: campaign ? `${campaign.name}, تخفیف, خرید آنلاین` : '',
        canonical: window.location.origin + window.location.pathname
    });

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white">
            {/* Header */}
            <section className="relative py-6 md:py-10 px-4">
                <div className="max-w-7xl mx-auto">
                    <button onClick={() => navigate(-1)} className="text-cherry-400 hover:text-cherry-300 text-sm mb-3 flex items-center gap-1">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/></svg>
                        بازگشت
                    </button>
                    {campaign ? (
                        <div className="rounded-2xl overflow-hidden glass-card soft-shadow border border-white/10">
                            <div className="relative h-32 md:h-40 bg-gradient-to-br from-cherry-600/40 via-violet-600/30 to-purple-600/20 overflow-hidden">
                                <div className="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAwIDEwIEwgNDAgMTAgTSAxMCAwIEwgMTAgNDAgTSAwIDIwIEwgNDAgMjAgTSAyMCAwIEwgMjAgNDAgTSAwIDMwIEwgNDAgMzAgTSAzMCAwIEwgMzAgNDAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS1vcGFjaXR5PSIwLjAzIiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=')] opacity-30" />
                                <div className="absolute inset-0 flex items-center justify-center text-center px-4 z-10">
                                    <div>
                                        <h1 className="text-xl md:text-3xl font-extrabold text-white mb-2">{campaign.name}</h1>
                                        {campaign.type === 'percentage' && (
                                            <div className="inline-block px-4 py-1.5 rounded-full bg-white/20 backdrop-blur text-white text-sm font-bold">
                                                {campaign.discount_value}% تخفیف
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div className="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-black/40 to-transparent" />
                            </div>
                            {campaign.description && (
                                <div className="p-4 md:p-5 bg-white/5">
                                    <p className="text-gray-300 text-sm">{campaign.description}</p>
                                    <div className="text-xs text-gray-400 mt-2">
                                        تا {new Date(campaign.ends_at).toLocaleDateString('fa-IR')}
                                    </div>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="rounded-2xl glass-card soft-shadow p-5 border border-white/10">
                            <div className="text-gray-300">در حال بارگذاری...</div>
                        </div>
                    )}
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
                            <div className="text-6xl mb-4">🎯</div>
                            <h3 className="text-xl font-semibold text-white mb-2">محصولی یافت نشد</h3>
                            <p className="text-gray-400">در این کمپین محصولی وجود ندارد</p>
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

export default CampaignPage;

