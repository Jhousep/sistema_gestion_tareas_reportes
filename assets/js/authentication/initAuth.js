import { authState } from './authState';
import { computePermissions } from './permissions';
import { getMe } from '../services/userAuthService';

export const initAuth = async () => {
    const token = localStorage.getItem('token');

    if (!token) {
        authState.user = null;
        authState.roles = [];
        authState.canDoIt = {
            createTask: false,
            editTask: false,
            deleteTask: false,
            manageUsers: false
        };
        return null;
    }

    try {
        const user = await getMe();

        authState.user = user;
        authState.roles = user.roles ?? [];
   

        return user;

    } catch (e) {
        localStorage.removeItem('token');

        authState.user = null;
        authState.roles = [];
        authState.canDoIt = {
            createTask: false,
            editTask: false,
            deleteTask: false,
            manageUsers: false
        };

        return null;
    }
};
