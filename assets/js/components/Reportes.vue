<template>
  <div style="max-width:1100px;margin:20px auto;">

    <!-- HEADER -->
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h2>Reportes de tareas</h2>

      <div style="display:flex;gap:10px;">
        <button @click="goDashboard">Dashboard</button>
        <button v-if="canDoIt.manageUsers" @click="goUsers">Usuarios</button>
        <button @click="logout">Cerrar sesión</button>
      </div>
    </div>

    <!-- FILTROS -->
    <div style="display:flex;flex-wrap:wrap;gap:15px;align-items:flex-end;margin-bottom:20px;">

      <div>
        <label>Fecha de creación desde</label><brS />
        <input type="date" v-model="filters.createdFrom" />
      </div>

      <div>
        <label>Fecha de creación hasta</label><br />
        <input type="date" v-model="filters.createdTo" />
      </div>

      <div>
        <label>Estado</label><br />
        <select v-model="filters.status">
          <option value="">Todos</option>
          <option value="pending">Pendiente</option>
          <option value="in_progress">En progreso</option>
          <option value="completed">Completada</option>
        </select>
      </div>

      <div>
        <label>Prioridad</label><br />
        <select v-model="filters.priority">
          <option value="">Todas</option>
          <option value="low">Baja</option>
          <option value="medium">Media</option>
          <option value="high">Alta</option>
        </select>
      </div>

      <div>
        <label>Usuario</label><br />
        <select v-model="filters.userId">
          <option value="">Todos</option>
          <option v-for="u in users" :key="u.id" :value="u.id">
            {{ u.email }}
          </option>
        </select>
      </div>

    </div>

    <!-- BOTONES -->
    <div style="display:flex;gap:10px;margin-bottom:20px;">
      <button @click="loadReport">Filtrar reporte</button>
      <button @click="downloadCsv">Exportar CSV</button>
      <button @click="downloadPdf">Exportar PDF</button>
    </div>

    <!-- LOADING -->
    <div v-if="loading">
      Cargando tareas para reporte...
    </div>

    <!-- TABLA -->
    <table v-else-if="report.length" border="1" cellpadding="5" width="100%">
      <thead>
        <tr>
          <th>Título</th>
          <th>Estado</th>
          <th>Prioridad</th>
          <th>Usuario</th>
          <th>Creado</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="r in report" :key="r.id">

          <td>{{ r.title }}</td>

          <td :style="{ color: getStatusColor(r.status) }">
            {{ formatStatus(r.status) }}
          </td>

          <td :style="{ color: getPriorityColor(r.priority) }">
            {{ formatPriority(r.priority) }}
          </td>

          <td>
            {{ r.assignedToEmail || 'No asignado' }}
          </td>

          <td>
            {{ formatDate(r.createdAt) }}
          </td>

        </tr>
      </tbody>
    </table>

    <!-- EMPTY -->
    <div v-else-if="loaded">
      No hay tareas registradas
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

import { getUsers } from '../services/userService';
import {
  getTaskReport,
  exportTasksCsv,
  exportTasksPdf
} from '../services/reportService';

import { authState } from '../authentication/authState';

const router = useRouter();

const users = ref([]);
const report = ref([]);
const loading = ref(false);
const loaded = ref(false);
const canDoIt = authState.canDoIt;
const filters = ref({
  createdFrom: '',
  createdTo: '',
  status: '',
  priority: '',
  userId: ''
});


// NAVIGATION
const goDashboard = () => {
  router.push('/dashboard');
};

const goUsers = () => {
  router.push('/users');
};

const logout = () => {
  authState.user = null;
  authState.canDoIt = {};

  localStorage.removeItem('token');
  localStorage.removeItem('user');

  router.push('/');
};


// DATA
const loadUsers = async () => {
  users.value = await getUsers();
};

const loadReport = async () => {
  loading.value = true;
  loaded.value = false;

  report.value = await getTaskReport(filters.value);

  loading.value = false;
  loaded.value = true;
};


// EXPORTS
const downloadCsv = async () => {
  const blob = await exportTasksCsv(filters.value);
  const url = window.URL.createObjectURL(new Blob([blob]));
  const link = document.createElement('a');

  link.href = url;
  link.download = 'reporte_tareas.csv';
  link.click();
};

const downloadPdf = async () => {
  const blob = await exportTasksPdf(filters.value);
  const url = window.URL.createObjectURL(new Blob([blob]));
  const link = document.createElement('a');

  link.href = url;
  link.download = 'reporte_tareas.pdf';
  link.click();
};


// HELPERS
const formatStatus = (s) => ({
  pending: 'Pendiente',
  in_progress: 'En progreso',
  completed: 'Completada'
}[s] || s);

const formatPriority = (p) => ({
  low: 'Baja',
  medium: 'Media',
  high: 'Alta'
}[p] || p);

const getPriorityColor = (p) => ({
  low: 'green',
  medium: 'orange',
  high: 'red'
}[p] || 'black');

const getStatusColor = (s) => ({
  pending: 'gray',
  in_progress: 'blue',
  completed: 'green'
}[s] || 'black');

const formatDate = (d) =>
  d ? new Date(d).toLocaleDateString() : '';


// INIT
onMounted(async () => {
  await loadUsers();
  await loadReport();
});
</script>
