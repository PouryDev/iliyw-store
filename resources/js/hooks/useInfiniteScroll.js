import { useState, useEffect, useCallback, useRef } from 'react';

/**
 * Custom hook for infinite scroll pagination
 * @param {Function} fetchFunction - Function that fetches data (page, perPage, search, filters) => Promise<{data, pagination}>
 * @param {Object} options - Configuration options
 * @param {number} options.perPage - Items per page (default: 20)
 * @param {string} options.search - Search term
 * @param {Object} options.filters - Additional filters
 * @param {boolean} options.enabled - Whether to enable fetching (default: true)
 */
export function useInfiniteScroll(fetchFunction, options = {}) {
    const {
        perPage = 20,
        search = '',
        filters = {},
        enabled = true
    } = options;

    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(false);
    const [hasMore, setHasMore] = useState(true);
    const [currentPage, setCurrentPage] = useState(1);
    const [error, setError] = useState(null);
    const [total, setTotal] = useState(0);
    
    const observerTarget = useRef(null);
    const isInitialLoad = useRef(true);

    // Reset when search or filters change
    useEffect(() => {
        setItems([]);
        setCurrentPage(1);
        setHasMore(true);
        setError(null);
        isInitialLoad.current = true;
    }, [search, JSON.stringify(filters)]);

    // Fetch data
    const fetchData = useCallback(async (page, reset = false) => {
        if (!enabled || loading) return;

        try {
            setLoading(true);
            setError(null);

            const result = await fetchFunction(page, perPage, search, filters);
            
            if (result && result.data) {
                if (reset || page === 1) {
                    setItems(result.data);
                } else {
                    setItems(prev => [...prev, ...result.data]);
                }

                if (result.pagination) {
                    setHasMore(page < result.pagination.last_page);
                    setTotal(result.pagination.total);
                } else {
                    setHasMore(result.data.length === perPage);
                }
            } else {
                setError('خطا در بارگذاری داده‌ها');
            }
        } catch (err) {
            console.error('Error fetching data:', err);
            setError('خطا در بارگذاری داده‌ها');
        } finally {
            setLoading(false);
            isInitialLoad.current = false;
        }
    }, [fetchFunction, perPage, search, filters, enabled, loading]);

    // Initial load
    useEffect(() => {
        if (enabled && isInitialLoad.current) {
            fetchData(1, true);
        }
    }, [enabled, fetchData]);

    // Intersection Observer for infinite scroll
    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting && hasMore && !loading && !isInitialLoad.current) {
                    const nextPage = currentPage + 1;
                    setCurrentPage(nextPage);
                    fetchData(nextPage, false);
                }
            },
            { threshold: 0.1 }
        );

        const currentTarget = observerTarget.current;
        if (currentTarget) {
            observer.observe(currentTarget);
        }

        return () => {
            if (currentTarget) {
                observer.unobserve(currentTarget);
            }
        };
    }, [hasMore, loading, currentPage, fetchData]);

    // Refresh function
    const refresh = useCallback(() => {
        setItems([]);
        setCurrentPage(1);
        setHasMore(true);
        isInitialLoad.current = true;
        fetchData(1, true);
    }, [fetchData]);

    return {
        items,
        loading,
        hasMore,
        error,
        total,
        refresh,
        observerTarget
    };
}

