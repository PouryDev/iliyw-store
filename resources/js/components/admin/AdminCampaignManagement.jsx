import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { adminApiRequest } from '../../utils/adminApi';

function AdminCampaignManagement() {
    const navigate = useNavigate();
    const [campaigns, setCampaigns] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadCampaigns = async () => {
            try {
                setLoading(true);
                const res = await adminApiRequest('/campaigns');
                
                if (res.ok) {
                    const data = await res.json();
                    if (data.success) {
                        setCampaigns(data.data);
                    }
                }
            } catch (error) {
                console.error('Failed to load campaigns:', error);
            } finally {
                setLoading(false);
            }
        };

        loadCampaigns();
    }, []);

    const formatPrice = (value) => {
        try { 
            return Number(value || 0).toLocaleString('fa-IR'); 
        } catch { 
            return value || '0'; 
        }
    };

    const handleDeleteCampaign = async (campaignId) => {
        if (!confirm('آیا مطمئن هستید که می‌خواهید این کمپین را حذف کنید؟')) {
            return;
        }

        try {
            const res = await adminApiRequest(`/campaigns/${campaignId}`, { method: 'DELETE' });

            if (res.ok) {
                setCampaigns(campaigns.filter(c => c.id !== campaignId));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'کمپین با موفقیت حذف شد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to delete campaign:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در حذف کمپین' } 
            }));
        }
    };

    const toggleCampaignStatus = async (campaignId, currentStatus) => {
        try {
            const res = await adminApiRequest(`/campaigns/${campaignId}/toggle`, { method: 'PATCH' });

            if (res.ok) {
                setCampaigns(campaigns.map(c => 
                    c.id === campaignId 
                        ? { ...c, is_active: !currentStatus }
                        : c
                ));
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'وضعیت کمپین تغییر کرد' } 
                }));
            }
        } catch (error) {
            console.error('Failed to toggle campaign:', error);
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در تغییر وضعیت' } 
            }));
        }
    };

    if (loading) {
        return (
            <div className="max-w-6xl mx-auto">
                <div className="flex items-center justify-center min-h-96">
                    <div className="text-center">
                        <div className="w-12 h-12 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
                        <p className="text-gray-400">در حال بارگذاری...</p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="max-w-6xl mx-auto">
            {/* Header */}
            <div className="mb-8">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold text-white mb-2">مدیریت کمپین‌ها</h1>
                        <p className="text-gray-400">مدیریت و ویرایش کمپین‌های فروشگاه</p>
                    </div>
                    <button
                        onClick={() => navigate('/admin/campaigns/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg flex items-center space-x-2 space-x-reverse"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span>کمپین جدید</span>
                    </button>
                </div>
            </div>

            {/* Campaigns List */}
            <div className="space-y-6">
                {campaigns.map((campaign) => (
                    <div key={campaign.id} className="bg-gradient-to-br from-white/5 to-white/10 backdrop-blur-xl rounded-xl border border-white/10 shadow-2xl p-6">
                        <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                            {/* Campaign Info */}
                            <div className="flex-1">
                                <div className="flex items-center gap-4 mb-4">
                                    <h3 className="text-white font-bold text-xl">{campaign.title}</h3>
                                    <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                                        campaign.is_active 
                                            ? 'bg-green-500/20 text-green-400 border border-green-500/30' 
                                            : 'bg-red-500/20 text-red-400 border border-red-500/30'
                                    }`}>
                                        {campaign.is_active ? 'فعال' : 'غیرفعال'}
                                    </span>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p className="text-gray-400 text-sm">نوع تخفیف</p>
                                        <p className="text-white font-medium">
                                            {campaign.discount_type === 'percentage' ? 'درصدی' : 'مبلغی'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">مقدار تخفیف</p>
                                        <p className="text-white font-medium">
                                            {campaign.discount_type === 'percentage' 
                                                ? `${campaign.discount_value}%` 
                                                : `${formatPrice(campaign.discount_value)} تومان`
                                            }
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">تعداد محصولات</p>
                                        <p className="text-white font-medium">
                                            {campaign.products?.length || 0} محصول
                                        </p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p className="text-gray-400 text-sm">تاریخ شروع</p>
                                        <p className="text-white font-medium">
                                            {campaign.starts_at ? new Date(campaign.starts_at).toLocaleDateString('fa-IR') : 'فوری'}
                                        </p>
                                    </div>
                                    <div>
                                        <p className="text-gray-400 text-sm">تاریخ انقضا</p>
                                        <p className="text-white font-medium">
                                            {campaign.expires_at ? new Date(campaign.expires_at).toLocaleDateString('fa-IR') : 'بدون انقضا'}
                                        </p>
                                    </div>
                                </div>

                                {campaign.description && (
                                    <div className="mb-4">
                                        <p className="text-gray-400 text-sm">توضیحات</p>
                                        <p className="text-white">{campaign.description}</p>
                                    </div>
                                )}

                                {/* Products Preview */}
                                {campaign.products && campaign.products.length > 0 && (
                                    <div>
                                        <p className="text-gray-400 text-sm mb-2">محصولات کمپین:</p>
                                        <div className="flex flex-wrap gap-2">
                                            {campaign.products.slice(0, 5).map((product) => (
                                                <span key={product.id} className="px-2 py-1 bg-purple-500/20 text-purple-400 rounded-lg text-xs">
                                                    {product.title}
                                                </span>
                                            ))}
                                            {campaign.products.length > 5 && (
                                                <span className="px-2 py-1 bg-gray-500/20 text-gray-400 rounded-lg text-xs">
                                                    +{campaign.products.length - 5} محصول دیگر
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Actions */}
                            <div className="flex flex-col sm:flex-row gap-2">
                                <button
                                    onClick={() => navigate(`/admin/campaigns/${campaign.id}/edit`)}
                                    className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                >
                                    ویرایش
                                </button>
                                <button
                                    onClick={() => toggleCampaignStatus(campaign.id, campaign.is_active)}
                                    className={`font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm ${
                                        campaign.is_active
                                            ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white'
                                            : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white'
                                    }`}
                                >
                                    {campaign.is_active ? 'غیرفعال کردن' : 'فعال کردن'}
                                </button>
                                <button
                                    onClick={() => handleDeleteCampaign(campaign.id)}
                                    className="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-all duration-200 hover:scale-105 text-sm"
                                >
                                    حذف
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Empty State */}
            {campaigns.length === 0 && !loading && (
                <div className="text-center py-12">
                    <div className="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg className="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 className="text-white text-xl font-semibold mb-2">کمپینی یافت نشد</h3>
                    <p className="text-gray-400 mb-6">هنوز کمپینی ایجاد نکرده‌اید</p>
                    <button
                        onClick={() => navigate('/admin/campaigns/create')}
                        className="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 hover:scale-105 shadow-lg"
                    >
                        اولین کمپین را ایجاد کنید
                    </button>
                </div>
            )}
        </div>
    );
}

export default AdminCampaignManagement;
