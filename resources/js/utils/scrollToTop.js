/**
 * Scroll to top of the page smoothly
 */
export const scrollToTop = () => {
        // Use setTimeout to ensure navigation is complete before scrolling
    setTimeout(() => {
        window.scrollTo({
            top: 0,
            left: 0,
            behavior: 'smooth'
        });
    }, 100);
};

/**
 * Scroll to top of the page instantly
 */
export const scrollToTopInstant = () => {
    // Use setTimeout to ensure navigation is complete before scrolling
    setTimeout(() => {
        window.scrollTo({
            top: 0,
            left: 0,
            behavior: 'auto'
        });
    }, 100);
};
