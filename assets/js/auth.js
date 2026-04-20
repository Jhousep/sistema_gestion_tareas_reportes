import api from './api';

export const login = async (email, password) => {
    const response = await api.post('/login_check', {
        email: email,
        password: password
    });

    const { token, refresh_token } = response.data;

    localStorage.setItem('token', token);
    localStorage.setItem('refresh_token', refresh_token);

    return token;
};
