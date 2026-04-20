import api from '../api';

export const resetUserPassword = async (id) => {
    const { data } = await api.post(`/admin/users/${id}/reset-password`);
    return data;
};
