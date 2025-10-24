import React, { useState, useEffect } from 'react';
import { apiRequest } from '../utils/sanctumAuth';

function VariantSelectorModal({ 
    product, 
    isOpen, 
    onClose, 
    onSuccess,
    currentQuantity = 1 
}) {
    const [selectedColorId, setSelectedColorId] = useState(null);
    const [selectedSizeId, setSelectedSizeId] = useState(null);
    const [quantity, setQuantity] = useState(1); // Always start with 1
    const [adding, setAdding] = useState(false);
    const [addStatus, setAddStatus] = useState(null);
    const [fullProduct, setFullProduct] = useState(null);
    const [loading, setLoading] = useState(false);
    const [currentCartQuantity, setCurrentCartQuantity] = useState(0); // Track current cart quantity for selected variant

    useEffect(() => {
        if (isOpen && product) {
            fetchProductDetails();
        }
    }, [isOpen, product]);

    // Update cart quantity when variant selection changes or modal opens
    useEffect(() => {
        const cartQuantity = getCurrentCartQuantityForVariant();
        setCurrentCartQuantity(cartQuantity);
    }, [selectedColorId, selectedSizeId, fullProduct, product, isOpen, currentQuantity]);

    async function fetchProductDetails() {
        if (!product?.slug) return;
        
        setLoading(true);
        try {
            const response = await apiRequest(`/api/products/${product.slug}`);
            
            if (response.ok) {
                const responseData = await response.json();
                const data = responseData.data || responseData;
                setFullProduct(data);
                
                // Reset selections
                setSelectedColorId(null);
                setSelectedSizeId(null);
                setQuantity(1); // Always start with 1
                setAddStatus(null);
                // Don't reset currentCartQuantity here - let useEffect handle it
            }
        } catch (error) {
            console.error('Error fetching product details:', error);
            setFullProduct(product);
        } finally {
            setLoading(false);
        }
    }

    function getSelectedVariant() {
        const currentProduct = fullProduct || product;
        if (!currentProduct?.active_variants) return null;
        
        // For products with variants, require exact match
        if (currentProduct.has_variants || currentProduct.has_colors || currentProduct.has_sizes) {
            const variant = currentProduct.active_variants.find(v => {
                const colorMatch = !currentProduct.has_colors || (selectedColorId && String(v.color_id) === String(selectedColorId));
                const sizeMatch = !currentProduct.has_sizes || (selectedSizeId && String(v.size_id) === String(selectedSizeId));
                
                return colorMatch && sizeMatch;
            });
            
            return variant;
        }
        
        return null;
    }

    function getStockCount() {
        const currentProduct = fullProduct || product;
        
        // For products with variants, require variant selection
        if (currentProduct.has_variants || currentProduct.has_colors || currentProduct.has_sizes) {
            const variant = getSelectedVariant();
            if (!variant) {
                return 0; // No stock if no variant selected
            }
            return variant.stock;
        }
        
        // For products without variants, use main product stock
        return currentProduct.stock;
    }

    function getCurrentCartQuantityForVariant() {
        // For now, use the currentQuantity prop which represents the total for this product
        // This is a simplified approach - in a real implementation you'd need to filter by variant
        return currentQuantity || 0;
    }

    function getAvailableColors() {
        const currentProduct = fullProduct || product;
        
        // Use available_colors attribute if available
        if (currentProduct?.available_colors) {
            return currentProduct.available_colors;
        }
        
        // Fallback to extracting from active_variants
        if (!currentProduct?.active_variants) return [];
        const colors = new Map();
        
        currentProduct.active_variants.forEach(variant => {
            if (variant.color && !colors.has(variant.color.id)) {
                colors.set(variant.color.id, variant.color);
            }
        });
        
        return Array.from(colors.values());
    }

    function getAvailableSizes() {
        const currentProduct = fullProduct || product;
        
        // Use available_sizes attribute if available
        if (currentProduct?.available_sizes) {
            return currentProduct.available_sizes;
        }
        
        // Fallback to extracting from active_variants
        if (!currentProduct?.active_variants) return [];
        const sizes = new Map();
        
        currentProduct.active_variants.forEach(variant => {
            if (variant.size && !sizes.has(variant.size.id)) {
                sizes.set(variant.size.id, variant.size);
            }
        });
        
        return Array.from(sizes.values());
    }

    async function addToCart() {
        if (adding) return;
        
        setAdding(true);
        setAddStatus(null);
        
        try {
            const currentProduct = fullProduct || product;
            
            // Check if product has variants and require selection
            if (currentProduct.has_variants || currentProduct.has_colors || currentProduct.has_sizes) {
                // For products with variants, require color and size selection
                if (currentProduct.has_colors && !selectedColorId) {
                    setAddStatus('error');
                    setTimeout(() => {
                        setAddStatus(null);
                    }, 3000);
                    return;
                }
                if (currentProduct.has_sizes && !selectedSizeId) {
                    setAddStatus('error');
                    setTimeout(() => {
                        setAddStatus(null);
                    }, 3000);
                    return;
                }
            }
            
            // Always use product slug for the URL (API route)
            const url = `/api/cart/add/${currentProduct.slug}`;
            
            // Prepare request body with variant info
            const requestBody = {
                quantity,
                color_id: selectedColorId || null,
                size_id: selectedSizeId || null
            };
            
            const response = await apiRequest(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(requestBody)
            });
            
            const data = await response.json();
            
            if (data.success || data.ok) {
                setAddStatus('success');
                // Update cart count
                window.dispatchEvent(new CustomEvent('cart:update'));
                // Show toast
                try {
                    window.showToast('به سبد افزوده شد');
                } catch {}
                
                setTimeout(() => {
                    onSuccess && onSuccess();
                    onClose();
                }, 1000);
            } else {
                setAddStatus('error');
                setTimeout(() => {
                    setAddStatus(null);
                }, 3000);
            }
        } catch (error) {
            console.error('Add to cart error:', error);
            setAddStatus('error');
            setTimeout(() => {
                setAddStatus(null);
            }, 3000);
        } finally {
            setAdding(false);
        }
    }

    function handleBackdropClick(e) {
        if (e.target === e.currentTarget) {
            onClose();
        }
    }

    function handleKeyDown(e) {
        if (e.key === 'Escape') {
            onClose();
        }
    }

    useEffect(() => {
        if (isOpen) {
            document.addEventListener('keydown', handleKeyDown);
            document.body.style.overflow = 'hidden';
            
            return () => {
                document.removeEventListener('keydown', handleKeyDown);
                document.body.style.overflow = 'unset';
            };
        } else {
            document.body.style.overflow = 'unset';
        }
    }, [isOpen]);

    if (!isOpen || !product) return null;

    const currentProduct = fullProduct || product;
    const stock = getStockCount();
    const availableStock = Math.max(0, stock - currentCartQuantity);
    const maxQuantity = Math.min(availableStock, 10);
    const isOutOfStock = availableStock <= 0;

    return (
        <div 
            className="fixed inset-0 z-[99999] flex items-end md:items-center justify-center"
            onClick={handleBackdropClick}
        >
            {/* Backdrop */}
            <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" />
            
            {/* Modal */}
            <div className="relative w-full max-w-md h-[85vh] md:h-[80vh] bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 rounded-t-3xl md:rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                {/* Header */}
                <div className="sticky top-0 bg-gray-900/80 backdrop-blur-sm border-b border-white/10 px-4 py-3 flex items-center justify-between z-10">
                    <button
                        onClick={onClose}
                        className="p-2 rounded-full bg-white/10 hover:bg-white/20 transition-colors"
                    >
                        <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <h2 className="text-lg font-semibold text-white truncate px-2">انتخاب گزینه‌ها</h2>
                    <div className="w-9" /> {/* Spacer */}
                </div>

                {/* Content */}
                <div className="overflow-y-auto flex-1">
                    {loading ? (
                        <div className="flex items-center justify-center h-64">
                            <div className="flex flex-col items-center gap-4">
                                <div className="w-8 h-8 border-2 border-amber-500 border-t-transparent rounded-full animate-spin" />
                                <span className="text-gray-400">در حال بارگذاری...</span>
                            </div>
                        </div>
                    ) : (
                        <>
                            {/* Product Info */}
                            <div className="p-4 border-b border-white/10">
                                <div className="flex items-center gap-3">
                                    <div className="w-16 h-16 rounded-xl bg-gradient-to-br from-amber-500/20 to-indigo-500/20 flex items-center justify-center text-2xl flex-shrink-0">
                                        🛍️
                                    </div>
                                    <div className="flex-1">
                                        <h3 className="font-semibold text-white text-sm leading-tight">{currentProduct.title}</h3>
                                        <div className="text-xs text-amber-400 mt-1">{Number(currentProduct.price || 0).toLocaleString('fa-IR')} تومان</div>
                                    </div>
                                </div>
                            </div>

                            {/* Variant Selection */}
                            <div className="p-4 space-y-6">
                                {/* Colors */}
                                {currentProduct.has_colors && (
                                    <div>
                                        <h3 className="text-sm font-semibold text-gray-300 mb-3">
                                            انتخاب رنگ
                                        </h3>
                                        <div className="grid grid-cols-2 gap-2">
                                            {getAvailableColors().map(color => (
                                                <button
                                                    key={color.id}
                                                    onClick={() => setSelectedColorId(color.id)}
                                                    className={`flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all border ${
                                                        selectedColorId === color.id
                                                            ? 'bg-amber-600 text-white border-amber-500 shadow-lg'
                                                            : 'bg-white/10 text-gray-300 hover:bg-white/20 border-white/20'
                                                    }`}
                                                >
                                                    {color.hex_code && (
                                                        <div 
                                                            className="w-6 h-6 rounded-full border-2 border-white/30 shadow-sm" 
                                                            style={{ backgroundColor: color.hex_code }}
                                                        />
                                                    )}
                                                    <span className="flex-1 text-right">{color.name}</span>
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Sizes */}
                                {currentProduct.has_sizes && (
                                    <div>
                                        <h3 className="text-sm font-semibold text-gray-300 mb-3">
                                            انتخاب سایز
                                        </h3>
                                        <div className="grid grid-cols-3 gap-2">
                                            {getAvailableSizes().map(size => (
                                                <button
                                                    key={size.id}
                                                    onClick={() => setSelectedSizeId(size.id)}
                                                    className={`px-4 py-3 rounded-xl text-sm font-medium transition-all border ${
                                                        selectedSizeId === size.id
                                                            ? 'bg-amber-600 text-white border-amber-500 shadow-lg'
                                                            : 'bg-white/10 text-gray-300 hover:bg-white/20 border-white/20'
                                                    }`}
                                                >
                                                    {size.name}
                                                </button>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {/* Quantity */}
                                <div>
                                    <h3 className="text-sm font-semibold text-gray-300 mb-3">
                                        تعداد
                                    </h3>
                                    <div className="flex items-center justify-center gap-4">
                                        <button
                                            onClick={() => setQuantity(Math.max(1, quantity - 1))}
                                            disabled={quantity <= 1}
                                            className="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white text-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                        >
                                            −
                                        </button>
                                        <div className="w-16 text-center">
                                            <span className="text-2xl font-bold text-white">{quantity}</span>
                                        </div>
                                        <button
                                            onClick={() => setQuantity(Math.min(maxQuantity, quantity + 1))}
                                            disabled={quantity >= maxQuantity}
                                            className="w-12 h-12 rounded-full bg-amber-600 hover:bg-amber-500 text-white text-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                        >
                                            +
                                        </button>
                                    </div>
                                </div>

                                {/* Stock Info */}
                                <div className="text-center">
                                    {isOutOfStock ? (
                                        <span className="text-red-400 text-sm">ناموجود</span>
                                    ) : (
                                        <span className="text-gray-400 text-sm">
                                            {stock} عدد موجود
                                            {currentCartQuantity > 0 && (
                                                <span className="text-xs text-gray-500">
                                                    {' '}({currentCartQuantity} عدد در سبد)
                                                </span>
                                            )}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </>
                    )}
                </div>

                {/* Footer - Add to Cart */}
                <div className="sticky bottom-0 bg-gray-900/80 backdrop-blur-sm border-t border-white/10 p-4">
                    <button
                        onClick={addToCart}
                        disabled={adding || isOutOfStock}
                        className={`w-full py-4 px-4 rounded-xl font-semibold transition-colors ${
                            addStatus === 'success'
                                ? 'bg-green-600 text-white'
                                : addStatus === 'error'
                                ? 'bg-red-600 text-white'
                                : isOutOfStock
                                ? 'bg-gray-600 text-gray-400 cursor-not-allowed'
                                : 'bg-amber-600 hover:bg-amber-700 text-white shadow-lg'
                        }`}
                    >
                        {adding ? (
                            <span className="flex items-center justify-center gap-2">
                                <div className="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin" />
                                در حال افزودن...
                            </span>
                        ) : addStatus === 'success' ? (
                            'افزوده شد!'
                        ) : addStatus === 'error' ? (
                            'لطفاً گزینه‌ها را انتخاب کنید'
                        ) : isOutOfStock ? (
                            'ناموجود'
                        ) : (
                            'افزودن به سبد'
                        )}
                    </button>
                </div>
            </div>
        </div>
    );
}

export default VariantSelectorModal;
