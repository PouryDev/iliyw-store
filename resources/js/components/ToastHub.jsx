import React from 'react';

function Toast({ toast, onDone, onClose }) {
    const [mounted, setMounted] = React.useState(false);
    const duration = toast.duration || 2500;

    React.useEffect(() => {
        setMounted(true);
        const t = setTimeout(() => handleClose(), duration);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    function handleClose() {
        setMounted(false);
        setTimeout(() => onDone?.(toast.id), 180);
    }

    const typeStyles = toast.type === 'success'
        ? {
            chip: 'text-emerald-300 bg-emerald-500/10 border-emerald-400/30',
            icon: (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"/></svg>
            )
        }
        : toast.type === 'error'
        ? {
            chip: 'text-rose-300 bg-rose-500/10 border-rose-400/30',
            icon: (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/></svg>
            )
        }
        : {
            chip: 'text-sky-300 bg-sky-500/10 border-sky-400/30',
            icon: (
                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            )
        };

    return (
        <div
            className={`rounded-xl border ${typeStyles.chip} backdrop-blur px-3 py-2.5 shadow-lg ring-1 ring-white/5 w-full md:w-auto transition-all duration-200 ${mounted ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-1'}`}
            role="status"
        >
            <div className="flex items-center gap-2">
                <span className="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/5">
                    {typeStyles.icon}
                </span>
                <div className="text-sm text-white">{toast.message}</div>
                <button onClick={handleClose} className="ms-auto text-white/70 hover:text-white transition" aria-label="Close">✕</button>
            </div>
            <div className="mt-2 h-1 overflow-hidden rounded bg-white/10">
                <div className="h-full bg-gradient-to-r from-white/70 to-white/30 animate-[toastbar_var_2.5s_linear]" style={{
                    // fallback if CSS var unsupported
                    animationDuration: `${duration}ms`
                }} />
            </div>
        </div>
    );
}

function ToastHub() {
    const [toasts, setToasts] = React.useState([]);
    React.useEffect(() => {
        const handler = (e) => {
            const { message, type, duration } = e.detail || {};
            const id = Math.random().toString(36).slice(2);
            setToasts((prev) => [...prev, { id, message: message || 'عملیات انجام شد', type: type || 'success', duration: duration || 2500 }]);
        };
        window.addEventListener('toast:show', handler);
        return () => window.removeEventListener('toast:show', handler);
    }, []);

    const remove = (id) => setToasts((prev) => prev.filter((t) => t.id !== id));

    return (
        <div className="fixed z-50 bottom-3 right-3 left-3 md:left-auto space-y-2 pointer-events-none">
            {toasts.map((t) => (
                <div key={t.id} className="pointer-events-auto md:min-w-[260px]">
                    <Toast toast={t} onDone={remove} />
                </div>
            ))}
        </div>
    );
}

export default ToastHub;


