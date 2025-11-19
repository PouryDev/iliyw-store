// Toast utility for React components
export function showToast(message, type = 'success') {
    // Check if window.showToast exists (from app.js)
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
        return;
    }
    
    // Fallback: create toast manually
    const el = document.createElement('div');
    el.className = `fixed left-1/2 -translate-x-1/2 bottom-4 z-[9999] px-4 py-2 rounded-lg text-sm text-white ${
        type === 'error' ? 'bg-red-600' : 'bg-cherry-600'
    } shadow-lg anim-fade-up`;
    el.textContent = message;
    document.body.appendChild(el);
    
    // Add animation class if not exists
    if (!document.querySelector('style[data-toast-animation]')) {
        const style = document.createElement('style');
        style.setAttribute('data-toast-animation', 'true');
        style.textContent = `
            .anim-fade-up {
                animation: fadeUp 0.3s ease-out;
            }
            @keyframes fadeUp {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    setTimeout(() => {
        el.remove();
    }, 2500);
}
