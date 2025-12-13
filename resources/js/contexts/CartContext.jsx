import React, { createContext, useContext, useState, useEffect } from 'react';
import { apiRequest } from '../utils/sanctumAuth';

const CartContext = createContext();

export function CartProvider({ children }) {
    const [cartData, setCartData] = useState({
        items: [],
        total: 0,
        count: 0,
        original_total: 0,
        total_discount: 0
    });
    const [loading, setLoading] = useState(false);

    const fetchCart = async () => {
        if (loading) return; // Prevent multiple simultaneous requests
        
        setLoading(true);
        try {
            const response = await apiRequest('/api/cart/json');
            if (response.ok) {
                const data = await response.json();
                setCartData(data);
            }
        } catch (error) {
            console.error('Failed to fetch cart:', error);
        } finally {
            setLoading(false);
        }
    };

    const getProductQuantity = (productId) => {
        return cartData.items
            .filter(item => String(item.product?.id) === String(productId))
            .reduce((sum, item) => sum + (item.quantity || 0), 0);
    };

    const updateCart = () => {
        fetchCart();
    };

    useEffect(() => {
        // Initial fetch
        fetchCart();
        
        // Listen for cart updates
        const handleCartUpdate = () => {
            fetchCart();
        };
        
        window.addEventListener('cart:update', handleCartUpdate);
        return () => window.removeEventListener('cart:update', handleCartUpdate);
    }, []);

    return (
        <CartContext.Provider value={{
            cartData,
            getProductQuantity,
            updateCart,
            loading
        }}>
            {children}
        </CartContext.Provider>
    );
}

export function useCart() {
    const context = useContext(CartContext);
    if (!context) {
        throw new Error('useCart must be used within a CartProvider');
    }
    return context;
}
