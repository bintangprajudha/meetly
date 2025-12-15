import axios, { AxiosError, AxiosResponse, InternalAxiosRequestConfig } from 'axios';

// Setup axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Types
interface QueueItem {
    resolve: (value?: unknown) => void;
    reject: (reason?: unknown) => void;
}

interface CsrfTokenResponse {
    csrf_token: string;
}

// Track retry to prevent infinite loops
let isRefreshing = false;
let failedQueue: QueueItem[] = [];

const processQueue = (error: AxiosError | null = null): void => {
    failedQueue.forEach(prom => {
        if (error) {
            prom.reject(error);
        } else {
            prom.resolve();
        }
    });
    failedQueue = [];
};

// Function untuk get CSRF token dari meta tag
function getCsrfToken(): string | null {
    const metaTag = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
    return metaTag?.content || null;
}

// Function untuk set CSRF token ke axios headers
function setCsrfToken(): boolean {
    const token = getCsrfToken();
    
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        console.log('✅ CSRF Token set:', token.substring(0, 10) + '...');
        return true;
    }
    
    console.error('❌ CSRF token NOT FOUND in meta tag');
    return false;
}

// Function untuk refresh CSRF token dari server
async function refreshCsrfToken(): Promise<boolean> {
    try {
        console.log('🔄 Fetching fresh CSRF token from server...');
        const response: AxiosResponse<CsrfTokenResponse> = await axios.get('/csrf-token');
        
        if (response.data?.csrf_token) {
            // Update meta tag
            const metaTag = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
            if (metaTag) {
                metaTag.content = response.data.csrf_token;
                console.log('✅ Meta tag updated with fresh token');
            }
            
            // Set to axios
            axios.defaults.headers.common['X-CSRF-TOKEN'] = response.data.csrf_token;
            console.log('✅ Fresh CSRF token set:', response.data.csrf_token.substring(0, 10) + '...');
            return true;
        }
        
        return false;
    } catch (error) {
        console.error('❌ Failed to refresh CSRF token:', error);
        return false;
    }
}

// Set token saat DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setCsrfToken);
} else {
    setCsrfToken();
}

// Re-set token setelah Inertia navigation
document.addEventListener('inertia:navigate', () => {
    console.log('🔄 Inertia navigate...');
    setTimeout(setCsrfToken, 50);
});

document.addEventListener('inertia:finish', () => {
    console.log('✅ Inertia finish, refreshing CSRF token...');
    setTimeout(setCsrfToken, 50);
});

// Axios interceptor untuk handle 419 errors
axios.interceptors.response.use(
    (response: AxiosResponse) => response,
    async (error: AxiosError) => {
        const originalRequest = error.config as InternalAxiosRequestConfig & { _retry?: boolean };

        // Handle 419 CSRF token mismatch
        if (error.response?.status === 419 && !originalRequest._retry) {
            if (isRefreshing) {
                // If already refreshing, queue this request
                return new Promise((resolve, reject) => {
                    failedQueue.push({ resolve, reject });
                })
                    .then(() => axios(originalRequest))
                    .catch((err: AxiosError) => Promise.reject(err));
            }

            originalRequest._retry = true;
            isRefreshing = true;

            console.error('❌ CSRF Token Mismatch (419)');
            console.log('🔄 Attempting to refresh token...');

            try {
                // Try to refresh token from server
                const refreshed = await refreshCsrfToken();
                
                if (refreshed) {
                    console.log('✅ Token refreshed successfully, retrying requests...');
                    
                    // Update original request header
                    const token = getCsrfToken();
                    if (token && originalRequest.headers) {
                        originalRequest.headers['X-CSRF-TOKEN'] = token;
                    }
                    
                    processQueue();
                    isRefreshing = false;
                    
                    // Retry original request
                    return axios(originalRequest);
                } else {
                    throw new Error('Failed to refresh token');
                }
            } catch (refreshError) {
                console.error('❌ Token refresh failed');
                processQueue(error);
                isRefreshing = false;
                
                // Show user-friendly error
                alert('Your session has expired. The page will reload.');
                window.location.reload();
                
                return Promise.reject(refreshError);
            }
        }

        return Promise.reject(error);
    }
);

// Expose axios globally
window.axios = axios;

export { axios, setCsrfToken };