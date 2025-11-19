import React, { useState, useEffect } from 'react';
import { useSeo } from '../hooks/useSeo';

function AccountProfile() {
    const [user, setUser] = useState(null);
    const [editing, setEditing] = useState(false);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState({});
    const [form, setForm] = useState({
        name: '',
        phone: '',
        instagram_id: '',
        address: '',
    });

    useEffect(() => {
        const currentUser = window.__USER__;
        if (currentUser) {
            setUser(currentUser);
            setForm({
                name: currentUser.name || '',
                phone: currentUser.phone || '',
                instagram_id: currentUser.instagram_id || '',
                address: currentUser.address || '',
            });
        }
    }, []);

    useSeo({
        title: 'پروفایل - فروشگاه جمه',
        description: 'مدیریت اطلاعات حساب کاربری',
        canonical: window.location.origin + '/account'
    });

    const handleSave = async () => {
        setSaving(true);
        setErrors({});
        
        try {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch('/account/profile', {
                method: 'POST',
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
                if (data.success) {
                    setUser(data.user);
                    window.__USER__ = data.user;
                    window.dispatchEvent(new CustomEvent('user:updated', { detail: data.user }));
                    setEditing(false);
                    window.dispatchEvent(new CustomEvent('toast:show', { detail: { type: 'success', message: 'اطلاعات با موفقیت ذخیره شد' } }));
                }
            } else if (res.status === 422) {
                // Validation errors
                setErrors(data.errors || {});
                window.dispatchEvent(new CustomEvent('toast:show', { detail: { type: 'error', message: 'لطفا فیلدها را بررسی کنید' } }));
            } else {
                throw new Error('failed');
            }
        } catch (e) {
            window.dispatchEvent(new CustomEvent('toast:show', { detail: { type: 'error', message: 'خطا در ذخیره اطلاعات' } }));
        } finally {
            setSaving(false);
        }
    };

    if (!user) {
        return (
            <div className="text-center py-12">
                <div className="text-gray-400">در حال بارگذاری...</div>
            </div>
        );
    }

    return (
        <div className="space-y-6">
            {/* Profile Header */}
            <div className="glass-card rounded-2xl p-6 border border-white/10">
                <div className="flex items-center gap-4 mb-6">
                    <div className="w-20 h-20 rounded-full bg-gradient-to-br from-cherry-600 to-violet-600 flex items-center justify-center text-white text-3xl font-bold">
                        {user.name ? user.name.charAt(0).toUpperCase() : 'U'}
                    </div>
                    <div>
                        <h2 className="text-2xl font-bold text-white">{user.name}</h2>
                        <p className="text-gray-400 text-sm">{user.phone}</p>
                    </div>
                </div>

                {/* Action Button */}
                <div className="flex gap-2">
                    {!editing ? (
                        <button
                            onClick={() => setEditing(true)}
                            className="px-4 py-2 rounded-lg bg-cherry-600 hover:bg-cherry-700 text-white text-sm font-semibold transition"
                        >
                            ویرایش اطلاعات
                        </button>
                    ) : (
                        <>
                            <button
                                onClick={handleSave}
                                disabled={saving}
                                className="px-4 py-2 rounded-lg bg-cherry-600 hover:bg-cherry-700 disabled:opacity-50 text-white text-sm font-semibold transition"
                            >
                                {saving ? 'در حال ذخیره...' : 'ذخیره'}
                            </button>
                            <button
                                onClick={() => {
                                    setEditing(false);
                                    setErrors({});
                                    setForm({
                                        name: user.name || '',
                                        phone: user.phone || '',
                                        instagram_id: user.instagram_id || '',
                                        address: user.address || '',
                                    });
                                }}
                                className="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-sm font-semibold transition"
                            >
                                انصراف
                            </button>
                        </>
                    )}
                </div>
            </div>

            {/* Profile Info */}
            <div className="glass-card rounded-2xl p-6 border border-white/10">
                <h3 className="text-lg font-bold text-white mb-4">اطلاعات حساب کاربری</h3>
                <div className="space-y-4">
                    <div>
                        <label className="block text-sm text-gray-400 mb-1">نام</label>
                        {editing ? (
                            <>
                                <input
                                    type="text"
                                    value={form.name}
                                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                                    className={`w-full bg-white/5 border ${errors.name ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                />
                                {errors.name && <p className="text-red-400 text-xs mt-1">{errors.name[0]}</p>}
                            </>
                        ) : (
                            <div className="text-white">{user.name}</div>
                        )}
                    </div>
                    <div>
                        <label className="block text-sm text-gray-400 mb-1">شماره تلفن</label>
                        {editing ? (
                            <>
                                <input
                                    type="text"
                                    value={form.phone}
                                    onChange={(e) => setForm({ ...form, phone: e.target.value })}
                                    className={`w-full bg-white/5 border ${errors.phone ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                />
                                {errors.phone && <p className="text-red-400 text-xs mt-1">{errors.phone[0]}</p>}
                            </>
                        ) : (
                            <div className="text-white">{user.phone}</div>
                        )}
                    </div>
                    <div>
                        <label className="block text-sm text-gray-400 mb-1">آیدی اینستاگرام</label>
                        {editing ? (
                            <>
                                <input
                                    type="text"
                                    value={form.instagram_id}
                                    onChange={(e) => setForm({ ...form, instagram_id: e.target.value })}
                                    className={`w-full bg-white/5 border ${errors.instagram_id ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                    placeholder="@username"
                                />
                                {errors.instagram_id && <p className="text-red-400 text-xs mt-1">{errors.instagram_id[0]}</p>}
                            </>
                        ) : (
                            <div className="text-white">{user.instagram_id || '-'}</div>
                        )}
                    </div>
                    <div>
                        <label className="block text-sm text-gray-400 mb-1">آدرس</label>
                        {editing ? (
                            <>
                                <textarea
                                    value={form.address}
                                    onChange={(e) => setForm({ ...form, address: e.target.value })}
                                    rows="3"
                                    className={`w-full bg-white/5 border ${errors.address ? 'border-red-500' : 'border-white/10'} rounded-lg py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-cherry-600`}
                                    placeholder="آدرس پستی خود را وارد کنید"
                                />
                                {errors.address && <p className="text-red-400 text-xs mt-1">{errors.address[0]}</p>}
                            </>
                        ) : (
                            <div className="text-white">{user.address || 'آدرسی ثبت نشده'}</div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default AccountProfile;

