import React, { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { apiRequest } from '../utils/sanctumAuth';

function HeroSlider() {
    const [slides, setSlides] = useState([]);
    const [currentIndex, setCurrentIndex] = useState(0);
    const [isPaused, setIsPaused] = useState(false);
    const [touchStart, setTouchStart] = useState(null);
    const [touchEnd, setTouchEnd] = useState(null);
    const [touchStartY, setTouchStartY] = useState(null);
    const [isAnimating, setIsAnimating] = useState(false);
    const [direction, setDirection] = useState(0); // -1 for prev, 1 for next
    const intervalRef = useRef(null);
    const navigate = useNavigate();

    useEffect(() => {
        const loadSlides = async () => {
            try {
                const res = await apiRequest('/api/hero-slides');
                if (res.ok) {
                    const data = await res.json();
                    if (data.success && data.data.length > 0) {
                        setSlides(data.data);
                    }
                }
            } catch (error) {
                console.error('Failed to load hero slides:', error);
            }
        };

        loadSlides();
    }, []);

    // Auto-play functionality
    useEffect(() => {
        if (slides.length <= 1 || isPaused) {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
            return;
        }

        intervalRef.current = setInterval(() => {
            if (!isAnimating) {
                setDirection(1);
                setIsAnimating(true);
                setCurrentIndex((prevIndex) => (prevIndex + 1) % slides.length);
                setTimeout(() => setIsAnimating(false), 1000);
            }
        }, 5000);

        return () => {
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
        };
    }, [slides.length, isPaused, isAnimating]);

    const goToSlide = (index) => {
        if (index === currentIndex || isAnimating) return;
        setDirection(index > currentIndex ? 1 : -1);
        setIsAnimating(true);
        setCurrentIndex(index);
        setTimeout(() => setIsAnimating(false), 1000);
    };

    const goToPrevious = () => {
        if (isAnimating) return;
        setDirection(-1);
        setIsAnimating(true);
        setCurrentIndex((prevIndex) => (prevIndex - 1 + slides.length) % slides.length);
        setTimeout(() => setIsAnimating(false), 1000);
    };

    const goToNext = () => {
        if (isAnimating) return;
        setDirection(1);
        setIsAnimating(true);
        setCurrentIndex((prevIndex) => (prevIndex + 1) % slides.length);
        setTimeout(() => setIsAnimating(false), 1000);
    };

    const handleSlideClick = async (slide) => {
        if (slide.link_url) {
            // Register click
            try {
                await apiRequest(`/api/hero-slides/${slide.id}/click`, {
                    method: 'POST'
                });
            } catch (error) {
                console.error('Failed to register click:', error);
            }

            // Navigate
            if (slide.link_url.startsWith('http')) {
                window.open(slide.link_url, '_blank');
            } else {
                navigate(slide.link_url);
            }
        }
    };

    // Touch handlers for swipe
    const minSwipeDistance = 50;

    const onTouchStart = (e) => {
        setTouchEnd(null);
        setTouchStart(e.targetTouches[0].clientX);
        setTouchStartY(e.targetTouches[0].clientY);
        setIsPaused(true);
    };

    const onTouchMove = (e) => {
        // Prevent default scroll behavior when swiping horizontally
        if (touchStart !== null && touchStartY !== null) {
            const currentX = e.targetTouches[0].clientX;
            const currentY = e.targetTouches[0].clientY;
            const deltaX = Math.abs(currentX - touchStart);
            const deltaY = Math.abs(currentY - touchStartY);
            
            // If horizontal movement is greater than vertical, prevent default
            if (deltaX > deltaY && deltaX > 10) {
                e.preventDefault();
            }
        }
        setTouchEnd(e.targetTouches[0].clientX);
    };

    const onTouchEnd = (e) => {
        if (!touchStart || !touchEnd) {
            setIsPaused(false);
            return;
        }

        const distance = touchStart - touchEnd;
        const isLeftSwipe = distance > minSwipeDistance;
        const isRightSwipe = distance < -minSwipeDistance;

        if (isLeftSwipe) {
            e.preventDefault();
            goToNext();
        }
        if (isRightSwipe) {
            e.preventDefault();
            goToPrevious();
        }

        setIsPaused(false);
    };

    if (slides.length === 0) {
        return null;
    }

    return (
        <>
            <style>{`
                @keyframes slideInRight {
                    from {
                        opacity: 0;
                        transform: translateX(30px) scale(1.05);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0) scale(1);
                    }
                }
                @keyframes slideInLeft {
                    from {
                        opacity: 0;
                        transform: translateX(-30px) scale(1.05);
                    }
                    to {
                        opacity: 1;
                        transform: translateX(0) scale(1);
                    }
                }
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                @keyframes fadeInUpDelayed {
                    0% {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    50% {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    100% {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                @keyframes fadeInUpMoreDelayed {
                    0% {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    70% {
                        opacity: 0;
                        transform: translateY(30px);
                    }
                    100% {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                @keyframes fadeInUpButton {
                    0% {
                        opacity: 0;
                        transform: translateY(30px) scale(0.9);
                    }
                    80% {
                        opacity: 0;
                        transform: translateY(30px) scale(0.9);
                    }
                    100% {
                        opacity: 1;
                        transform: translateY(0) scale(1);
                    }
                }
                @keyframes zoomIn {
                    from {
                        transform: scale(1.1);
                    }
                    to {
                        transform: scale(1);
                    }
                }
                .slide-active-right {
                    animation: slideInRight 1s cubic-bezier(0.4, 0, 0.2, 1) forwards;
                }
                .slide-active-left {
                    animation: slideInLeft 1s cubic-bezier(0.4, 0, 0.2, 1) forwards;
                }
                .bg-zoom {
                    animation: zoomIn 8s ease-out forwards;
                }
                .content-animate-1 {
                    animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
                }
                .content-animate-2 {
                    animation: fadeInUpDelayed 1s cubic-bezier(0.4, 0, 0.2, 1) 0.3s both;
                }
                .content-animate-3 {
                    animation: fadeInUpMoreDelayed 1.2s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
                }
                .content-animate-4 {
                    animation: fadeInUpButton 1.4s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s both;
                }
            `}</style>
            <div
                className="relative w-full h-screen md:h-[60vh] overflow-hidden"
                style={{ touchAction: 'pan-y pinch-zoom', height: '70vh' }}
                onMouseEnter={() => setIsPaused(true)}
                onMouseLeave={() => setIsPaused(false)}
                onTouchStart={onTouchStart}
                onTouchMove={onTouchMove}
                onTouchEnd={onTouchEnd}
            >
                {/* Slides */}
                <div className="relative w-full h-full">
                    {slides.map((slide, index) => {
                        const isActive = index === currentIndex;
                        
                        return (
                            <div
                                key={slide.id}
                                className={`absolute inset-0 ${
                                    isActive 
                                        ? `opacity-100 z-10 ${direction === 1 ? 'slide-active-right' : 'slide-active-left'}` 
                                        : 'opacity-0 z-0'
                                }`}
                            >
                                <div
                                    className={`relative w-full h-full bg-cover bg-center bg-no-repeat transition-transform duration-[8000ms] ease-out ${
                                        isActive ? 'bg-zoom' : ''
                                    }`}
                                    style={{
                                        backgroundImage: slide.image_url ? `url(${slide.image_url})` : 'none',
                                        backgroundColor: '#1a1a1a',
                                        transform: isActive ? 'scale(1)' : 'scale(1.1)'
                                    }}
                                >
                                    {/* Overlay gradient with smooth transition */}
                                    <div 
                                        className={`absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent transition-opacity duration-1000 ${
                                            isActive ? 'opacity-100' : 'opacity-0'
                                        }`}
                                    />

                                    {/* Content with staggered animations */}
                                    <div className="relative z-20 h-full flex flex-col justify-end items-center text-center px-4 pb-20 md:pb-12">
                                        <div key={`content-${slide.id}-${currentIndex}`} className="max-w-2xl mx-auto">
                                            {slide.title && (
                                                <h2 className={`text-3xl md:text-5xl font-bold text-white mb-4 drop-shadow-2xl ${
                                                    isActive ? 'content-animate-1' : 'opacity-0'
                                                }`}>
                                                    {slide.title}
                                                </h2>
                                            )}
                                            {slide.subtitle && (
                                                <h3 className={`text-xl md:text-2xl text-gray-200 mb-4 drop-shadow-lg ${
                                                    isActive ? 'content-animate-2' : 'opacity-0'
                                                }`}>
                                                    {slide.subtitle}
                                                </h3>
                                            )}
                                            {slide.description && (
                                                <p className={`text-base md:text-lg text-gray-300 mb-6 drop-shadow-md ${
                                                    isActive ? 'content-animate-3' : 'opacity-0'
                                                }`}>
                                                    {slide.description}
                                                </p>
                                            )}
                                            {slide.button_text && slide.link_url && (
                                                <button
                                                    onClick={() => handleSlideClick(slide)}
                                                    className={`bg-gradient-to-r from-cherry-500 to-violet-600 hover:from-cherry-600 hover:to-violet-700 text-white font-semibold py-3 px-8 rounded-full transition-all duration-300 hover:scale-110 hover:shadow-2xl shadow-lg ${
                                                        isActive ? 'content-animate-4' : 'opacity-0'
                                                    }`}
                                                >
                                                    {slide.button_text}
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {/* Navigation Arrows */}
                {slides.length > 1 && (
                    <>
                        <button
                            onClick={goToPrevious}
                            className="absolute left-4 top-1/2 -translate-y-1/2 z-30 bg-white/20 hover:bg-white/40 backdrop-blur-md text-white p-3 rounded-full transition-all duration-300 hover:scale-125 hover:shadow-xl active:scale-95 hidden md:flex items-center justify-center group"
                            aria-label="اسلاید قبلی"
                        >
                            <svg className="w-6 h-6 transition-transform duration-300 group-hover:translate-x-[-2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button
                            onClick={goToNext}
                            className="absolute right-4 top-1/2 -translate-y-1/2 z-30 bg-white/20 hover:bg-white/40 backdrop-blur-md text-white p-3 rounded-full transition-all duration-300 hover:scale-125 hover:shadow-xl active:scale-95 hidden md:flex items-center justify-center group"
                            aria-label="اسلاید بعدی"
                        >
                            <svg className="w-6 h-6 transition-transform duration-300 group-hover:translate-x-[2px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </>
                )}

                {/* Dots Navigation */}
                {slides.length > 1 && (
                    <div className="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 flex gap-2">
                        {slides.map((_, index) => (
                            <button
                                key={index}
                                onClick={() => goToSlide(index)}
                                className={`transition-all duration-500 ease-out rounded-full ${
                                    index === currentIndex
                                        ? 'w-8 h-2 bg-white shadow-lg scale-110'
                                        : 'w-2 h-2 bg-white/50 hover:bg-white/75 hover:scale-110'
                                }`}
                                aria-label={`رفتن به اسلاید ${index + 1}`}
                            />
                        ))}
                    </div>
                )}

            </div>
        </>
    );
}

export default HeroSlider;

