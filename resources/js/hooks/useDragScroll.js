import { useCallback } from 'react';

// Native-only: keep API stable but do not attach any custom listeners.
// Components can keep using: const ref = useDragScroll(); <div ref={ref} ... />
export default function useDragScroll() {
    // No-op callback ref to preserve existing usage without JS interception
    return useCallback(() => {}, []);
}
