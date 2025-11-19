import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { createPortal } from 'react-dom';
import { apiRequest } from '../utils/sanctumAuth';
import ProductModal from './ProductModal';

function SearchDropdown({ onSearch, initialQuery = '' }) {
    const [query, setQuery] = useState(initialQuery);
    const [results, setResults] = useState({ products: [], categories: [] });
    const [loading, setLoading] = useState(false);
    const [showDropdown, setShowDropdown] = useState(false);
    const [timeoutId, setTimeoutId] = useState(null);
    const navigate = useNavigate();
    const inputRef = useRef(null);
    const dropdownRef = useRef(null);
    const [selectedProduct, setSelectedProduct] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    useEffect(() => {
        setQuery(initialQuery);
    }, [initialQuery]);

    // Handle click outside to close dropdown
    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target) && 
                inputRef.current && !inputRef.current.contains(event.target)) {
                setShowDropdown(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const searchAPI = async (searchQuery) => {
        if (!searchQuery.trim()) {
            setResults({ products: [], categories: [] });
            setShowDropdown(false);
            return;
        }

        setLoading(true);
        try {
            const response = await apiRequest(`/api/search/dropdown?q=${encodeURIComponent(searchQuery)}&limit=5`);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            if (data.success) {
                setResults(data.data);
                setShowDropdown(true);
            }
        } catch (error) {
            console.error('Error searching:', error);
            setResults({ products: [], categories: [] });
        } finally {
            setLoading(false);
        }
    };

    const handleInputChange = (e) => {
        const value = e.target.value;
        setQuery(value);
        
        // Clear existing timeout
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        
        // Set new timeout for search
        const newTimeoutId = setTimeout(() => {
            searchAPI(value);
        }, 300);
        
        setTimeoutId(newTimeoutId);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        
        if (query.trim()) {
            // Navigate to products page with search query
            navigate(`/products?q=${encodeURIComponent(query)}`);
            setShowDropdown(false);
        }
    };

    const handleResultClick = async (result) => {
        if (result.type === 'product') {
            // Fetch full product details using slug
            try {
                const response = await apiRequest(`/api/products/${result.slug}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        setSelectedProduct(data.data);
                        setIsModalOpen(true);
                        setShowDropdown(false);
                        setQuery('');
                    }
                }
            } catch (error) {
                console.error('Error fetching product:', error);
                // Fallback to navigation
                navigate(result.url);
                setShowDropdown(false);
            }
        } else {
            navigate(result.url);
            setShowDropdown(false);
        }
    };

    const handleInputFocus = () => {
        if (query.trim() && (results.products.length > 0 || results.categories.length > 0)) {
            setShowDropdown(true);
        }
    };

    const formatPrice = (price) => {
        try {
            return Number(price || 0).toLocaleString('fa-IR');
        } catch {
            return price;
        }
    };

    return (
        <div className="relative w-full" style={{ zIndex: 99999 }}>
            <form onSubmit={handleSubmit}>
                <div className="relative">
                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={handleInputChange}
                        onFocus={handleInputFocus}
                        placeholder="جستجوی محصول، مثل: دستبند نقره"
                        className="w-full bg-white/5 border border-white/10 rounded-lg py-2.5 px-3 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-600 text-white"
                    />
                    <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        {loading ? (
                            <div className="w-4 h-4 border-2 border-gray-400 border-t-transparent rounded-full animate-spin"></div>
                        ) : (
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        )}
                    </div>
                </div>
            </form>

            {/* Dropdown Results */}
            {showDropdown && (results.products.length > 0 || results.categories.length > 0) && 
                createPortal(
                    <div 
                        ref={dropdownRef}
                        className="fixed bg-[#0d0d14]/95 border border-white/10 rounded-xl shadow-2xl backdrop-blur-sm max-h-80 overflow-y-auto"
                        style={{ 
                            zIndex: 99999,
                            top: inputRef.current ? inputRef.current.getBoundingClientRect().bottom + window.scrollY + 4 : 0,
                            left: inputRef.current ? inputRef.current.getBoundingClientRect().left + window.scrollX : 0,
                            width: inputRef.current ? inputRef.current.getBoundingClientRect().width : 'auto',
                            minWidth: '300px'
                        }}
                    >
                        {/* Categories */}
                        {results.categories.length > 0 && (
                            <div className="p-2">
                                <div className="text-xs text-gray-400 px-2 py-1 mb-1">دسته‌بندی‌ها</div>
                                {results.categories.map((category) => (
                                    <button
                                        key={`category-${category.id}`}
                                        onClick={() => handleResultClick(category)}
                                        className="w-full text-right px-3 py-2 rounded-lg hover:bg-white/5 text-sm flex items-center gap-3 group"
                                    >
                                        <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-cherry-600/25 to-violet-600/15 flex items-center justify-center text-white text-xs ring-1 ring-white/10">
                                            {category.name.charAt(0)}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="text-white font-medium truncate">{category.name}</div>
                                            {category.description && (
                                                <div className="text-gray-400 text-xs truncate">{category.description}</div>
                                            )}
                                        </div>
                                        <svg className="w-4 h-4 text-gray-400 group-hover:text-cherry-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                ))}
                            </div>
                        )}

                        {/* Products */}
                        {results.products.length > 0 && (
                            <div className="p-2">
                                {results.categories.length > 0 && <div className="border-t border-white/10 my-2"></div>}
                                <div className="text-xs text-gray-400 px-2 py-1 mb-1">محصولات</div>
                                {results.products.map((product) => (
                                    <button
                                        key={`product-${product.id}`}
                                        onClick={() => handleResultClick(product)}
                                        className="w-full text-right px-3 py-2 rounded-lg hover:bg-white/5 text-sm flex items-center gap-3 group"
                                    >
                                        <div className="w-10 h-10 rounded-lg overflow-hidden bg-white/5 ring-1 ring-white/10">
                                            <img 
                                                src={product.image} 
                                                alt={product.title}
                                                className="w-full h-full object-cover"
                                                onError={(e) => {
                                                    const img = e.currentTarget;
                                                    if (img.src.includes('/images/placeholder.jpg') || img.dataset.placeholderTried === 'true') {
                                                        img.style.display = 'none';
                                                        return;
                                                    }
                                                    img.dataset.placeholderTried = 'true';
                                                    img.src = '/images/placeholder.jpg';
                                                }}
                                            />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="text-white font-medium truncate">{product.title}</div>
                                            <div className="text-cherry-400 text-xs">{formatPrice(product.price)} تومان</div>
                                        </div>
                                        <svg className="w-4 h-4 text-gray-400 group-hover:text-cherry-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                ))}
                            </div>
                        )}

                        {/* View All Results */}
                        {query.trim() && (
                            <div className="border-t border-white/10 p-2">
                                <button
                                    onClick={() => {
                                        navigate(`/products?q=${encodeURIComponent(query)}`);
                                        setShowDropdown(false);
                                    }}
                                    className="w-full text-center px-3 py-2 rounded-lg hover:bg-white/5 text-sm text-cherry-400 hover:text-cherry-300"
                                >
                                    مشاهده همه نتایج برای "{query}"
                                </button>
                            </div>
                        )}
                    </div>,
                    document.body
                )
            }
            
            {/* Product Modal */}
            {selectedProduct && (
                <ProductModal 
                    product={selectedProduct} 
                    isOpen={isModalOpen} 
                    onClose={() => {
                        setIsModalOpen(false);
                        setSelectedProduct(null);
                    }} 
                />
            )}
        </div>
    );
}

export default SearchDropdown;
