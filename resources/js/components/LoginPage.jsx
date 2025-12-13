import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSeo } from '../hooks/useSeo';

function LoginPage() {
    const navigate = useNavigate();

    useSeo({
        title: 'ورود - فروشگاه ایلی استور',
        description: 'ورود به حساب کاربری',
        canonical: window.location.origin + '/login'
    });

    useEffect(() => {
        // Open auth modal with login tab
        window.dispatchEvent(new CustomEvent('auth:open', { detail: { tab: 'login' } }));
        
        // Listen for modal close to navigate back
        const handleAuthClose = () => {
            navigate('/');
        };
        
        window.addEventListener('auth:closed', handleAuthClose);
        return () => window.removeEventListener('auth:closed', handleAuthClose);
    }, [navigate]);

    return null;
}

export default LoginPage;

