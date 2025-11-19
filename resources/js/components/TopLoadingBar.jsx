import React from 'react';
import { useLocation } from 'react-router-dom';

function TopLoadingBar() {
    const location = useLocation();
    const [loading, setLoading] = React.useState(false);
    const [progress, setProgress] = React.useState(0);

    React.useEffect(() => {
        // Start loading on route change
        setLoading(true);
        setProgress(20);
        
        const timer1 = setTimeout(() => setProgress(40), 100);
        const timer2 = setTimeout(() => setProgress(70), 300);
        const timer3 = setTimeout(() => {
            setProgress(100);
            setTimeout(() => setLoading(false), 200);
        }, 600);

        return () => {
            clearTimeout(timer1);
            clearTimeout(timer2);
            clearTimeout(timer3);
        };
    }, [location.pathname]);

    // Listen to global loading events
    React.useEffect(() => {
        const handleStart = () => {
            setLoading(true);
            setProgress(30);
        };
        const handleComplete = () => {
            setProgress(100);
            setTimeout(() => setLoading(false), 200);
        };

        window.addEventListener('loading:start', handleStart);
        window.addEventListener('loading:complete', handleComplete);

        return () => {
            window.removeEventListener('loading:start', handleStart);
            window.removeEventListener('loading:complete', handleComplete);
        };
    }, []);

    if (!loading) return null;

    return (
        <div className="fixed top-0 left-0 right-0 z-[100] h-1 overflow-hidden">
            <div 
                className="h-full bg-gradient-to-r from-cherry-600 via-violet-500 to-cherry-600 transition-all duration-300 ease-out shadow-lg shadow-cherry-500/50"
                style={{ 
                    width: `${progress}%`,
                    transition: progress === 100 ? 'width 0.2s ease-in' : 'width 0.3s ease-out'
                }}
            />
        </div>
    );
}

export default TopLoadingBar;

