// src/services/reportService.js
import api from '../api';

export const getTaskReport = (filters) => {
    return api.get('/reports/tasks', { params: filters })
        .then(res => res.data);
};

export const exportTasksCsv = (filters) => {
    return api.get('/reports/tasks/export/csv', {
        params: filters,
        responseType: 'blob'
    }).then(res => res.data);
};

export const exportTasksPdf = (filters) => {
    return api.get('/reports/tasks/export/pdf', {
        params: filters,
        responseType: 'blob'
    }).then(res => res.data);
};
