import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { HelmetProvider } from 'react-helmet-async';
import { AuthProvider } from '../contexts/AuthContext';
import { CartProvider } from '../contexts/CartContext';
import ShopPage from './ShopPage';
import ProductsPage from './ProductsPage';
import ProductPage from './ProductPage';
import CartPage from './CartPage';
import CheckoutPage from './CheckoutPage';
import AdminLayout from './admin/AdminLayout';
import AdminDashboard from './admin/AdminDashboard';
import AdminProductManagement from './admin/AdminProductManagement';
import AdminProductForm from './admin/AdminProductForm';
import AdminOrderManagement from './admin/AdminOrderManagement';
import AdminOrderDetail from './admin/AdminOrderDetail';
import AdminDeliveryManagement from './admin/AdminDeliveryManagement';
import AdminCampaignManagement from './admin/AdminCampaignManagement';
import AdminCampaignForm from './admin/AdminCampaignForm';
import AdminDiscountManagement from './admin/AdminDiscountManagement';
import AdminDiscountForm from './admin/AdminDiscountForm';
import AdminHeroSlideManagement from './admin/AdminHeroSlideManagement';
import AdminHeroSlideForm from './admin/AdminHeroSlideForm';
import AdminPaymentGateways from './admin/AdminPaymentGateways';
import AdminCategoryManagement from './admin/AdminCategoryManagement';
import AdminCategoryForm from './admin/AdminCategoryForm';
import AccountLayout from './account/AccountLayout';
import AccountProfile from './account/AccountProfile';
import AccountOrders from './account/AccountOrders';
import AccountAddresses from './account/AccountAddresses';
import LoginPage from './auth/LoginPage';
import RegisterPage from './auth/RegisterPage';
import Layout from './Layout';
import NotFound from './NotFound';
import CategoryPage from './CategoryPage';
import CategoriesPage from './CategoriesPage';
import ThanksPage from './ThanksPage';
import PaymentErrorPage from './PaymentErrorPage';
import AboutPage from './AboutPage';
import ContactPage from './ContactPage';
import ShippingPage from './ShippingPage';
import ReturnsPage from './ReturnsPage';

function App() {
    return (
        <HelmetProvider>
            <AuthProvider>
                <CartProvider>
                    <Router>
                    <Layout>
                        <Routes>
                        {/* Shop Routes */}
                        <Route path="/" element={<ShopPage />} />
                        <Route path="/products" element={<ProductsPage />} />
                        <Route path="/product/:slug" element={<ProductPage />} />
                        <Route path="/category/:id" element={<CategoryPage />} />
                        <Route path="/categories" element={<CategoriesPage />} />
                        <Route path="/404" element={<NotFound />} />
                        <Route path="/cart" element={<CartPage />} />
                        <Route path="/checkout" element={<CheckoutPage />} />
                        <Route path="/thanks/:invoiceId" element={<ThanksPage />} />
                        <Route path="/payment/error" element={<PaymentErrorPage />} />
                        
                        {/* Static Pages */}
                        <Route path="/about" element={<AboutPage />} />
                        <Route path="/contact" element={<ContactPage />} />
                        <Route path="/shipping" element={<ShippingPage />} />
                        <Route path="/returns" element={<ReturnsPage />} />
                        
                        {/* Auth Routes */}
                        <Route path="/login" element={<LoginPage />} />
                        <Route path="/register" element={<RegisterPage />} />
                        
                        {/* Account Routes */}
                        <Route path="/account" element={<AccountLayout />}>
                            <Route index element={<AccountProfile />} />
                            <Route path="profile" element={<AccountProfile />} />
                            <Route path="orders" element={<AccountOrders />} />
                            <Route path="addresses" element={<AccountAddresses />} />
                        </Route>
                        
                        {/* Admin Routes */}
                        <Route path="/admin" element={<AdminLayout />}>
                            <Route index element={<AdminDashboard />} />
                            <Route path="products" element={<AdminProductManagement />} />
                            <Route path="products/create" element={<AdminProductForm />} />
                            <Route path="products/:id/edit" element={<AdminProductForm />} />
                            <Route path="orders" element={<AdminOrderManagement />} />
                            <Route path="orders/:id" element={<AdminOrderDetail />} />
                            <Route path="delivery" element={<AdminDeliveryManagement />} />
                            <Route path="campaigns" element={<AdminCampaignManagement />} />
                            <Route path="campaigns/create" element={<AdminCampaignForm />} />
                            <Route path="campaigns/:id/edit" element={<AdminCampaignForm />} />
                            <Route path="discounts" element={<AdminDiscountManagement />} />
                            <Route path="discounts/create" element={<AdminDiscountForm />} />
                            <Route path="discounts/:id/edit" element={<AdminDiscountForm />} />
                            <Route path="hero-slides" element={<AdminHeroSlideManagement />} />
                            <Route path="hero-slides/create" element={<AdminHeroSlideForm />} />
                            <Route path="hero-slides/:id/edit" element={<AdminHeroSlideForm />} />
                            <Route path="payment-gateways" element={<AdminPaymentGateways />} />
                            <Route path="categories" element={<AdminCategoryManagement />} />
                            <Route path="categories/create" element={<AdminCategoryForm />} />
                            <Route path="categories/:id/edit" element={<AdminCategoryForm />} />
                        </Route>
                        </Routes>
                    </Layout>
                    </Router>
                </CartProvider>
            </AuthProvider>
        </HelmetProvider>
    );
}

export default App;
