/**
 * Sanctum Token Management for React App
 */

let authToken = null;

/**
 * Get current auth token
 * @returns {string|null} Current auth token
 */
export function getAuthToken() {
    if (!authToken) {
        authToken = localStorage.getItem('sanctum_auth_token');
        console.log('Loading token from localStorage:', authToken);
    }
    return authToken;
}

/**
 * Set auth token
 * @param {string} token - Auth token
 */
export function setAuthToken(token) {
    authToken = token;
    if (token) {
        localStorage.setItem('sanctum_auth_token', token);
        console.log('Token stored in localStorage:', token);
    } else {
        localStorage.removeItem('sanctum_auth_token');
        console.log('Token removed from localStorage');
    }
}

/**
 * Clear auth token
 */
export function clearAuthToken() {
    authToken = null;
    localStorage.removeItem('sanctum_auth_token');
    console.log('Token cleared from localStorage');
}

/**
 * Debug function to check token status
 */
export function debugTokenStatus() {
    const storedToken = localStorage.getItem('sanctum_auth_token');
    console.log('=== Token Debug Info ===');
    console.log('In-memory token:', authToken);
    console.log('localStorage token:', storedToken);
    console.log('Tokens match:', authToken === storedToken);
    console.log('=======================');
    return { inMemory: authToken, localStorage: storedToken, match: authToken === storedToken };
}

/**
 * Test function to manually set a token (for debugging)
 */
export function setTestToken(token) {
    console.log('Setting test token:', token);
    setAuthToken(token);
    debugTokenStatus();
}

/**
 * Make API request with Sanctum token
 * @param {string} url - API endpoint
 * @param {object} options - Fetch options
 * @returns {Promise<Response>} Fetch response
 */
export async function apiRequest(url, options = {}) {
    const token = getAuthToken();
    console.log('Making API request to:', url, 'with token:', token ? 'Present' : 'Missing');
    console.log('Token value:', token);
    console.log('localStorage token:', localStorage.getItem('sanctum_auth_token'));
    
    const requestOptions = {
        ...options,
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
            ...options.headers,
        },
        credentials: 'same-origin',
    };

    // Log headers for debugging
    console.log('Request headers:', requestOptions.headers);

    // Handle FormData
    if (options.body instanceof FormData) {
        delete requestOptions.headers['Content-Type'];
    }

    try {
        const response = await fetch(url, requestOptions);
        
        // If unauthorized (401), clear token and redirect to login
        if (response.status === 401) {
            console.log('Unauthorized request, clearing token');
            clearAuthToken();
            window.dispatchEvent(new CustomEvent('auth:logout'));
        }
        
        // If this is a login/register request and successful, store the token
        if ((url.includes('/api/auth/login') || url.includes('/api/auth/register')) && response.ok) {
            try {
                const data = await response.clone().json();
                console.log('Login/register response data:', data);
                if (data.success && data.token) {
                    setAuthToken(data.token);
                    console.log('Token stored from login/register response:', data.token);
                    console.log('Token verification - localStorage:', localStorage.getItem('sanctum_auth_token'));
                } else {
                    console.log('No token found in response:', data);
                }
            } catch (e) {
                console.error('Failed to parse response for token storage:', e);
            }
        }
        
        return response;
    } catch (error) {
        console.error('API request failed:', error);
        throw error;
    }
}

/**
 * Login user and store token
 * @param {string} loginField - Instagram ID or phone
 * @param {string} password - Password
 * @returns {Promise<object>} Login response
 */
export async function login(loginField, password) {
    const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            login_field: loginField,
            password: password
        })
    });

    const data = await response.json();
    
    if (data.success && data.token) {
        setAuthToken(data.token);
        console.log('Login successful, token stored');
    }
    
    return data;
}

/**
 * Logout user and clear token
 * @returns {Promise<object>} Logout response
 */
export async function logout() {
    const token = getAuthToken();
    
    if (token) {
        try {
            await fetch('/api/auth/logout', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
        } catch (error) {
            console.error('Logout request failed:', error);
        }
    }
    
    clearAuthToken();
    console.log('Logout successful, token cleared');
    
    return { success: true };
}

/**
 * Get current user info
 * @returns {Promise<object>} User info
 */
export async function getCurrentUser() {
    const token = getAuthToken();
    
    if (!token) {
        return { success: false, message: 'No token available' };
    }
    
    const response = await fetch('/api/auth/user', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    });
    
    const data = await response.json();
    
    if (!data.success && response.status === 401) {
        clearAuthToken();
        window.dispatchEvent(new CustomEvent('auth:logout'));
    }
    
    return data;
}
