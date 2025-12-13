import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSeo } from '../hooks/useSeo';

function RegisterPage() {
    const navigate = useNavigate();

    useSeo({
        title: 'ثبت‌نام - فروشگاه ایلی استور',
        description: 'ایجاد حساب کاربری جدید',
        canonical: window.location.origin + '/register'
    });

    useEffect(() => {
        // Open auth modal with register tab
        window.dispatchEvent(new CustomEvent('auth:open', { detail: { tab: 'register' } }));
        
        // Listen for modal close to navigate back
        const handleAuthClose = () => {
            navigate('/');
        };
        
        window.addEventListener('auth:closed', handleAuthClose);
        return () => window.removeEventListener('auth:closed', handleAuthClose);
    }, [navigate]);

    return null;
}

export default RegisterPage;

