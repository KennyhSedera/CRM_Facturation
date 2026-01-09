// resources/js/lib/axios.ts
import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest', // ✅ CRUCIAL pour Sanctum
    },
    withCredentials: true,
});

// Debug pour voir ce qui est envoyé
api.interceptors.request.use(
    (config) => {
        console.log('📤 Requête API:', {
            url: config.url,
            method: config.method,
            baseURL: config.baseURL,
            headers: config.headers,
            withCredentials: config.withCredentials,
            cookies: document.cookie,
            domain: window.location.hostname,
        });
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

api.interceptors.response.use(
    (response) => {
        console.log('✅ Réponse API:', {
            url: response.config.url,
            status: response.status,
            data: response.data,
        });
        return response;
    },
    (error) => {
        console.error('❌ Erreur API complète:', {
            url: error.config?.url,
            method: error.config?.method,
            status: error.response?.status,
            statusText: error.response?.statusText,
            data: error.response?.data,
            requestHeaders: error.config?.headers,
            responseHeaders: error.response?.headers,
        });

        if (error.response?.status === 401) {
            console.error('🔒 Non authentifié. Redirection vers login...');
            window.location.href = '/login';
        }

        return Promise.reject(error);
    }
);

export default api;
