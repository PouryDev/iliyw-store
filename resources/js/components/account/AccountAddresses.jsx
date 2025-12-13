import React, { useState, useEffect } from 'react';
import { useAuth } from '../../contexts/AuthContext';
import AddressModal from '../AddressModal';
import { apiRequest } from '../../utils/sanctumAuth';

function AccountAddresses() {
    const { user } = useAuth();
    const [addresses, setAddresses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [addressModalOpen, setAddressModalOpen] = useState(false);
    const [editingAddress, setEditingAddress] = useState(null);

    useEffect(() => {
        fetchAddresses();
    }, []);

    const fetchAddresses = async () => {
        try {
            const res = await apiRequest('/api/addresses');
            if (res.ok) {
                const data = await res.json();
                setAddresses(data.data || []);
            }
        } catch (error) {
            console.error('Error fetching addresses:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleAddNew = () => {
        setEditingAddress(null);
        setAddressModalOpen(true);
    };

    const handleEdit = (address) => {
        setEditingAddress(address);
        setAddressModalOpen(true);
    };

    const handleDelete = async (addressId) => {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این آدرس را حذف کنید؟')) {
            return;
        }

        try {
            const res = await apiRequest(`/api/addresses/${addressId}`, {
                method: 'DELETE',
            });

            if (res.ok) {
                setAddresses(prev => prev.filter(addr => addr.id !== addressId));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'آدرس با موفقیت حذف شد' } 
                }));
            }
        } catch (error) {
            console.error('Error deleting address:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در حذف آدرس' } 
            }));
        }
    };

    const handleSetDefault = async (addressId) => {
        try {
            const res = await apiRequest(`/api/addresses/${addressId}/set-default`, {
                method: 'POST',
            });

            if (res.ok) {
                setAddresses(prev => 
                    prev.map(addr => ({
                        ...addr,
                        is_default: addr.id === addressId
                    }))
                );
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'آدرس پیش‌فرض تنظیم شد' } 
                }));
            }
        } catch (error) {
            console.error('Error setting default address:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در تنظیم آدرس پیش‌فرض' } 
            }));
        }
    };

    const handleSaveAddress = () => {
        // Refresh addresses list after successful save
        fetchAddresses();
        setAddressModalOpen(false);
        setEditingAddress(null);
    };

    if (loading) {
        return (
            <div className="max-w-4xl mx-auto">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-white mb-2">آدرس‌های من</h1>
                    <p className="text-gray-400">مدیریت آدرس‌های ذخیره شده</p>
                </div>
                <div className="flex items-center justify-center py-20">
                    <div className="w-8 h-8 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-4xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <h1 className="text-3xl font-bold text-white mb-2">آدرس‌های من</h1>
                <p className="text-gray-400">مدیریت آدرس‌های ذخیره شده</p>
            </div>

            {/* Add Address Button */}
            <div className="mb-6">
                <button
                    onClick={handleAddNew}
                    className="bg-gradient-to-r from-cherry-500 to-cherry-600 hover:from-cherry-600 hover:to-cherry-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg flex items-center space-x-2 space-x-reverse"
                >
                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span>افزودن آدرس جدید</span>
                </button>
            </div>

            {/* Addresses List */}
            {addresses.length === 0 ? (
                <div className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-12 text-center">
                    <div className="w-20 h-20 bg-gray-500/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg className="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">هنوز آدرسی اضافه نکرده‌اید</h3>
                    <p className="text-gray-400 mb-6">اولین آدرس خود را اضافه کنید</p>
                    <button
                        onClick={handleAddNew}
                        className="bg-gradient-to-r from-cherry-500 to-cherry-600 hover:from-cherry-600 hover:to-cherry-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg"
                    >
                        افزودن آدرس
                    </button>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {addresses.map((address) => (
                        <div
                            key={address.id}
                            className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-2xl border border-white/10 shadow-2xl p-6 hover:shadow-3xl transition-all duration-200"
                        >
                            {/* Address Header */}
                            <div className="flex items-start justify-between mb-4">
                                <div className="flex items-center space-x-3 space-x-reverse">
                                    <div className="w-10 h-10 bg-cherry-500/20 rounded-xl flex items-center justify-center">
                                        <svg className="w-5 h-5 text-cherry-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 className="text-white font-semibold text-lg">{address.title}</h3>
                                        {address.is_default && (
                                            <span className="text-cherry-400 text-sm font-medium">آدرس پیش‌فرض</span>
                                        )}
                                    </div>
                                </div>
                                <div className="flex space-x-2 space-x-reverse">
                                    <button
                                        onClick={() => handleEdit(address)}
                                        className="text-gray-400 hover:text-white transition-colors p-2"
                                        title="ویرایش"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button
                                        onClick={() => handleDelete(address.id)}
                                        className="text-red-400 hover:text-red-300 transition-colors p-2"
                                        title="حذف"
                                    >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            {/* Address Details */}
                            <div className="space-y-3 mb-6">
                                <div className="bg-white/5 rounded-xl p-4">
                                    <h4 className="text-white font-medium mb-2">آدرس</h4>
                                    <p className="text-gray-300 text-sm leading-relaxed">{address.address}</p>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="bg-white/5 rounded-xl p-3">
                                        <h4 className="text-white font-medium text-sm mb-1">شهر</h4>
                                        <p className="text-gray-300 text-sm">{address.city}</p>
                                    </div>
                                    <div className="bg-white/5 rounded-xl p-3">
                                        <h4 className="text-white font-medium text-sm mb-1">استان</h4>
                                        <p className="text-gray-300 text-sm">{address.province}</p>
                                    </div>
                                </div>

                                <div className="bg-white/5 rounded-xl p-3">
                                    <h4 className="text-white font-medium text-sm mb-1">کد پستی</h4>
                                    <p className="text-gray-300 text-sm">{address.postal_code}</p>
                                </div>

                                {(address.recipient_name || address.recipient_phone) && (
                                    <div className="bg-white/5 rounded-xl p-3">
                                        <h4 className="text-white font-medium text-sm mb-2">اطلاعات گیرنده</h4>
                                        <div className="space-y-1 text-sm">
                                            {address.recipient_name && (
                                                <p className="text-gray-300">نام: {address.recipient_name}</p>
                                            )}
                                            {address.recipient_phone && (
                                                <p className="text-gray-300">تلفن: {address.recipient_phone}</p>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Actions */}
                            <div className="flex space-x-3 space-x-reverse">
                                {!address.is_default && (
                                    <button
                                        onClick={() => handleSetDefault(address.id)}
                                        className="flex-1 bg-white/10 hover:bg-white/20 text-white py-2 px-4 rounded-lg transition-colors duration-200 text-sm font-medium"
                                    >
                                        تنظیم به عنوان پیش‌فرض
                                    </button>
                                )}
                                <button
                                    onClick={() => handleEdit(address)}
                                    className="flex-1 bg-cherry-500/20 hover:bg-cherry-500/30 text-cherry-400 py-2 px-4 rounded-lg transition-colors duration-200 text-sm font-medium"
                                >
                                    ویرایش
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {/* Address Modal */}
            <AddressModal
                open={addressModalOpen}
                onClose={() => {
                    setAddressModalOpen(false);
                    setEditingAddress(null);
                }}
                onSave={handleSaveAddress}
                address={editingAddress}
            />
        </div>
    );
}

export default AccountAddresses;
