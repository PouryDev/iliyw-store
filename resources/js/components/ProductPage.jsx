import React from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';
import { useSeo } from '../hooks/useSeo';
import { calculateCampaignPrice } from '../utils/pricing';

function ProductPage() {
    const { slug } = useParams();
    const navigate = useNavigate();

    const [product, setProduct] = React.useState(null);
    const [loading, setLoading] = React.useState(true);
    const [error, setError] = React.useState(null);
    const [mainImage, setMainImage] = React.useState(null);
    const [selectedColorId, setSelectedColorId] = React.useState(null);
    const [selectedSizeId, setSelectedSizeId] = React.useState(null);
    const [quantity, setQuantity] = React.useState(1);
    const [adding, setAdding] = React.useState(false);
    const [addStatus, setAddStatus] = React.useState(null);
    const [displayPrice, setDisplayPrice] = React.useState(null);
    const [linkCopied, setLinkCopied] = React.useState(false);

    // SEO
    useSeo({
        title: product ? `${product.title} - ایلی استور` : 'محصول - ایلی استور',
        description: product ? `${product.title} - ${product.description?.substring(0, 150)}...` : 'محصول با کیفیت از فروشگاه ایلی استور',
        keywords: product ? `${product.title}, اکسسوری, تابلو, ایلی استور, خرید آنلاین` : 'اکسسوری, تابلو, خرید آنلاین',
        image: product?.images?.[0]?.path ? resolveImageUrl(product.images[0].path) : '/images/logo.png',
        canonical: window.location.origin + `/products/${slug}`
    });

    function resolveImageUrl(path) {
        if (!path) return null;
        // If already an absolute URL, return as-is
        if (/^https?:\/\//i.test(path)) return path;
        // Normalize leading slashes
        if (path.startsWith('/')) path = path.slice(1);
        return `/storage/${path}`;
    }

    React.useEffect(() => {
        setLoading(true);
        setError(null);
        apiRequest(`/api/products/${slug}`)
            .then(async (res) => {
                if (res.status === 404) {
                    navigate('/404', { replace: true });
                    return Promise.reject(new Error('not-found'));
                }
                if (!res.ok) throw new Error('failed');
                const data = await res.json();
                if (!data?.success) throw new Error('failed');
                return data.data;
            })
            .then((p) => {
                setProduct(p);
                const firstImage = p?.images?.[0]?.path ? resolveImageUrl(p.images[0].path) : null;
                setMainImage(firstImage);
                if (p?.has_colors) {
                    const firstColor = uniqueColors(p)?.[0];
                    setSelectedColorId(firstColor ? firstColor.id : null);
                }
                if (p?.has_sizes) {
                    const firstSize = uniqueSizes(p)?.[0];
                    setSelectedSizeId(firstSize ? firstSize.id : null);
                }
                setDisplayPrice(calculateBasePrice(p, null, null));
            })
            .catch(() => setError('خطا در بارگذاری محصول'))
            .finally(() => setLoading(false));
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [slug]);

    function uniqueColors(p) {
        const variants = p?.active_variants || p?.activeVariants || [];
        const colors = variants.map((v) => v.color).filter(Boolean);
        const map = new Map();
        colors.forEach((c) => {
            if (!map.has(c.id)) map.set(c.id, c);
        });
        return Array.from(map.values());
    }

    function uniqueSizes(p) {
        const variants = p?.active_variants || p?.activeVariants || [];
        const sizes = variants.map((v) => v.size).filter(Boolean);
        const map = new Map();
        sizes.forEach((s) => {
            if (!map.has(s.id)) map.set(s.id, s);
        });
        return Array.from(map.values());
    }

    function filteredSizes() {
        if (!product?.has_sizes) return [];
        if (!selectedColorId) return uniqueSizes(product);
        const variants = product?.active_variants || product?.activeVariants || [];
        const sizes = variants
            .filter((v) => v.color && v.color.id === Number(selectedColorId) && v.size)
            .map((v) => v.size);
        const map = new Map();
        sizes.forEach((s) => {
            if (!map.has(s.id)) map.set(s.id, s);
        });
        return Array.from(map.values());
    }

    

    function filteredColors() {
        if (!product?.has_colors) return [];
        if (!selectedSizeId) return uniqueColors(product);
        const variants = product?.active_variants || product?.activeVariants || [];
        const colors = variants
            .filter((v) => v.size && v.size.id === Number(selectedSizeId) && v.color)
            .map((v) => v.color);
        const map = new Map();
        colors.forEach((c) => {
            if (!map.has(c.id)) map.set(c.id, c);
        });
        return Array.from(map.values());
    }

    function formatPrice(value) {
        try {
            return Number(value || 0).toLocaleString('fa-IR');
        } catch {
            return value;
        }
    }

    function findVariant(p, colorId, sizeId) {
        const variants = p?.active_variants || p?.activeVariants || [];
        return variants.find((v) => (
            (colorId ? v.color_id === Number(colorId) : !v.color_id) &&
            (sizeId ? v.size_id === Number(sizeId) : !v.size_id)
        ));
    }

    function calculateBasePrice(p, colorId, sizeId) {
        const variant = findVariant(p, colorId, sizeId);
        return variant?.price ?? p?.price ?? 0;
    }

    function getActiveCampaign(p) {
        return Array.isArray(p?.campaigns) && p.campaigns.length > 0 ? p.campaigns[0] : null;
    }

    const handleCopyLink = async () => {
        const productUrl = `${window.location.origin}/product/${slug}`;
        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(productUrl);
                setLinkCopied(true);
                setTimeout(() => setLinkCopied(false), 2000);
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'لینک محصول کپی شد' } 
                }));
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = productUrl;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                setLinkCopied(true);
                setTimeout(() => setLinkCopied(false), 2000);
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'لینک محصول کپی شد' } 
                }));
            }
        } catch (err) {
            console.error('Failed to copy:', err);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در کپی لینک' } 
            }));
        }
    };

    // Update price when selection changes
    React.useEffect(() => {
        if (!product) return;
        const base = calculateBasePrice(product, selectedColorId, selectedSizeId);
        setDisplayPrice(base);
    }, [product, selectedColorId, selectedSizeId]);

    async function handleAddToCart() {
        if (!product) return;
        setAdding(true);
        setAddStatus(null);
        try {
            const res = await apiRequest(`/api/cart/add/${product.slug}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    quantity: Number(quantity) || 1,
                    color_id: selectedColorId || null,
                    size_id: selectedSizeId || null,
                }),
            });
            if (!res.ok) throw new Error('failed');
            const payload = await res.json();
            if (!payload?.ok) throw new Error('failed');
            setAddStatus('محصول به سبد اضافه شد');
            try {
                localStorage.setItem('cart', JSON.stringify(payload.items || []));
            } catch {}
            window.dispatchEvent(new Event('cart:update'));
            window.dispatchEvent(new CustomEvent('toast:show', { detail: { type: 'success', message: 'به سبد اضافه شد' } }));
        } catch (e) {
            setAddStatus('خطا در افزودن به سبد');
        } finally {
            setAdding(false);
        }
    }

    if (loading) {
        return (
            <div className="min-h-screen py-12">
                <div className="max-w-7xl mx-auto px-4">
                    <div className="text-gray-300">در حال بارگذاری...</div>
                </div>
            </div>
        );
    }

    if (error || !product) {
        return (
            <div className="min-h-screen py-12">
                <div className="max-w-7xl mx-auto px-4">
                    <div className="text-red-400">{error || 'محصول یافت نشد'}</div>
                    <button onClick={() => navigate(-1)} className="mt-4 text-cherry-400 hover:text-cherry-300">بازگشت</button>
                </div>
            </div>
        );
    }

    const colors = product.has_colors ? filteredColors() : [];
    const sizes = product.has_sizes ? filteredSizes() : [];

    return (
        <div className="min-h-screen pb-28 md:pb-8 pt-6 md:pt-8">
            <div className="max-w-7xl mx-auto px-4">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <div className="bg-black/30 rounded-lg p-4 border border-white/10">
                            {mainImage ? (
                                <img src={mainImage} alt={product.title} className="w-full h-auto rounded" onError={(e) => {
                                    const img = e.currentTarget;
                                    if (img.src.includes('/images/placeholder.jpg') || img.dataset.placeholderTried === 'true') {
                                        img.style.display = 'none';
                                        return;
                                    }
                                    img.dataset.placeholderTried = 'true';
                                    img.src = '/images/placeholder.jpg';
                                }} />
                            ) : (
                                <div className="w-full aspect-[4/3] bg-gray-800 rounded" />
                            )}
                        </div>
                        {product?.images?.length > 1 && (
                            <div className="mt-4">
                                <div className="flex md:hidden gap-3 overflow-x-auto overflow-y-hidden pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [touch-action:pan-x] touch-pan-x" style={{ WebkitOverflowScrolling: 'touch' }}>
                                    {product.images.map((img, idx) => (
                                        <button
                                            key={idx}
                                            onClick={() => setMainImage(resolveImageUrl(img.path))}
                                            className={`flex-shrink-0 bg-black/30 rounded border ${mainImage === resolveImageUrl(img.path) ? 'border-cherry-500' : 'border-white/10'} p-1`}
                                        >
                                            <img src={resolveImageUrl(img.path)} alt={product.title} className="w-16 h-16 object-cover rounded" onError={(e) => {
                                                const img = e.currentTarget;
                                                if (img.src.includes('/images/placeholder.jpg') || img.dataset.placeholderTried === 'true') {
                                                    img.style.display = 'none';
                                                    return;
                                                }
                                                img.dataset.placeholderTried = 'true';
                                                img.src = '/images/placeholder.jpg';
                                            }} />
                                        </button>
                                    ))}
                                </div>
                                <div className="hidden md:grid grid-cols-5 gap-3">
                                    {product.images.map((img, idx) => (
                                        <button
                                            key={idx}
                                            onClick={() => setMainImage(resolveImageUrl(img.path))}
                                            className={`bg-black/30 rounded border ${mainImage === resolveImageUrl(img.path) ? 'border-cherry-500' : 'border-white/10'} p-1`}
                                        >
                                            <img src={resolveImageUrl(img.path)} alt={product.title} className="w-full h-16 object-cover rounded" onError={(e) => {
                                                const img = e.currentTarget;
                                                if (img.src.includes('/images/placeholder.jpg') || img.dataset.placeholderTried === 'true') {
                                                    img.style.display = 'none';
                                                    return;
                                                }
                                                img.dataset.placeholderTried = 'true';
                                                img.src = '/images/placeholder.jpg';
                                            }} />
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="text-white">
                        <div className="flex items-start justify-between mb-4">
                            <h1 className="text-2xl md:text-3xl font-bold">{product.title}</h1>
                            {/* Copy Link Button */}
                            <button
                                onClick={handleCopyLink}
                                className="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors flex-shrink-0"
                                title="کپی لینک محصول"
                            >
                                {linkCopied ? (
                                    <svg className="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                ) : (
                                    <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                )}
                            </button>
                        </div>
                        <div className="flex flex-wrap items-center gap-2 md:gap-3 mb-6">
                            {(() => {
                                const base = displayPrice ?? product.price;
                                const activeCampaign = getActiveCampaign(product);
                                const { finalPrice, originalPrice } = calculateCampaignPrice(base, activeCampaign);
                                return (
                                    <>
                                        {activeCampaign && originalPrice !== finalPrice ? (
                                            <>
                                                <span className="text-gray-400 line-through">{formatPrice(originalPrice)} تومان</span>
                                                <span className="text-xl md:text-2xl font-extrabold">{formatPrice(finalPrice)} تومان</span>
                                            </>
                                        ) : (
                                            <span className="text-xl md:text-2xl font-extrabold">{formatPrice(base)} تومان</span>
                                        )}
                                    </>
                                );
                            })()}
                            {getActiveCampaign(product) && (
                                <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-gradient-to-r from-cherry-600/20 to-violet-600/20 text-cherry-200 border border-cherry-500/30 text-xs md:text-sm backdrop-blur">
                                    <svg className="w-3.5 h-3.5 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    <span className="font-medium truncate max-w-[160px] md:max-w-xs">{getActiveCampaign(product).name}</span>
                                </span>
                            )}
                        </div>

                        {product.description && (
                            <p className="text-gray-300 leading-7 mb-6">{product.description}</p>
                        )}

                        <div className="space-y-4">
                            {product.has_colors && (
                                <div>
                                    <label className="block text-sm text-gray-300 mb-2">رنگ</label>
                                    <div className="flex flex-wrap gap-2">
                                        {colors.length === 0 ? (
                                            <span className="text-xs text-gray-400">ناموجود</span>
                                        ) : (
                                            colors.map((c) => (
                                                <button
                                                    key={c.id}
                                                    onClick={() => setSelectedColorId(c.id)}
                                                    className={`w-9 h-9 rounded-full border-2 transition ${
                                                        selectedColorId === c.id ? 'border-white ring-2 ring-cherry-500' : 'border-white/20'
                                                    }`}
                                                    style={{ backgroundColor: c.hex_code || '#777' }}
                                                    aria-label={c.name}
                                                    title={c.name}
                                                />
                                            ))
                                        )}
                                    </div>
                                </div>
                            )}

                            {product.has_sizes && (
                                <div>
                                    <label className="block text-sm text-gray-300 mb-2">سایز</label>
                                    <div className="flex flex-wrap gap-2">
                                        {sizes.length === 0 ? (
                                            <span className="text-xs text-gray-400">ناموجود</span>
                                        ) : (
                                            sizes.map((s) => (
                                                <button
                                                    key={s.id}
                                                    onClick={() => setSelectedSizeId(s.id)}
                                                    className={`px-3 py-1.5 rounded border text-sm transition ${
                                                        selectedSizeId === s.id ? 'border-white bg-white/10' : 'border-white/20 bg-white/5'
                                                    }`}
                                                    title={s.name}
                                                >
                                                    {s.name}
                                                </button>
                                            ))
                                        )}
                                    </div>
                                </div>
                            )}

                            <div>
                                <label className="block text-sm text-gray-300 mb-2">تعداد</label>
                                <input
                                    type="number"
                                    min={1}
                                    value={quantity}
                                    onChange={(e) => setQuantity(Math.max(1, Number(e.target.value)))}
                                    className="bg-black/40 border border-white/10 rounded px-3 py-2 w-28 text-white"
                                />
                            </div>

                            <div className="pt-2">
                                <button
                                    onClick={handleAddToCart}
                                    disabled={adding}
                                    className="bg-cherry-600 hover:bg-cherry-500 disabled:opacity-60 disabled:cursor-not-allowed transition-colors text-white px-6 py-3 rounded-lg"
                                >
                                    {adding ? 'در حال افزودن...' : 'افزودن به سبد خرید'}
                                </button>
                                {addStatus && (
                                    <span className="ml-4 text-sm text-gray-300">{addStatus}</span>
                                )}
                            </div>
                        </div>
                        {/* Benefit Cards */}
                        <div className="mt-8 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                            <div className="bg-white/5 border border-white/10 rounded-lg p-3 flex items-start gap-2">
                                <span className="text-cherry-400 mt-0.5">🚚</span>
                                <div>
                                    <div className="text-white font-semibold">ارسال سریع</div>
                                    <div className="text-gray-400 text-xs">تحویل 5 تا 9 روز کاری</div>
                                </div>
                            </div>
                            {/* <div className="bg-white/5 border border-white/10 rounded-lg p-3 flex items-start gap-2">
                                <span className="text-cherry-400 mt-0.5">🔄</span>
                                <div>
                                    <div className="text-white font-semibold">مرجوع آسان</div>
                                    <div className="text-gray-400 text-xs">تا 7 روز پس از دریافت</div>
                                </div>
                            </div> */}
                            <div className="bg-white/5 border border-white/10 rounded-lg p-3 flex items-start gap-2">
                                <span className="text-cherry-400 mt-0.5">💳</span>
                                <div>
                                    <div className="text-white font-semibold">پرداخت امن</div>
                                    <div className="text-gray-400 text-xs">از طریق درگاه شتاب</div>
                                </div>
                            </div>
                            {/* <div className="bg-white/5 border border-white/10 rounded-lg p-3 flex items-start gap-2">
                                <span className="text-cherry-400 mt-0.5">🎁</span>
                                <div>
                                    <div className="text-white font-semibold">بسته‌بندی شیک</div>
                                    <div className="text-gray-400 text-xs">مناسب هدیه دادن</div>
                                </div>
                            </div> */}
                        </div>
                    </div>
                </div>
            </div>
            {/* Mobile sticky CTA */}
            {product && (
                <div className="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-black/70 backdrop-blur border-t border-white/10">
                    <div className="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
                        <div>
                            <div className="text-xs text-gray-300">قیمت</div>
                            {(() => {
                                const base = displayPrice ?? product.price;
                                const activeCampaign = getActiveCampaign(product);
                                const { finalPrice } = calculateCampaignPrice(base, activeCampaign);
                                return <div className="text-white font-extrabold">{formatPrice(finalPrice)} تومان</div>;
                            })()}
                        </div>
                        <button onClick={handleAddToCart} disabled={adding} className="flex-1 text-center bg-cherry-600 hover:bg-cherry-500 text-white rounded-lg py-2">
                            {adding ? 'در حال افزودن...' : 'افزودن به سبد'}
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}

// Mobile sticky add-to-cart bar
// Placed outside main grid to overlay at the bottom on small screens
// Using existing helpers to compute final campaign price

export default ProductPage;
