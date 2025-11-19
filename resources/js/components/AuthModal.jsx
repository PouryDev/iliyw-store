import React from 'react';
import { useAuth } from '../contexts/AuthContext';
import { convertPersianToEnglish } from '../utils/convertPersianNumbers';
import { apiRequest } from '../utils/sanctumAuth';

function AuthModal({ open, onClose, onSuccess, initialTab = 'login' }) {
    const { login } = useAuth();
    const [tab, setTab] = React.useState(initialTab);
    const [loading, setLoading] = React.useState(false);
    const [error, setError] = React.useState(null);
    const [errors, setErrors] = React.useState({});
    const [loginForm, setLoginForm] = React.useState({ login_field: '', password: '' });
    const [registerForm, setRegisterForm] = React.useState({ name: '', instagram_id: '', phone: '', password: '', password_confirmation: '' });

    React.useEffect(() => {
        if (open) {
            setTab(initialTab);
        }
    }, [open, initialTab]);

    React.useEffect(() => {
        if (!open) {
            setError(null);
            setErrors({});
            setLoading(false);
        }
    }, [open]);

    async function handleLogin(e) {
        e.preventDefault();
        setLoading(true); 
        setError(null);
        setErrors({});
        
        try {
            const res = await apiRequest('/api/auth/login', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    login_field: convertPersianToEnglish(loginForm.login_field),
                    password: loginForm.password
                }),
            });
            const data = await res.json();
            
            if (res.status === 422) {
                setErrors(data.errors || {});
                setError('لطفا فیلدها را بررسی کنید');
            } else if (!res.ok || !data?.success) {
                setError(data?.message || 'ورود ناموفق بود. لطفا اطلاعات را بررسی کنید.');
            } else {
                // Token is already stored by apiRequest, just update user state
                await login(data.user);
                onSuccess?.(data.user);
                onClose?.();
            }
        } catch (e) {
            setError('ورود ناموفق بود. لطفا اطلاعات را بررسی کنید.');
        } finally {
            setLoading(false);
        }
    }

    async function handleRegister(e) {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setErrors({});
        
        try {
            const instagramId = registerForm.instagram_id?.trim() ?? '';
            const res = await apiRequest('/api/auth/register', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    name: registerForm.name,
                    instagram_id: instagramId.length ? instagramId : null,
                    phone: convertPersianToEnglish(registerForm.phone),
                    password: registerForm.password,
                    password_confirmation: registerForm.password_confirmation
                }),
            });
            const data = await res.json();
            
            if (res.status === 422) {
                setErrors(data.errors || {});
                setError('لطفا فیلدها را بررسی کنید');
            } else if (!res.ok || !data?.success) {
                setError(data?.message || 'ثبت‌نام ناموفق بود.');
            } else {
                // Token is already stored by apiRequest, just update user state
                await login(data.user);
                onSuccess?.(data.user);
                onClose?.();
            }
        } catch (e) {
            setError('ثبت‌نام ناموفق بود.');
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className={`fixed inset-0 z-50 ${open ? '' : 'hidden'}`} aria-hidden={!open}>
            <div className="absolute inset-0 bg-black/60 backdrop-blur" onClick={onClose} />
            <div className="absolute inset-x-0 bottom-0 md:inset-0 md:flex md:items-center md:justify-center">
                <div className="bg-white/5 border border-white/10 rounded-t-2xl md:rounded-2xl max-w-md w-full mx-auto shadow-xl animate-slide-up">
                    <div className="px-4 pt-4 pb-3 md:p-4 flex items-center justify-between">
                        <div className="text-white font-bold">ورود / ثبت‌نام</div>
                        <button onClick={onClose} className="text-gray-300 hover:text-white">✕</button>
                    </div>
                    <div className="px-4 md:px-5">
                        <div className="flex bg-white/5 rounded-lg p-1 text-sm mb-4">
                            <button 
                                onClick={() => { setTab('login'); setError(null); setErrors({}); }} 
                                className={`flex-1 py-2 rounded-md ${tab==='login'?'bg-white/10 text-white':'text-gray-300'}`}
                            >
                                ورود
                            </button>
                            <button 
                                onClick={() => { setTab('register'); setError(null); setErrors({}); }} 
                                className={`flex-1 py-2 rounded-md ${tab==='register'?'bg-white/10 text-white':'text-gray-300'}`}
                            >
                                ثبت‌نام
                            </button>
                        </div>

                        {error && (
                            <div className="mb-3 rounded border border-rose-500/40 bg-rose-500/10 text-rose-300 text-sm px-3 py-2">{error}</div>
                        )}

                        {tab === 'login' ? (
                            <form onSubmit={handleLogin} className="space-y-3">
                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">آیدی اینستاگرام یا شماره تلفن</label>
                                    <input type="text" value={loginForm.login_field} onChange={(e)=>setLoginForm({...loginForm, login_field:convertPersianToEnglish(e.target.value)})} placeholder="@username یا 09123456789" required className={`w-full bg-white/5 border ${errors.login_field ? 'border-red-500' : 'border-white/10'} rounded-lg px-3 py-2 text-white`} />
                                    {errors.login_field && <p className="text-red-400 text-xs mt-1">{errors.login_field[0]}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">رمز عبور</label>
                                    <input type="password" value={loginForm.password} onChange={(e)=>setLoginForm({...loginForm, password:e.target.value})} required className={`w-full bg-white/5 border ${errors.password ? 'border-red-500' : 'border-white/10'} rounded-lg px-3 py-2 text-white`} />
                                    {errors.password && <p className="text-red-400 text-xs mt-1">{errors.password[0]}</p>}
                                </div>
                                <button type="submit" disabled={loading} className="w-full bg-cherry-600 hover:bg-cherry-500 disabled:opacity-60 text-white rounded-lg px-4 py-2.5">{loading?'در حال ورود...':'ورود'}</button>
                            </form>
                        ) : (
                            <form onSubmit={handleRegister} className="space-y-3">
                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">نام و نام خانوادگی</label>
                                    <input value={registerForm.name} onChange={(e)=>setRegisterForm({...registerForm, name:e.target.value})} required className={`w-full bg-white/5 border ${errors.name ? 'border-red-500' : 'border-white/10'} rounded-lg px-3 py-2 text-white`} />
                                    {errors.name && <p className="text-red-400 text-xs mt-1">{errors.name[0]}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">آیدی اینستاگرام</label>
                                    <input
                                        type="text"
                                        value={registerForm.instagram_id}
                                        onChange={(e)=>setRegisterForm({...registerForm, instagram_id:e.target.value})}
                                        placeholder="@username"
                                        className={`w-full bg-white/5 border ${errors.instagram_id ? 'border-red-500' : 'border-white/10'} rounded-lg px-3 py-2 text-white`}
                                    />
                                    {errors.instagram_id && <p className="text-red-400 text-xs mt-1">{errors.instagram_id[0]}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm text-gray-300 mb-1">شماره تماس</label>
                                    <input value={registerForm.phone} onChange={(e)=>setRegisterForm({...registerForm, phone:convertPersianToEnglish(e.target.value)})} placeholder="09123456789" required className={`w-full bg-white/5 border ${errors.phone ? 'border-red-500' : 'border-white/10'} rounded-lg px-3 py-2 text-white`} />
                                    {errors.phone && <p className="text-red-400 text-xs mt-1">{errors.phone[0]}</p>}
                                </div>
                                <div className="grid grid-cols-2 gap-2">
                                    <div>
                                        <label className="block text-sm text-gray-300 mb-1">رمز عبور</label>
                                        <input type="password" value={registerForm.password} onChange={(e)=>setRegisterForm({...registerForm, password:e.target.value})} required className={`w-full bg-white/5 border ${errors.password ? 'border-red-500' : 'border-white/10'} rounded-lg px-3 py-2 text-white`} />
                                        {errors.password && <p className="text-red-400 text-xs mt-1">{errors.password[0]}</p>}
                                    </div>
                                    <div>
                                        <label className="block text-sm text-gray-300 mb-1">تکرار رمز</label>
                                        <input type="password" value={registerForm.password_confirmation} onChange={(e)=>setRegisterForm({...registerForm, password_confirmation:e.target.value})} required className={`w-full bg-white/5 border ${errors.password_confirmation ? 'border-red-500' : 'border-white/10'} rounded-lg px-3 py-2 text-white`} />
                                    </div>
                                </div>
                                <button type="submit" disabled={loading} className="w-full bg-cherry-600 hover:bg-cherry-500 disabled:opacity-60 text-white rounded-lg px-4 py-2.5">{loading?'در حال ثبت‌نام...':'ثبت‌نام'}</button>
                            </form>
                        )}

                        <div className="h-4" />
                    </div>
                </div>
            </div>
        </div>
    );
}

export default AuthModal;

