import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json'
    }
});

// request interceptor
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

// response interceptor
api.interceptors.response.use(
    response => response,
    async error => {
        const originalRequest = error.config;

        if (error.response?.status === 401 && !originalRequest._retry) {
            originalRequest._retry = true;

            try {
                const refreshToken = localStorage.getItem('refresh_token');

                const res = await axios.post('/api/token/refresh', {
                    refresh_token: refreshToken
                });

                const newToken = res.data.token;

                localStorage.setItem('token', newToken);

                originalRequest.headers.Authorization = `Bearer ${newToken}`;

                return api(originalRequest);

            } catch (e) {
                localStorage.clear();
                window.location.href = '/';
            }
        }

        return Promise.reject(error);
    }
);

export default api;
