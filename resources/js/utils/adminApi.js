/**
 * Admin API Request Utility
 * Handles Sanctum authentication for admin API calls
 */

import { apiRequest } from './sanctumAuth';

/**
 * Make admin API request with proper Sanctum authentication
 * @param {string} url - Admin API endpoint (without /api/admin prefix)
 * @param {object} options - Fetch options
 * @returns {Promise<Response>} Fetch response
 */
export async function adminApiRequest(url, options = {}) {
    // Ensure URL starts with /api/admin/
    const fullUrl = url.startsWith('/api/admin/') ? url : `/api/admin${url.startsWith('/') ? url : '/' + url}`;
    
    console.log('Making admin API request to:', fullUrl);
    
    try {
        const response = await apiRequest(fullUrl, options);
        
        // Log response for debugging
        console.log('Admin API response status:', response.status);
        
        return response;
    } catch (error) {
        console.error('Admin API request failed:', error);
        throw error;
    }
}

/**
 * Test Sanctum authentication for admin
 * @returns {Promise<boolean>} True if authenticated
 */
export async function testAdminAuth() {
    try {
        const response = await adminApiRequest('/dashboard');
        
        if (response.ok) {
            console.log('Admin authentication is valid');
            return true;
        } else {
            console.error('Admin authentication test failed:', response.status);
            return false;
        }
    } catch (error) {
        console.error('Admin authentication test error:', error);
        return false;
    }
}

/**
 * Initialize admin API with authentication test
 * Call this when admin panel loads
 */
export async function initializeAdminApi() {
    console.log('Initializing admin API...');
    
    // Test authentication
    const isValid = await testAdminAuth();
    
    if (!isValid) {
        console.warn('Admin authentication failed');
        // Redirect to login or show error
        window.location.href = '/admin/login';
        return false;
    }
    
    console.log('Admin API initialized');
    return true;
}
