import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set up Sanctum token handling for axios
const getAuthToken = () => {
    return localStorage.getItem('sanctum_auth_token');
};

// Add axios interceptor to handle authentication
window.axios.interceptors.request.use(
    (config) => {
        const token = getAuthToken();
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add axios interceptor to handle authentication errors
window.axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response && error.response.status === 401) {
            // Clear token if unauthorized
            localStorage.removeItem('sanctum_auth_token');
            // Redirect to login or show error
            window.dispatchEvent(new CustomEvent('auth:logout'));
        }
        return Promise.reject(error);
    }
);
