import React, { useState, useEffect } from 'react';
import { useSeo } from '../hooks/useSeo';

function AccountAddresses() {
    const [addresses, setAddresses] = useState([]);
    const [showForm, setShowForm] = useState(false);
    const [editing, setEditing] = useState(null);
    const [errors, setErrors] = useState({});
    const [saving, setSaving] = useState(false);
    const user = window.__USER__;
    const [form, setForm] = useState({
        title: '',
        province: '',
        city: '',
        address: '',
        postal_code: '',
        recipient_name: user?.name || '',
        recipient_phone: user?.phone || '',
        is_default: false
    });

    useEffect(() => {
        fetchAddresses();
    }, []);

    useSeo({
        title: 'آدرس‌ها - فروشگاه جمه',
        description: 'مدیریت آدرس‌های من',
        canonical: window.location.origin + '/account/addresses'
    });

    const fetchAddresses = async () => {
        try {
            const res = await fetch('/api/account/addresses', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (res.ok) {
                const data = await res.json();
                setAddresses(data.addresses || []);
            }
        } catch (e) {
            console.error(e);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setErrors({});
        setSaving(true);
        
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const url = editing ? `/api/account/addresses/${editing.id}` : '/api/account/addresses';
            const method = editing ? 'PUT' : 'POST';
            
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(form),
                credentials: 'same-origin'
            });
            
            const data = await res.json();
            
            if (res.ok) {
                fetchAddresses();
                resetForm();
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: editing ? 'آدرس ویرایش شد' : 'آدرس افزوده شد' } 
                }));
            } else if (res.status === 422) {
                // Validation errors
                setErrors(data.errors || {});
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'error', message: 'لطفا فیلدها را بررسی کنید' } 
                }));
            } else {
                throw new Error('Failed');
            }
        } catch (e) {
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در ذخیره آدرس' } 
            }));
        } finally {
            setSaving(false);
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('آیا از حذف این آدرس اطمینان دارید؟')) return;
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch(`/api/account/addresses/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (res.ok) {
                fetchAddresses();
                window.dispatchEvent(new CustomEvent('toast:show', { 
                    detail: { type: 'success', message: 'آدرس حذف شد' } 
                }));
            }
        } catch (e) {
            window.dispatchEvent(new CustomEvent('toast:show', { 
                detail: { type: 'error', message: 'خطا در حذف آدرس' } 
            }));
        }
    };

    const resetForm = () => {
        const user = window.__USER__;
        setForm({
            title: '',
            province: '',
            city: '',
            address: '',
            postal_code: '',
            recipient_name: user?.name || '',
            recipient_phone: user?.phone || '',
            is_default: false
        });
        setErrors({});
        setShowForm(false);
        setEditing(null);
    };

    const startEdit = (addr) => {
        setForm({
            title: addr.title || '',
            province: addr.province || '',
            city: addr.city || '',
            address: addr.address || '',
            postal_code: addr.postal_code || '',
            recipient_name: addr.recipient_name || '',
            recipient_phone: addr.recipient_phone || '',
            is_default: addr.is_default || false
        });
        setEditing(addr);
        setShowForm(true);
    };

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <h2 className="text-2xl font-bold text-white">آدرس‌های من</h2>
                <button
                    onClick={() => {
                        if (!showForm) {
                            // پر کردن فیلدها با اطلاعات کاربر
                            setForm({
                                title: '',
                                province: '',
                                city: '',
                                address: '',
                                postal_code: '',
                                recipient_name: user?.name || '',
                                recipient_phone: user?.phone || '',
                                is_default: false
                            });
                            setEditing(null);
                        }
                        setShowForm(!showForm);
                    }}
                    className="px-4 py-2 rounded-lg bg-cherry-600 hover:bg-cherry-700 text-white text-sm font-semibold transition flex items-center gap-2"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4"/></svg>
                    {showForm ? 'انصراف' : 'آدرس جدید'}
                </button>
            </div>

            {/* Form */}
            {showForm && (
                <form onSubmit={handleSubmit} className="glass-card rounded-2xl p-5 border border-white/10 space-y-4">
                    <h3 className="text-lg font-bold text-white">
                        {editing ? 'ویرایش آدرس' : 'افزودن آدرس جدید'}
                    </h3>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm text-gray-400 mb-1">عنوان آدرس</label>
                            <input
                                type="text"
                                value={form.title}
                                onChange={(e) => setForm({ ...form, title: e.target.value })}
                                placeholder="مثلاً: منزل، محل کار"
                                className={`w-full bg-white/5 border ${errors.title ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                required
                            />
                            {errors.title && <p className="text-red-400 text-xs mt-1">{errors.title[0]}</p>}
                        </div>
                        <div>
                            <label className="block text-sm text-gray-400 mb-1">استان</label>
                            <input
                                type="text"
                                value={form.province}
                                onChange={(e) => setForm({ ...form, province: e.target.value })}
                                placeholder="تهران"
                                className={`w-full bg-white/5 border ${errors.province ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                required
                            />
                            {errors.province && <p className="text-red-400 text-xs mt-1">{errors.province[0]}</p>}
                        </div>
                        <div>
                            <label className="block text-sm text-gray-400 mb-1">شهر</label>
                            <input
                                type="text"
                                value={form.city}
                                onChange={(e) => setForm({ ...form, city: e.target.value })}
                                placeholder="تهران"
                                className={`w-full bg-white/5 border ${errors.city ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                required
                            />
                            {errors.city && <p className="text-red-400 text-xs mt-1">{errors.city[0]}</p>}
                        </div>
                        <div>
                            <label className="block text-sm text-gray-400 mb-1">کد پستی</label>
                            <input
                                type="text"
                                value={form.postal_code}
                                onChange={(e) => setForm({ ...form, postal_code: e.target.value })}
                                placeholder="۱۲۳۴۵۶۷۸۹۰"
                                className={`w-full bg-white/5 border ${errors.postal_code ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                required
                            />
                            {errors.postal_code && <p className="text-red-400 text-xs mt-1">{errors.postal_code[0]}</p>}
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm text-gray-400 mb-1">آدرس کامل</label>
                        <textarea
                            value={form.address}
                            onChange={(e) => setForm({ ...form, address: e.target.value })}
                            rows="3"
                            placeholder="خیابان، کوچه، پلاک، واحد"
                            className={`w-full bg-white/5 border ${errors.address ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                            required
                        />
                        {errors.address && <p className="text-red-400 text-xs mt-1">{errors.address[0]}</p>}
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm text-gray-400 mb-1">نام گیرنده</label>
                            <input
                                type="text"
                                value={form.recipient_name}
                                onChange={(e) => setForm({ ...form, recipient_name: e.target.value })}
                                className={`w-full bg-white/5 border ${errors.recipient_name ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                required
                            />
                            {errors.recipient_name && <p className="text-red-400 text-xs mt-1">{errors.recipient_name[0]}</p>}
                        </div>
                        <div>
                            <label className="block text-sm text-gray-400 mb-1">شماره تماس گیرنده</label>
                            <input
                                type="text"
                                value={form.recipient_phone}
                                onChange={(e) => setForm({ ...form, recipient_phone: e.target.value })}
                                placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                                className={`w-full bg-white/5 border ${errors.recipient_phone ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                required
                            />
                            {errors.recipient_phone && <p className="text-red-400 text-xs mt-1">{errors.recipient_phone[0]}</p>}
                        </div>
                    </div>

                    <div className="flex items-center justify-between p-3 rounded-lg bg-white/5">
                        <label htmlFor="is_default" className="text-sm text-gray-300 cursor-pointer">
                            به عنوان آدرس پیش‌فرض تنظیم شود
                        </label>
                        <label className="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                id="is_default"
                                checked={form.is_default}
                                onChange={(e) => setForm({ ...form, is_default: e.target.checked })}
                                className="sr-only peer"
                            />
                            <div className="w-11 h-6 bg-white/10 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-cherry-600 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cherry-600"></div>
                        </label>
                    </div>

                    <div className="flex gap-2 pt-2">
                        <button
                            type="submit"
                            disabled={saving}
                            className="px-6 py-2 rounded-lg bg-cherry-600 hover:bg-cherry-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold transition"
                        >
                            {saving ? 'در حال ذخیره...' : (editing ? 'ویرایش' : 'افزودن')}
                        </button>
                        <button
                            type="button"
                            onClick={resetForm}
                            disabled={saving}
                            className="px-6 py-2 rounded-lg bg-white/10 hover:bg-white/20 disabled:opacity-50 text-white font-semibold transition"
                        >
                            انصراف
                        </button>
                    </div>
                </form>
            )}

            {/* Addresses List */}
            {addresses.length === 0 && !showForm ? (
                <div className="glass-card rounded-2xl p-8 border border-white/10 text-center">
                    <div className="text-6xl mb-4">📍</div>
                    <h3 className="text-xl font-semibold text-white mb-2">آدرسی ثبت نشده</h3>
                    <p className="text-gray-400 mb-6">برای ثبت اولین آدرس خود دکمه "آدرس جدید" را بزنید</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {addresses.map((addr) => (
                        <div 
                            key={addr.id} 
                            className={`glass-card rounded-2xl p-5 border ${
                                addr.is_default ? 'border-cherry-500/50' : 'border-white/10'
                            } hover:border-cherry-500/30 transition relative`}
                        >
                            {addr.is_default && (
                                <div className="absolute top-3 left-3">
                                    <span className="px-2 py-1 rounded-full bg-cherry-600 text-white text-xs font-bold">
                                        پیش‌فرض
                                    </span>
                                </div>
                            )}
                            
                            <div className="flex items-start gap-3 mb-3">
                                <div className="w-10 h-10 rounded-full bg-cherry-600/20 flex items-center justify-center text-cherry-400 flex-shrink-0">
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div className="flex-1 min-w-0">
                                    <h4 className="text-white font-bold mb-1">{addr.title}</h4>
                                    <p className="text-sm text-gray-300 mb-2">
                                        {addr.province}، {addr.city}
                                    </p>
                                    <p className="text-sm text-gray-400 mb-2 line-clamp-2">
                                        {addr.address}
                                    </p>
                                    <p className="text-xs text-gray-500">کد پستی: {addr.postal_code}</p>
                                </div>
                            </div>

                            <div className="border-t border-white/10 pt-3 mt-3">
                                <div className="text-sm text-gray-400 mb-1">گیرنده: {addr.recipient_name}</div>
                                <div className="text-sm text-gray-400">{addr.recipient_phone}</div>
                            </div>

                            <div className="flex gap-2 mt-4">
                                <button
                                    onClick={() => startEdit(addr)}
                                    className="flex-1 px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-sm font-semibold transition"
                                >
                                    ویرایش
                                </button>
                                <button
                                    onClick={() => handleDelete(addr.id)}
                                    className="flex-1 px-3 py-2 rounded-lg bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm font-semibold transition"
                                >
                                    حذف
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

export default AccountAddresses;
