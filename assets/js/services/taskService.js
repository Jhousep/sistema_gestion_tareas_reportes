import api from '../api';

export const getTasks = async (filters = {}) => {
    const res = await api.get('/tasks', { params: filters });
    return res.data;
};

export const createTask = async (data) => {
    const res = await api.post('/tasks', data);
    return res.data;
};

export const updateTask = async (id, data) => {
    const res = await api.put(`/tasks/${id}`, data);
    return res.data;
};

export const deleteTask = async (id) => {
    await api.delete(`/tasks/${id}`);
};
