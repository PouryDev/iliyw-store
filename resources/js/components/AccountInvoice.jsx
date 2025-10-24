import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useSeo } from '../hooks/useSeo';
import LoadingSpinner from './LoadingSpinner';

function AccountInvoice() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [invoice, setInvoice] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchInvoice();
    }, [id]);

    useSeo({
        title: `فاکتور #${id} - فروشگاه جمه`,
        description: 'مشاهده فاکتور',
        canonical: window.location.origin + `/account/invoices/${id}`
    });

    const fetchInvoice = async () => {
        setLoading(true);
        try {
            const res = await fetch(`/api/account/invoices/${id}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!res.ok) throw new Error('failed');
            const data = await res.json();
            setInvoice(data.invoice);
        } catch (e) {
            console.error(e);
        } finally {
            setLoading(false);
        }
    };

    const formatPrice = (v) => {
        try { return Number(v || 0).toLocaleString('fa-IR'); } catch { return v; }
    };

    const handlePrint = () => {
        window.print();
    };

    if (loading) {
        return (
            <div className="flex justify-center py-12">
                <LoadingSpinner />
            </div>
        );
    }

    if (!invoice) {
        return (
            <div className="glass-card rounded-2xl p-8 border border-white/10 text-center">
                <div className="text-6xl mb-4">❌</div>
                <h3 className="text-xl font-semibold text-white mb-2">فاکتور یافت نشد</h3>
                <button onClick={() => navigate(-1)} className="text-amber-400 hover:text-amber-300">
                    بازگشت
                </button>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {/* Header */}
            <div className="flex items-center justify-between print:hidden">
                <button
                    onClick={() => navigate(-1)}
                    className="flex items-center gap-1 text-amber-400 hover:text-amber-300 text-sm"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/></svg>
                    بازگشت
                </button>
                <button
                    onClick={handlePrint}
                    className="flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold transition"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    چاپ فاکتور
                </button>
            </div>

            {/* Invoice Card */}
            <div className="glass-card rounded-2xl border border-white/10 overflow-hidden">
                {/* Header */}
                <div className="bg-gradient-to-r from-amber-600/20 to-indigo-600/20 p-6 border-b border-white/10">
                    <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h2 className="text-2xl font-bold text-white mb-1">فاکتور فروش</h2>
                            <p className="text-sm text-gray-300">شماره فاکتور: #{invoice.id}</p>
                        </div>
                        <div className="text-right">
                            <p className="text-sm text-gray-300">تاریخ صدور:</p>
                            <p className="text-white font-semibold">
                                {new Date(invoice.created_at).toLocaleDateString('fa-IR')}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="p-6 space-y-6">
                    {/* Shop & Customer Info */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="bg-white/5 rounded-lg p-4">
                            <h3 className="text-sm font-bold text-gray-400 mb-2">فروشنده</h3>
                            <p className="text-white font-semibold mb-1">فروشگاه جمه</p>
                            <p className="text-sm text-gray-300">استایل مینیمال با عطر شمال</p>
                        </div>
                        <div className="bg-white/5 rounded-lg p-4">
                            <h3 className="text-sm font-bold text-gray-400 mb-2">خریدار</h3>
                            <p className="text-white font-semibold mb-1">{invoice.customer_name}</p>
                            <p className="text-sm text-gray-300">{invoice.customer_phone}</p>
                            {invoice.customer_address && (
                                <p className="text-sm text-gray-400 mt-1 line-clamp-2">{invoice.customer_address}</p>
                            )}
                        </div>
                    </div>

                    {/* Items Table */}
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-white/10">
                                    <th className="text-right py-3 px-2 text-sm font-bold text-gray-400">ردیف</th>
                                    <th className="text-right py-3 px-2 text-sm font-bold text-gray-400">محصول</th>
                                    <th className="text-center py-3 px-2 text-sm font-bold text-gray-400">تعداد</th>
                                    <th className="text-left py-3 px-2 text-sm font-bold text-gray-400">قیمت واحد</th>
                                    <th className="text-left py-3 px-2 text-sm font-bold text-gray-400">جمع</th>
                                </tr>
                            </thead>
                            <tbody>
                                {invoice.order?.items?.map((item, index) => (
                                    <tr key={item.id} className="border-b border-white/5">
                                        <td className="py-3 px-2 text-white">{index + 1}</td>
                                        <td className="py-3 px-2">
                                            <div className="text-white font-semibold">{item.product_title}</div>
                                            {(item.color_name || item.size_name) && (
                                                <div className="text-xs text-gray-400">
                                                    {item.color_name && `رنگ: ${item.color_name}`}
                                                    {item.color_name && item.size_name && ' - '}
                                                    {item.size_name && `سایز: ${item.size_name}`}
                                                </div>
                                            )}
                                        </td>
                                        <td className="py-3 px-2 text-center text-white">{formatPrice(item.quantity)}</td>
                                        <td className="py-3 px-2 text-left text-white">{formatPrice(item.unit_price)}</td>
                                        <td className="py-3 px-2 text-left text-white font-semibold">
                                            {formatPrice(item.unit_price * item.quantity)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Summary */}
                    <div className="bg-white/5 rounded-lg p-4">
                        <div className="space-y-2">
                            <div className="flex justify-between text-gray-300">
                                <span>جمع کل:</span>
                                <span>{formatPrice(invoice.amount)} تومان</span>
                            </div>
                            {invoice.discount_amount > 0 && (
                                <div className="flex justify-between text-green-400">
                                    <span>تخفیف:</span>
                                    <span>- {formatPrice(invoice.discount_amount)} تومان</span>
                                </div>
                            )}
                            {invoice.delivery_fee > 0 && (
                                <div className="flex justify-between text-gray-300">
                                    <span>هزینه ارسال:</span>
                                    <span>{formatPrice(invoice.delivery_fee)} تومان</span>
                                </div>
                            )}
                            <div className="border-t border-white/10 pt-2 mt-2 flex justify-between">
                                <span className="text-white font-bold text-lg">مبلغ قابل پرداخت:</span>
                                <span className="text-amber-400 font-bold text-xl">
                                    {formatPrice(invoice.final_amount)} تومان
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Footer Note */}
                    <div className="text-center text-sm text-gray-400 pt-4 border-t border-white/10">
                        <p>از خرید شما متشکریم 🌸</p>
                        <p className="mt-1">با آرزوی روزهای خوش برای شما</p>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default AccountInvoice;

