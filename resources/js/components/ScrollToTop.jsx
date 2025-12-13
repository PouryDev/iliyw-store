import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';

function ScrollToTop() {
    const { pathname } = useLocation();

    useEffect(() => {
        // Don't scroll to top for home page (preserve scroll position)
        if (pathname === '/') {
            return;
        }
        window.scrollTo({ top: 0, behavior: 'instant' });
    }, [pathname]);

    return null;
}

export default ScrollToTop;

