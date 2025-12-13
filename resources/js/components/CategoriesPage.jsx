import React from 'react';
import { useNavigate } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';

function CategoriesPage(){
    const [categories, setCategories] = React.useState([]);
    const [loading, setLoading] = React.useState(true);
    const [query, setQuery] = React.useState('');
    const navigate = useNavigate();

    React.useEffect(() => {
        (async () => {
            try {
                const res = await apiRequest('/api/categories');
                if (!res.ok) throw new Error('failed');
                const data = await res.json();
                setCategories(data.data || []);
            } catch (e) {
            } finally {
                setLoading(false);
            }
        })();
    }, []);

    const filtered = React.useMemo(() => {
        const q = (query || '').trim();
        if (!q) return categories;
        return categories.filter(c => (c.name || '').includes(q));
    }, [categories, query]);

    return (
        <div className="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white anim-page">
            {/* Hero / Sticky search */}
            <div className="sticky top-0 z-30 bg-black/40 backdrop-blur border-b border-white/10">
                <div className="max-w-7xl mx-auto px-4 py-3">
                    <div className="flex items-center justify-between">
                        <h1 className="text-lg font-extrabold">دسته‌بندی‌ها</h1>
                        <button onClick={() => navigate(-1)} className="text-xs text-gray-300 hover:text-white px-2 py-1 rounded-lg bg-white/5 border border-white/10">بازگشت</button>
                    </div>
                    <div className="mt-3">
                        <div className="relative">
                            <input
                                value={query}
                                onChange={(e)=>setQuery(e.target.value)}
                                placeholder="جستجوی دسته‌بندی..."
                                className="w-full bg-white/5 border border-white/10 rounded-lg py-2.5 px-3 pl-10 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-cherry-600 text-white"
                            />
                            <div className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="px-4 py-4">
                <div className="max-w-7xl mx-auto">
                    {/* Grid */}
                    {loading ? (
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3 md:gap-4 mt-2">
                            {Array.from({ length: 8 }).map((_, i) => (
                                <div key={i} className="rounded-xl border border-white/10 bg-white/5 animate-pulse h-28" />
                            ))}
                        </div>
                    ) : filtered.length === 0 ? (
                        <div className="text-center text-gray-300 py-16">دسته‌بندی‌ای یافت نشد</div>
                    ) : (
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3 md:gap-4 mt-2">
                            {filtered.map(cat => (
                                <button
                                    key={cat.id}
                                    onClick={() => navigate(`/category/${cat.id}`)}
                                    className="group rounded-xl glass-card soft-shadow border border-white/10 p-3 text-right hover:border-cherry-500/40 hover:bg-white/10 transition flex items-center gap-3"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-cherry-600/25 to-violet-600/15 flex items-center justify-center text-white text-sm ring-1 ring-white/10">
                                        {(cat.name || '?').charAt(0)}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <div className="text-sm font-semibold text-white truncate">{cat.name}</div>
                                        {cat.description && (
                                            <div className="text-[11px] text-gray-400 mt-0.5 line-clamp-2">{cat.description}</div>
                                        )}
                                    </div>
                                    <svg className="w-4 h-4 text-gray-400 group-hover:text-cherry-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7"/></svg>
                                </button>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default CategoriesPage;


