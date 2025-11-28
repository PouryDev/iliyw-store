import React from 'react';
import { useAuth } from '../contexts/AuthContext';
import CheckoutAuthModal from './CheckoutAuthModal';
import AddressDropdown from './AddressDropdown';
import AddressModal from './AddressModal';
import FileUpload from './FileUpload';
import { apiRequest } from '../utils/sanctumAuth';
import { showToast } from '../utils/toast';

const PAYMENT_CARD = {
    number: '6037991553211859',
    holder: 'مرضیه جعفرنژاد قمی',
};

const FORMATTED_PAYMENT_CARD_NUMBER = PAYMENT_CARD.number.replace(/(.{4})/g, '$1 ').trim();
const FORMATTED_PAYMENT_CARD_NUMBER_PERSIAN = FORMATTED_PAYMENT_CARD_NUMBER.replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

function CheckoutPage() {
    const { user: authUser, isAuthenticated, loading: authLoading } = useAuth();
    const [loading, setLoading] = React.useState(true);
    const [cart, setCart] = React.useState({ 
        items: [], 
        total: 0, 
        originalTotal: 0,
        totalDiscount: 0,
        count: 0 
    });
    const [form, setForm] = React.useState({ 
        name: '', 
        phone: '', 
        address: '', 
        discount_code: '', 
        receipt: null,
        delivery_method_id: null,
        payment_gateway_id: null
    });
    const [submitting, setSubmitting] = React.useState(false);
    const [discountInfo, setDiscountInfo] = React.useState(null);
    const [deliveryMethods, setDeliveryMethods] = React.useState([]);
    const [selectedDeliveryMethod, setSelectedDeliveryMethod] = React.useState(null);
    const [copyingCard, setCopyingCard] = React.useState(false);
    const [paymentGateways, setPaymentGateways] = React.useState([]);
    const [selectedGateway, setSelectedGateway] = React.useState(null);

    const formatPrice = (v) => {
        try { return Number(v || 0).toLocaleString('fa-IR'); } catch { return v; }
    };

    const [authOpen, setAuthOpen] = React.useState(false);
    const [addresses, setAddresses] = React.useState([]);
    const [selectedAddress, setSelectedAddress] = React.useState(null);
    const [addressModalOpen, setAddressModalOpen] = React.useState(false);
    const [editingAddress, setEditingAddress] = React.useState(null);
    const [addressLoading, setAddressLoading] = React.useState(false);

    // Update form when user changes
    React.useEffect(() => {
        if (authUser) {
            setForm((prev) => ({
                ...prev,
                name: authUser.name || '',
                phone: authUser.phone || '',
                address: authUser.address || '',
            }));
        }
    }, [authUser]);

    // Open auth modal if user is not authenticated (only after auth loading is complete)
    React.useEffect(() => {
        if (!authLoading) {
            if (!isAuthenticated) {
                setAuthOpen(true);
            } else {
                setAuthOpen(false);
            }
        }
    }, [isAuthenticated, authLoading]);

    const fetchDeliveryMethods = React.useCallback(async () => {
        try {
            const res = await apiRequest('/api/delivery-methods');
            if (!res.ok) {
                if (res.status === 401) {
                    console.warn('Delivery methods require authentication, but user is not authenticated');
                    // Set empty array for unauthenticated users - they'll see delivery methods after login
                    setDeliveryMethods([]);
                    return;
                }
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            const data = await res.json();
            setDeliveryMethods(data.data || []);
        } catch (e) {
            console.error('Failed to fetch delivery methods:', e);
            // Set empty array on error to prevent UI issues
            setDeliveryMethods([]);
        }
    }, []);

    const fetchPaymentGateways = React.useCallback(async () => {
        try {
            const res = await apiRequest('/api/payment/gateways');
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            const data = await res.json();
            setPaymentGateways(data.data || []);
            // Auto-select first gateway if available
            if (data.data && data.data.length > 0) {
                const firstGateway = data.data[0];
                setSelectedGateway(firstGateway);
                setForm(prev => ({ ...prev, payment_gateway_id: firstGateway.id }));
            }
        } catch (e) {
            console.error('Failed to fetch payment gateways:', e);
            setPaymentGateways([]);
        }
    }, []);

    const fetchCart = React.useCallback(async () => {
        setLoading(true);
        try {
            const res = await apiRequest('/api/cart/json');
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            setCart({ 
                items: data.items || [], 
                total: data.total || 0, 
                originalTotal: data.original_total || data.total || 0,
                totalDiscount: data.total_discount || 0,
                count: data.count || 0 
            });
        } catch (e) {
            showToast('خطا در دریافت اطلاعات سبد', 'error');
        } finally {
            setLoading(false);
        }
    }, []);

    React.useEffect(() => {
        fetchCart();
        fetchDeliveryMethods();
        fetchPaymentGateways();
    }, [fetchCart, fetchDeliveryMethods, fetchPaymentGateways]);

    // Fetch addresses and delivery methods when user is authenticated
    React.useEffect(() => {
        if (authUser) {
            fetchAddresses();
            // Refetch delivery methods after authentication (in case they were empty before)
            if (deliveryMethods.length === 0) {
                fetchDeliveryMethods();
            }
        }
    }, [authUser, deliveryMethods.length, fetchDeliveryMethods]);

    const fetchAddresses = async () => {
        if (!authUser) return;
        try {
            const res = await apiRequest('/api/addresses');
            if (res.ok) {
                const data = await res.json();
                setAddresses(data.data || []);
                // Set default address as selected
                const defaultAddr = data.data?.find(addr => addr.is_default);
                if (defaultAddr) {
                    setSelectedAddress(defaultAddr);
                    setForm(prev => ({ ...prev, address: defaultAddr.address }));
                }
            }
        } catch (error) {
            console.error('Failed to fetch addresses:', error);
        }
    };

    const handleAddressSelect = (address) => {
        setSelectedAddress(address);
        setForm(prev => ({ ...prev, address: address.address }));
    };

    const handleAddNewAddress = () => {
        setEditingAddress(null);
        setAddressModalOpen(true);
    };

    const handleSaveAddress = () => {
        // Refresh addresses list after successful save
        fetchAddresses();
        setAddressModalOpen(false);
        setEditingAddress(null);
    };

    const handleChange = (e) => {
        const { name, value, type, files } = e.target;
        if (type === 'file') {
            setForm((prev) => ({ ...prev, [name]: files[0] || null }));
        } else {
            setForm((prev) => ({ ...prev, [name]: value }));
        }
    };

    const handleFileChange = (name, file) => {
        setForm((prev) => ({ ...prev, [name]: file }));
    };

    const handleDeliveryMethodChange = (methodId) => {
        const method = deliveryMethods.find(m => m.id === methodId);
        setSelectedDeliveryMethod(method);
        setForm((prev) => ({ ...prev, delivery_method_id: methodId }));
    };

    const handlePaymentGatewayChange = (gatewayId) => {
        const gateway = paymentGateways.find(g => g.id === gatewayId);
        setSelectedGateway(gateway);
        setForm((prev) => ({ ...prev, payment_gateway_id: gatewayId }));
        // Clear receipt if switching away from card-to-card
        if (gateway && gateway.type !== 'card_to_card') {
            setForm((prev) => ({ ...prev, receipt: null }));
        }
    };

    const handleCopyCardNumber = React.useCallback(async () => {
        if (copyingCard) return;
        setCopyingCard(true);
        const rawNumber = PAYMENT_CARD.number.replace(/\s+/g, '');
        try {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                await navigator.clipboard.writeText(rawNumber);
            } else {
                throw new Error('Clipboard API unavailable');
            }
            showToast('شماره کارت کپی شد', 'success');
        } catch (err) {
            try {
                const textArea = document.createElement('textarea');
                textArea.value = rawNumber;
                textArea.style.position = 'fixed';
                textArea.style.left = '-9999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                if (!successful) throw new Error('Copy command failed');
                showToast('شماره کارت کپی شد', 'success');
            } catch (fallbackErr) {
                console.error('Failed to copy card number:', fallbackErr);
                showToast('امکان کپی خودکار وجود ندارد؛ لطفاً دستی کپی کنید', 'error');
            }
        } finally {
            setTimeout(() => setCopyingCard(false), 400);
        }
    }, [copyingCard]);

    async function applyDiscount() {
        // Placeholder: here you could call a dedicated API to validate discount; for now just show a dummy confirmation
        if (!form.discount_code) return;
        setDiscountInfo({ code: form.discount_code, amount: Math.min(50000, Math.round(cart.total * 0.1)) });
    }

    async function handleSubmit(e) {
        e.preventDefault();
        
        // Gate by auth: if not logged in, open auth modal instead of submit
        if (!authUser) {
            setAuthOpen(true);
            return;
        }
        
        // Basic client-side validation before submission with toast notifications
        if (!form.name.trim()) {
            showToast('نام و نام خانوادگی الزامی است', 'error');
            return;
        }
        if (!form.phone.trim()) {
            showToast('شماره تماس الزامی است', 'error');
            return;
        }
        if (!form.address.trim()) {
            showToast('آدرس الزامی است', 'error');
            return;
        }
        if (!form.delivery_method_id) {
            showToast('انتخاب روش ارسال الزامی است', 'error');
            return;
        }
        if (!form.payment_gateway_id) {
            showToast('انتخاب روش پرداخت الزامی است', 'error');
            return;
        }
        
        // Check if card-to-card gateway requires receipt
        const gateway = paymentGateways.find(g => g.id === form.payment_gateway_id);
        if (gateway && gateway.type === 'card_to_card' && !form.receipt) {
            showToast('آپلود فیش واریزی الزامی است', 'error');
            return;
        }
        
        setSubmitting(true);
        try {
            // Use FormData for file upload
            const formData = new FormData();
            formData.append('customer_name', form.name);
            formData.append('customer_phone', form.phone);
            formData.append('customer_address', form.address);
            formData.append('receipt', form.receipt);
            formData.append('delivery_method_id', form.delivery_method_id);
            if (form.discount_code) {
                formData.append('discount_code', form.discount_code);
            }
            
            // First, create order and invoice
            const checkoutRes = await apiRequest('/api/checkout', {
                method: 'POST',
                body: formData,
            });
            
            if (!checkoutRes.ok) {
                const errorData = await checkoutRes.json();
                
                // Handle validation errors (422 status) with toast notifications
                if (checkoutRes.status === 422 && errorData.errors) {
                    // Show first validation error as toast
                    const firstError = Object.values(errorData.errors)[0];
                    if (firstError && firstError[0]) {
                        showToast(firstError[0], 'error');
                    } else {
                        showToast(errorData.message || 'لطفاً خطاهای زیر را برطرف کنید', 'error');
                    }
                    setSubmitting(false);
                    return;
                }
                
                throw new Error(errorData.message || 'خطا در ثبت سفارش');
            }
            
            const checkoutData = await checkoutRes.json();
            
            if (!checkoutData.success) {
                throw new Error(checkoutData.message || 'خطا در ثبت سفارش');
            }

            const invoiceId = checkoutData.invoice?.id || checkoutData.invoice_id;
            
            // Handle payment based on gateway type
            const gateway = paymentGateways.find(g => g.id === form.payment_gateway_id);
            
            // Initiate payment for all gateways
            const paymentRes = await apiRequest('/api/payment/initiate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    invoice_id: invoiceId,
                    gateway_id: form.payment_gateway_id,
                }),
            });
            
            if (!paymentRes.ok) {
                const errorData = await paymentRes.json();
                throw new Error(errorData.message || 'خطا در شروع پرداخت');
            }
            
            const paymentData = await paymentRes.json();
            
            // Cart will be cleared only after successful payment verification
            // This prevents cart loss if user cancels payment
            
            if (gateway.type === 'card_to_card') {
                // For card-to-card, upload receipt and verify
                if (form.receipt && paymentData.data.transaction_id) {
                    const verifyFormData = new FormData();
                    verifyFormData.append('transaction_id', paymentData.data.transaction_id);
                    verifyFormData.append('receipt', form.receipt);
                    
                    const verifyRes = await apiRequest('/api/payment/verify', {
                        method: 'POST',
                        body: verifyFormData,
                    });
                    
                    if (verifyRes.ok) {
                        showToast('سفارش با موفقیت ثبت شد. پس از تایید پرداخت، سفارش شما پردازش خواهد شد.', 'success');
                        window.location.href = `/thanks/${encodeURIComponent(invoiceId)}`;
                    } else {
                        throw new Error('خطا در آپلود فیش واریزی');
                    }
                } else {
                    throw new Error('خطا در ایجاد تراکنش پرداخت');
                }
            } else if (gateway.type === 'zarinpal' || gateway.type === 'zibal') {
                // Redirect to payment gateway (ZarinPal or Zibal)
                if (paymentData.success && paymentData.data.redirect_url) {
                    window.location.href = paymentData.data.redirect_url;
                    return;
                } else {
                    throw new Error(paymentData.message || 'خطا در شروع پرداخت');
                }
            } else {
                // Default: redirect to thanks page
                showToast('سفارش با موفقیت ثبت شد', 'success');
                window.location.href = `/thanks/${encodeURIComponent(invoiceId)}`;
            }
        } catch (e) {
            showToast(e.message || 'ثبت سفارش با خطا مواجه شد', 'error');
            setSubmitting(false);
            // Cart is not cleared if payment initiation fails, so user can retry
        }
    }

    const finalAmount = React.useMemo(() => {
        let total = cart.total;
        if (selectedDeliveryMethod) {
            total += selectedDeliveryMethod.fee;
        }
        if (discountInfo) {
            total = Math.max(0, total - (discountInfo.amount || 0));
        }
        return total;
    }, [cart.total, selectedDeliveryMethod, discountInfo]);

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
            {/* Mobile Header */}
            <div className="sticky top-0 z-30 bg-black/20 backdrop-blur-md border-b border-white/10 lg:hidden">
                <div className="max-w-md mx-auto px-4 py-4">
                    <h1 className="text-xl font-bold text-white text-center">تسویه حساب</h1>
                </div>
            </div>

            {/* Desktop Header */}
            <div className="hidden lg:block pt-6 md:pt-8">
            <div className="max-w-7xl mx-auto px-4">
                <h1 className="text-2xl md:text-3xl font-extrabold text-white mb-4 md:mb-6">تسویه حساب</h1>
                </div>
            </div>

                {loading ? (
                <div className="flex items-center justify-center py-12">
                    <div className="w-8 h-8 border-2 border-cherry-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
            ) : (
                <div className="max-w-md mx-auto lg:max-w-7xl px-4 py-6 lg:py-8">
                    {/* Mobile Layout */}
                    <div className="lg:hidden space-y-4">
                        {/* Order Summary Card */}
                        <div className="bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl overflow-hidden">
                            <div className="bg-gradient-to-r from-white/10 to-white/0 px-4 py-3">
                                <div className="flex items-center justify-between">
                                    <h2 className="text-white font-bold text-lg">جزئیات سفارش</h2>
                                    <div className="text-xs text-gray-300 bg-white/10 px-2 py-1 rounded-full">{cart.count} قلم</div>
                                </div>
                            </div>
                            
                            <div className="p-4 space-y-3">
                                {cart.items.map((item) => (
                                    <div key={item.key} className="flex items-center gap-3 p-3 bg-white/5 rounded-xl">
                                        <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-cherry-500/20 to-violet-500/20 flex items-center justify-center text-lg flex-shrink-0">
                                            🛍️
                                        </div>
                                        
                                        <div className="flex-1 min-w-0">
                                            <h3 className="text-white font-semibold text-sm leading-tight truncate">{item.title}</h3>
                                            {item.variant_display_name && (
                                                <div className="text-xs text-gray-400 mt-0.5">{item.variant_display_name}</div>
                                            )}
                                            {item.campaign && (
                                                <div className="text-xs text-green-400 mt-0.5 flex items-center gap-1">
                                                    <span>🎉</span>
                                                    <span>{item.campaign.name}</span>
                                                </div>
                                            )}
                                            <div className="flex items-center gap-2 mt-1">
                                                {item.original_price && item.original_price !== item.price && (
                                                    <span className="text-xs text-gray-500 line-through">{formatPrice(item.original_price)}</span>
                                                )}
                                                <span className="text-xs text-gray-400">{item.quantity} × {formatPrice(item.price)}</span>
                                            </div>
                                        </div>
                                        
                                        <div className="text-right">
                                            <div className="text-white font-bold text-sm">{formatPrice(item.total)}</div>
                                            {item.total_discount > 0 && (
                                                <div className="text-xs text-green-400">صرفه‌جویی: {formatPrice(item.total_discount)}</div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                            
                            {/* Campaign Discount Summary */}
                            {cart.totalDiscount > 0 && (
                                <div className="mx-4 mb-4">
                                    <div className="bg-green-500/10 backdrop-blur-sm rounded-xl p-3 border border-green-500/20">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <span className="text-green-400 text-lg">🎉</span>
                                                <div>
                                                    <div className="text-green-400 font-medium text-sm">تخفیف کمپین</div>
                                                    <div className="text-green-300 text-xs">صرفه‌جویی از کمپین‌های فعال</div>
                                                </div>
                                            </div>
                                            <div className="text-green-400 font-bold">{formatPrice(cart.totalDiscount)}</div>
                                        </div>
                                    </div>
                                </div>
                            )}
                            
                            {/* Price Summary */}
                            <div className="px-4 pb-4 space-y-2">
                                {cart.totalDiscount > 0 && (
                                    <div className="flex items-center justify-between text-green-400 text-sm">
                                        <span className="flex items-center gap-1">
                                            <span>🎉</span>
                                            <span>تخفیف کمپین</span>
                                        </span>
                                        <span className="font-bold">-{formatPrice(cart.totalDiscount)}</span>
                                    </div>
                                )}
                                
                                <div className="flex items-center justify-between text-white">
                                    <span className="text-sm text-gray-300">
                                        {cart.totalDiscount > 0 ? 'جمع کل (پس از تخفیف)' : 'جمع کل'}
                                    </span>
                                    <span className="font-bold">{formatPrice(cart.total)}</span>
                                </div>
                                
                                {selectedDeliveryMethod && (
                                    <div className="flex items-center justify-between text-white">
                                        <span className="text-sm text-gray-300">هزینه ارسال</span>
                                        <span className={`font-bold ${
                                            selectedDeliveryMethod.fee === 0 
                                                ? 'text-green-400' 
                                                : 'text-white'
                                        }`}>
                                            {selectedDeliveryMethod.fee === 0 ? 'رایگان' : formatPrice(selectedDeliveryMethod.fee)}
                                        </span>
                                    </div>
                                )}
                                
                                {discountInfo && (
                                    <div className="flex items-center justify-between text-green-400 text-sm">
                                        <span>تخفیف ({discountInfo.code})</span>
                                        <span>-{formatPrice(discountInfo.amount)}</span>
                                    </div>
                                )}
                                
                                <div className="border-t border-white/10 pt-2">
                                    <div className="flex items-center justify-between text-white">
                                        <span className="font-semibold">مبلغ نهایی</span>
                                        <span className="font-extrabold text-cherry-400 text-lg">{formatPrice(finalAmount)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Form Card */}
                        <div className="bg-white/5 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-4 space-y-4">
                            <h2 className="text-white font-bold text-lg mb-4">اطلاعات سفارش</h2>
                            
                            <div>
                                <label className="block text-sm text-gray-300 mb-2">نام و نام خانوادگی</label>
                                <input 
                                    name="name" 
                                    value={form.name} 
                                    onChange={handleChange} 
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:ring-1 focus:border-cherry-500 focus:ring-cherry-500 transition-colors"
                                    required 
                                />
                            </div>
                            
                            <div>
                                <label className="block text-sm text-gray-300 mb-2">شماره تماس</label>
                                <input 
                                    name="phone" 
                                    value={form.phone} 
                                    onChange={handleChange} 
                                    placeholder="09123456789" 
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:ring-1 focus:border-cherry-500 focus:ring-cherry-500 transition-colors"
                                    required 
                                />
                            </div>
                            
                            {/* Address Selection */}
                            {authUser && (
                                <div>
                                    <label className="block text-sm text-gray-300 mb-2">
                                        {addresses.length > 0 ? 'انتخاب آدرس ذخیره شده' : 'آدرس ارسال'}
                                    </label>
                                    <AddressDropdown
                                        addresses={addresses}
                                        selectedAddress={selectedAddress}
                                        onSelect={handleAddressSelect}
                                        onAddNew={handleAddNewAddress}
                                        loading={addressLoading}
                                    />
                                </div>
                            )}
                            {!authUser && (
                                <div className="text-sm text-gray-400">
                                    برای افزودن آدرس، ابتدا وارد حساب کاربری خود شوید
                                </div>
                            )}

                            <div>
                                <label className="block text-sm text-gray-300 mb-2">آدرس کامل</label>
                                <textarea 
                                    name="address" 
                                    value={form.address} 
                                    onChange={handleChange} 
                                    rows={4} 
                                    className="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:ring-1 focus:border-cherry-500 focus:ring-cherry-500 transition-colors resize-none"
                                    required 
                                    placeholder="آدرس کامل خود را وارد کنید..."
                                />
                            </div>

                            {/* Delivery Method Selection */}
                            <div>
                                <label className="block text-sm font-medium text-gray-300 mb-3">روش ارسال *</label>
                                {deliveryMethods.length === 0 && !authUser ? (
                                    <div className="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-3 text-center">
                                        <div className="text-yellow-400 text-sm">
                                            برای مشاهده روش‌های ارسال، ابتدا وارد حساب کاربری خود شوید
                                        </div>
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {deliveryMethods.map((method) => (
                                        <div 
                                            key={method.id} 
                                            className={`relative cursor-pointer transition-all duration-200`}
                                            onClick={() => handleDeliveryMethodChange(method.id)}
                                        >
                                            <div className={`bg-white/5 rounded-xl p-3 border transition-all duration-200 ${
                                                form.delivery_method_id === method.id
                                                    ? 'border-cherry-500/50 bg-cherry-500/5'
                                                    : 'border-white/10 hover:border-cherry-400/30'
                                            }`}>
                                                <div className="flex items-center justify-between">
                                                    <div className="flex items-center gap-3">
                                                        <div className={`w-4 h-4 rounded-full border-2 transition-all duration-200 ${
                                                            form.delivery_method_id === method.id
                                                                ? 'border-cherry-500 bg-cherry-500'
                                                                : 'border-white/40 bg-white/5'
                                                        }`}>
                                                            {form.delivery_method_id === method.id && (
                                                                <div className="w-1.5 h-1.5 rounded-full bg-white mx-auto mt-0.5"></div>
                                                            )}
                                                        </div>
                                                        <span className="text-white font-medium text-sm">{method.title}</span>
                                                    </div>
                                                    <span className={`text-xs font-bold px-2 py-1 rounded-full ${
                                                        method.fee === 0 
                                                            ? 'text-green-400 bg-green-500/10' 
                                                            : 'text-cherry-400 bg-cherry-500/10'
                                                    }`}>
                                                        {method.fee === 0 ? 'رایگان' : formatPrice(method.fee)}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                    </div>
                                )}
                            </div>

                            {/* Payment Gateway Selection */}
                            <div>
                                <label className="block text-sm font-medium text-gray-300 mb-3">روش پرداخت *</label>
                                {paymentGateways.length === 0 ? (
                                    <div className="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-3 text-center">
                                        <div className="text-yellow-400 text-sm">
                                            در حال بارگذاری روش‌های پرداخت...
                                        </div>
                                    </div>
                                ) : (
                                    <div className="space-y-2">
                                        {paymentGateways.map((gateway) => (
                                            <div 
                                                key={gateway.id} 
                                                className={`relative cursor-pointer transition-all duration-200`}
                                                onClick={() => handlePaymentGatewayChange(gateway.id)}
                                            >
                                                <div className={`bg-white/5 rounded-xl p-3 border transition-all duration-200 ${
                                                    form.payment_gateway_id === gateway.id
                                                        ? 'border-cherry-500/50 bg-cherry-500/5'
                                                        : 'border-white/10 hover:border-cherry-400/30'
                                                }`}>
                                                    <div className="flex items-center justify-between">
                                                        <div className="flex items-center gap-3">
                                                            <div className={`w-4 h-4 rounded-full border-2 transition-all duration-200 ${
                                                                form.payment_gateway_id === gateway.id
                                                                    ? 'border-cherry-500 bg-cherry-500'
                                                                    : 'border-white/40 bg-white/5'
                                                            }`}>
                                                                {form.payment_gateway_id === gateway.id && (
                                                                    <div className="w-1.5 h-1.5 rounded-full bg-white mx-auto mt-0.5"></div>
                                                                )}
                                                            </div>
                                                            <span className="text-white font-medium text-sm">{gateway.display_name}</span>
                                                        </div>
                                                    </div>
                                                    {gateway.description && (
                                                        <div className="text-xs text-gray-400 mt-2 pr-7">
                                                            {gateway.description}
                                                        </div>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm text-gray-300 mb-2">کد تخفیف</label>
                                <div className="flex gap-2">
                                    <input 
                                        name="discount_code" 
                                        value={form.discount_code} 
                                        onChange={handleChange} 
                                        placeholder="کد تخفیف را وارد کنید" 
                                        className="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:ring-1 focus:border-cherry-500 focus:ring-cherry-500 transition-colors"
                                    />
                                    <button 
                                        type="button" 
                                        onClick={applyDiscount} 
                                        className="bg-gray-600 hover:bg-gray-700 text-white rounded-xl px-4 py-3 whitespace-nowrap transition-colors"
                                    >
                                        اعمال
                                    </button>
                                </div>
                                {discountInfo && (
                                    <div className="text-xs text-green-400 mt-2 flex items-center gap-1">
                                        <span>✅</span>
                                        <span>کد {discountInfo.code} اعمال شد ({formatPrice(discountInfo.amount)} تخفیف)</span>
                                    </div>
                                )}
                            </div>

                            {/* Card-to-Card Payment Info - Only show when card-to-card gateway is selected */}
                            {selectedGateway && selectedGateway.type === 'card_to_card' && selectedGateway.config && (
                                <>
                                    <div className="bg-gradient-to-br from-indigo-500/15 via-purple-500/15 to-cherry-500/25 relative overflow-hidden rounded-2xl border border-white/10 px-4 py-5">
                                        <div className="absolute inset-0 pointer-events-none">
                                            <div className="absolute -right-10 -top-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                                            <div className="absolute -left-10 bottom-0 w-40 h-40 bg-cherry-500/20 rounded-full blur-3xl"></div>
                                        </div>

                                        <div className="relative flex items-center justify-between gap-4">
                                            <div className="space-y-2">
                                                <div className="text-xs text-gray-300 tracking-[0.3em] uppercase">شماره کارت</div>
                                                <div className="text-white text-xl font-semibold tracking-[0.2em] sm:tracking-[0.25em] whitespace-nowrap" style={{ direction: 'ltr' }}>
                                                    {(() => {
                                                        const cardNumber = selectedGateway.config.card_number || PAYMENT_CARD.number;
                                                        const formatted = cardNumber.replace(/(.{4})/g, '$1 ').trim();
                                                        return formatted.replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
                                                    })()}
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                onClick={async () => {
                                                    const cardNumber = selectedGateway.config.card_number || PAYMENT_CARD.number;
                                                    const rawNumber = cardNumber.replace(/\s+/g, '');
                                                    if (copyingCard) return;
                                                    setCopyingCard(true);
                                                    try {
                                                        if (navigator.clipboard && navigator.clipboard.writeText) {
                                                            await navigator.clipboard.writeText(rawNumber);
                                                        } else {
                                                            const textArea = document.createElement('textarea');
                                                            textArea.value = rawNumber;
                                                            textArea.style.position = 'fixed';
                                                            textArea.style.left = '-9999px';
                                                            document.body.appendChild(textArea);
                                                            textArea.focus();
                                                            textArea.select();
                                                            document.execCommand('copy');
                                                            document.body.removeChild(textArea);
                                                        }
                                                        showToast('شماره کارت کپی شد', 'success');
                                                    } catch (err) {
                                                        showToast('امکان کپی خودکار وجود ندارد؛ لطفاً دستی کپی کنید', 'error');
                                                    } finally {
                                                        setTimeout(() => setCopyingCard(false), 400);
                                                    }
                                                }}
                                                disabled={copyingCard}
                                                className="shrink-0 rounded-xl bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-3 py-2 backdrop-blur flex items-center gap-2 transition-all duration-200 disabled:opacity-60"
                                            >
                                                <span className="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/20">
                                                    📋
                                                </span>
                                                <span>کپی</span>
                                            </button>
                                        </div>

                                        <div className="relative mt-6 flex flex-wrap items-center gap-4">
                                            <div>
                                                <div className="text-xs text-gray-300">به نام</div>
                                                <div className="text-sm font-semibold text-white">{selectedGateway.config.card_holder || PAYMENT_CARD.holder}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <FileUpload
                                        name="receipt"
                                        value={form.receipt}
                                        onChange={(file) => handleFileChange('receipt', file)}
                                        accept="image/*"
                                        required={true}
                                        label="آپلود فیش واریزی"
                                        placeholder="فیش واریزی را انتخاب کنید"
                                        className="mt-2"
                                    />
                                </>
                            )}

                            <button 
                                type="submit" 
                                onClick={handleSubmit}
                                disabled={submitting} 
                                className="w-full bg-gradient-to-r from-cherry-600 to-cherry-500 hover:from-cherry-500 hover:to-cherry-400 disabled:opacity-60 text-white rounded-xl px-4 py-4 font-semibold text-lg transition-all duration-200 shadow-lg"
                            >
                                {submitting ? 'در حال ثبت...' : 'ثبت سفارش'}
                            </button>
                        </div>
                    </div>

                    {/* Desktop Layout */}
                    <div className="hidden lg:grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Summary / Invoice */}
                        <div className="lg:col-span-2">
                            <div className="bg-white/5 glass-card rounded-xl overflow-hidden soft-shadow">
                                <div className="bg-gradient-to-r from-white/10 to-white/0 px-4 py-3 flex items-center justify-between">
                                    <div className="text-white font-bold">جزئیات سفارش</div>
                                    <div className="text-xs text-gray-300">{cart.count} قلم</div>
                                </div>
                                <div className="divide-y divide-white/10">
                                    {cart.items.map((item) => (
                                        <div key={item.key} className="p-3 md:p-4 flex items-start gap-3 md:gap-4">
                                            <div className="w-14 h-14 rounded bg-white/10 flex items-center justify-center">🧾</div>
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-start justify-between gap-2">
                                                    <div>
                                                        <div className="text-white font-semibold truncate max-w-[200px] md:max-w-none">{item.product?.title}</div>
                                                        {item.color_id && item.size_id && (
                                                            <div className="text-xs text-gray-300 mt-0.5">رنگ: {item.color_id}, سایز: {item.size_id}</div>
                                                        )}
                                                        {item.product?.campaign && (
                                                            <div className="text-xs text-green-400 mt-0.5 flex items-center gap-1">
                                                                <span>🎉</span>
                                                                <span>{item.product.campaign.name}</span>
                                                            </div>
                                                        )}
                                                        <div className="flex items-center gap-2 mt-1">
                                                            {item.product?.original_price && item.product.original_price !== item.product?.price && (
                                                                <span className="text-xs text-gray-500 line-through">{formatPrice(item.product.original_price)}</span>
                                                            )}
                                                            <span className="text-xs text-gray-400">{item.quantity} × {formatPrice(item.product?.price)}</span>
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                    <div className="text-white font-bold text-sm md:text-base">{formatPrice(item.quantity * item.product?.price)} تومان</div>
                                                        {item.total_discount > 0 && (
                                                            <div className="text-xs text-green-400">صرفه‌جویی: {formatPrice(item.total_discount)}</div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="p-4">
                                    {cart.totalDiscount > 0 && (
                                        <div className="flex items-center justify-between text-green-400 mb-2">
                                            <span className="text-sm flex items-center gap-1">
                                                <span>🎉</span>
                                                <span>تخفیف کمپین</span>
                                            </span>
                                            <span className="font-bold">-{formatPrice(cart.totalDiscount)} تومان</span>
                                        </div>
                                    )}
                                    <div className="flex items-center justify-between text-white mb-1">
                                        <span className="text-sm text-gray-300">
                                            {cart.totalDiscount > 0 ? 'جمع کل (پس از تخفیف کمپین)' : 'جمع کل'}
                                        </span>
                                        <span className="font-extrabold">{formatPrice(cart.total)} تومان</span>
                                    </div>
                                    {selectedDeliveryMethod && (
                                        <div className="flex items-center justify-between text-white mb-1">
                                            <span className="text-sm text-gray-300">هزینه ارسال ({selectedDeliveryMethod.title})</span>
                                            <span className={`font-bold ${
                                                selectedDeliveryMethod.fee === 0 
                                                    ? 'text-green-400' 
                                                    : 'text-white'
                                            }`}>
                                                {selectedDeliveryMethod.fee === 0 ? 'رایگان' : `${formatPrice(selectedDeliveryMethod.fee)} تومان`}
                                            </span>
                                        </div>
                                    )}
                                    {discountInfo && (
                                        <div className="flex items-center justify-between text-green-400 text-sm mb-1">
                                            <span>تخفیف ({discountInfo.code})</span>
                                            <span>-{formatPrice(discountInfo.amount)} تومان</span>
                                        </div>
                                    )}
                                    <div className="flex items-center justify-between text-white mt-2">
                                        <span className="font-semibold">مبلغ نهایی</span>
                                        <span className="font-extrabold text-cherry-400">{formatPrice(finalAmount)} تومان</span>
                                    </div>
                                </div>
                                
                                {/* Campaign Discount Summary */}
                                {cart.totalDiscount > 0 && (
                                    <div className="bg-green-500/10 backdrop-blur-sm rounded-xl p-4 border border-green-500/20 mx-4 mb-4">
                                        <div className="flex items-center justify-between">
                                            <div className="flex items-center gap-2">
                                                <span className="text-green-400 text-lg">🎉</span>
                                                <div>
                                                    <div className="text-green-400 font-medium text-sm">تخفیف کمپین</div>
                                                    <div className="text-green-300 text-xs">شما از کمپین‌های فعال صرفه‌جویی کرده‌اید</div>
                                                </div>
                                            </div>
                                            <div className="text-green-400 font-bold text-lg">{formatPrice(cart.totalDiscount)} تومان</div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Form */}
                        <div>
                            <form onSubmit={handleSubmit} className="bg-white/5 glass-card rounded-xl p-4 space-y-3 soft-shadow">
                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">نام و نام خانوادگی</label>
                                    <input 
                                        name="name" 
                                        value={form.name} 
                                        onChange={handleChange} 
                                        className="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white"
                                        required 
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">شماره تماس</label>
                                    <input 
                                        name="phone" 
                                        value={form.phone} 
                                        onChange={handleChange} 
                                        placeholder="09123456789" 
                                        className="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white"
                                        required 
                                    />
                                </div>
                                {/* Address Selection */}
                                {authUser && (
                                    <div>
                                        <label className="block text-sm text-gray-300 mb-2">
                                            {addresses.length > 0 ? 'انتخاب آدرس ذخیره شده' : 'آدرس ارسال'}
                                        </label>
                                        <AddressDropdown
                                            addresses={addresses}
                                            selectedAddress={selectedAddress}
                                            onSelect={handleAddressSelect}
                                            onAddNew={handleAddNewAddress}
                                            loading={addressLoading}
                                        />
                                    </div>
                                )}
                                {!authUser && (
                                    <div className="text-sm text-gray-400">
                                        برای افزودن آدرس، ابتدا وارد حساب کاربری خود شوید
                                    </div>
                                )}

                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">آدرس کامل</label>
                                    <textarea 
                                        name="address" 
                                        value={form.address} 
                                        onChange={handleChange} 
                                        rows={4} 
                                        className="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white" 
                                        required 
                                        placeholder="آدرس کامل خود را وارد کنید..."
                                    />
                                </div>

                                {/* Delivery Method Selection */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-300 mb-3">روش ارسال *</label>
                                    {deliveryMethods.length === 0 && !authUser ? (
                                        <div className="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-3 text-center">
                                            <div className="text-yellow-400 text-sm">
                                                برای مشاهده روش‌های ارسال، ابتدا وارد حساب کاربری خود شوید
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="space-y-3">
                                            {deliveryMethods.map((method) => (
                                            <div 
                                                key={method.id} 
                                                className={`relative cursor-pointer transition-all duration-200`}
                                                onClick={() => handleDeliveryMethodChange(method.id)}
                                            >
                                                {/* Hidden Radio Input */}
                                                <input
                                                    type="radio"
                                                    name="delivery_method_id"
                                                    value={method.id}
                                                    checked={form.delivery_method_id === method.id}
                                                    onChange={() => {}} // Handled by onClick
                                                    className="sr-only"
                                                />
                                                
                                                {/* Card Container */}
                                                <div className={`bg-white/5 rounded-2xl p-4 border transition-all duration-200 ${
                                                    form.delivery_method_id === method.id
                                                        ? 'border-cherry-500/50 bg-cherry-500/5'
                                                        : 'border-white/10 hover:border-cherry-400/30 hover:bg-white/10'
                                                }`}>
                                                    <div className="flex items-start gap-3">
                                                        {/* Radio Button */}
                                                        <div className={`w-5 h-5 rounded-full border-2 transition-all duration-200 flex items-center justify-center flex-shrink-0 mt-0.5 ${
                                                            form.delivery_method_id === method.id
                                                                ? 'border-cherry-500 bg-cherry-500'
                                                                : 'border-white/40 bg-white/5'
                                                        }`}>
                                                            {form.delivery_method_id === method.id && (
                                                                <div className="w-2 h-2 rounded-full bg-white"></div>
                                                            )}
                                                        </div>
                                                        
                                                        {/* Content */}
                                                        <div className="flex-1 min-w-0">
                                                            <div className="flex items-center justify-between gap-2">
                                                                {/* Method Title */}
                                                                <h3 className="text-white font-medium text-sm leading-tight">
                                                                    {method.title}
                                                                </h3>
                                                                
                                                                {/* Price Badge */}
                                                                <span className={`text-xs font-bold px-2 py-1 rounded-full flex-shrink-0 ${
                                                                    method.fee === 0 
                                                                        ? 'text-green-400 bg-green-500/10' 
                                                                        : 'text-cherry-400 bg-cherry-500/10'
                                                                }`}>
                                                                    {method.fee === 0 ? 'رایگان' : `${formatPrice(method.fee)} تومان`}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                        </div>
                                    )}
                                </div>

                                {/* Payment Gateway Selection */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-300 mb-3">روش پرداخت *</label>
                                    {paymentGateways.length === 0 ? (
                                        <div className="bg-yellow-500/10 border border-yellow-500/20 rounded-xl p-3 text-center">
                                            <div className="text-yellow-400 text-sm">
                                                در حال بارگذاری روش‌های پرداخت...
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="space-y-3">
                                            {paymentGateways.map((gateway) => (
                                                <div 
                                                    key={gateway.id} 
                                                    className={`relative cursor-pointer transition-all duration-200`}
                                                    onClick={() => handlePaymentGatewayChange(gateway.id)}
                                                >
                                                    <div className={`bg-white/5 rounded-2xl p-4 border transition-all duration-200 ${
                                                        form.payment_gateway_id === gateway.id
                                                            ? 'border-cherry-500/50 bg-cherry-500/5'
                                                            : 'border-white/10 hover:border-cherry-400/30 hover:bg-white/10'
                                                    }`}>
                                                        <div className="flex items-start gap-3">
                                                            <div className={`w-5 h-5 rounded-full border-2 transition-all duration-200 flex items-center justify-center flex-shrink-0 mt-0.5 ${
                                                                form.payment_gateway_id === gateway.id
                                                                    ? 'border-cherry-500 bg-cherry-500'
                                                                    : 'border-white/40 bg-white/5'
                                                            }`}>
                                                                {form.payment_gateway_id === gateway.id && (
                                                                    <div className="w-2 h-2 rounded-full bg-white"></div>
                                                                )}
                                                            </div>
                                                            <div className="flex-1 min-w-0">
                                                                <h3 className="text-white font-medium text-sm leading-tight">
                                                                    {gateway.display_name}
                                                                </h3>
                                                                {gateway.description && (
                                                                    <div className="text-xs text-gray-400 mt-1">
                                                                        {gateway.description}
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">کد تخفیف</label>
                                    <div className="flex gap-2">
                                        <input 
                                            name="discount_code" 
                                            value={form.discount_code} 
                                            onChange={handleChange} 
                                            placeholder="کد تخفیف را وارد کنید" 
                                            className="flex-1 bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-white"
                                        />
                                        <button type="button" onClick={applyDiscount} className="bg-gray-600 hover:bg-gray-700 text-white rounded-lg px-4 py-2 whitespace-nowrap">اعمال</button>
                                    </div>
                                    {discountInfo && (
                                        <div className="text-xs text-green-400 mt-2">✅ کد {discountInfo.code} اعمال شد ({formatPrice(discountInfo.amount)} تومان تخفیف)</div>
                                    )}
                                </div>

                                {/* Card-to-Card Payment Info - Only show when card-to-card gateway is selected */}
                                {selectedGateway && selectedGateway.type === 'card_to_card' && selectedGateway.config && (
                                    <>
                                        <div className="bg-gradient-to-br from-indigo-500/15 via-purple-500/15 to-cherry-500/25 relative overflow-hidden rounded-2xl border border-white/10 px-4 py-5">
                                            <div className="absolute inset-0 pointer-events-none">
                                                <div className="absolute -right-10 -top-12 w-44 h-44 bg-white/10 rounded-full blur-3xl"></div>
                                                <div className="absolute -left-10 bottom-0 w-56 h-56 bg-cherry-500/20 rounded-full blur-[70px]"></div>
                                            </div>

                                            <div className="relative flex items-start justify-between gap-6">
                                                <div className="space-y-2">
                                                    <div className="text-xs text-gray-300 tracking-[0.3em] uppercase">شماره کارت</div>
                                                    <div className="text-white text-2xl font-semibold tracking-[0.22em] sm:tracking-[0.3em] whitespace-nowrap" style={{ direction: 'ltr' }}>
                                                        {(() => {
                                                            const cardNumber = selectedGateway.config.card_number || PAYMENT_CARD.number;
                                                            const formatted = cardNumber.replace(/(.{4})/g, '$1 ').trim();
                                                            return formatted.replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
                                                        })()}
                                                    </div>
                                                </div>
                                                <button
                                                    type="button"
                                                    onClick={async () => {
                                                        const cardNumber = selectedGateway.config.card_number || PAYMENT_CARD.number;
                                                        const rawNumber = cardNumber.replace(/\s+/g, '');
                                                        if (copyingCard) return;
                                                        setCopyingCard(true);
                                                        try {
                                                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                                                await navigator.clipboard.writeText(rawNumber);
                                                            } else {
                                                                const textArea = document.createElement('textarea');
                                                                textArea.value = rawNumber;
                                                                textArea.style.position = 'fixed';
                                                                textArea.style.left = '-9999px';
                                                                document.body.appendChild(textArea);
                                                                textArea.focus();
                                                                textArea.select();
                                                                document.execCommand('copy');
                                                                document.body.removeChild(textArea);
                                                            }
                                                            showToast('شماره کارت کپی شد', 'success');
                                                        } catch (err) {
                                                            showToast('امکان کپی خودکار وجود ندارد؛ لطفاً دستی کپی کنید', 'error');
                                                        } finally {
                                                            setTimeout(() => setCopyingCard(false), 400);
                                                        }
                                                    }}
                                                    disabled={copyingCard}
                                                    className="shrink-0 rounded-2xl bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-3 py-2.5 backdrop-blur flex items-center gap-2 transition-all duration-200 disabled:opacity-60"
                                                >
                                                    <span className="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                                                        📋
                                                    </span>
                                                    <span>کپی شماره کارت</span>
                                                </button>
                                            </div>

                                            <div className="relative mt-6 flex items-center gap-4">
                                                <div>
                                                    <div className="text-xs text-gray-300">به نام</div>
                                                    <div className="text-sm md:text-base font-semibold text-white">{selectedGateway.config.card_holder || PAYMENT_CARD.holder}</div>
                                                </div>
                                                <div className="ml-auto text-xs text-gray-300 bg-white/10 px-3 py-1.5 rounded-full backdrop-blur">
                                                    لطفاً پس از پرداخت، فیش واریزی را بارگذاری کنید
                                                </div>
                                            </div>
                                        </div>

                                        <FileUpload
                                            name="receipt"
                                            value={form.receipt}
                                            onChange={(file) => handleFileChange('receipt', file)}
                                            accept="image/*"
                                            required={true}
                                            label="آپلود فیش واریزی"
                                            placeholder="فیش واریزی را انتخاب کنید"
                                            className="mt-2"
                                        />
                                    </>
                                )}

                                <button type="submit" disabled={submitting} className="w-full bg-cherry-600 hover:bg-cherry-500 disabled:opacity-60 text-white rounded-lg px-4 py-2.5">
                                    {submitting ? 'در حال ثبت...' : 'ثبت سفارش'}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            <CheckoutAuthModal
                open={authOpen}
                onClose={() => setAuthOpen(false)}
                onSuccess={(user) => {
                    setForm((prev) => ({ ...prev, name: user.name || '', phone: user.phone || '', address: user.address || '' }));
                    setAuthOpen(false);
                    // Refetch delivery methods and addresses after successful authentication
                    fetchDeliveryMethods();
                    // Small delay to ensure auth state is updated
                    setTimeout(() => {
                        fetchAddresses();
                    }, 100);
                }}
            />

            <AddressModal
                open={addressModalOpen}
                onClose={() => {
                    setAddressModalOpen(false);
                    setEditingAddress(null);
                }}
                onSave={handleSaveAddress}
                address={editingAddress}
                loading={addressLoading}
            />
        </div>
    );
}

export default CheckoutPage;