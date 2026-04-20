<template>
    <div style="max-width:1100px;margin:20px auto;">

        <!-- HEADER -->
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Mis tareas</h2>

            <div style="display:flex; gap:10px;">
                <button @click="goReports">
                    Reportes
                </button>

                <button v-if="canDoIt.manageUsers" @click="goUsers">
                    Usuarios
                </button>

                <button @click="logout">
                    Cerrar sesión
                </button>
            </div>
        </div>

        <!-- BOTÓN CREAR -->
        <button style="margin-bottom:20px;" v-if="canDoIt.createTask" @click="openCreate">
            Nueva tarea
        </button>

        <!-- FORM -->
        <TaskForm
            v-if="showForm"
            :task="selectedTask"
            :users="users"
            @save="saveTask"
            @cancel="closeForm"
        />

        <!-- FILTROS -->
        <div style="margin-bottom:20px; display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">

            <div>
                <label>Título</label><br />
                <input v-model="filters.title" placeholder="Buscar por título..." />
            </div>

            <div>
                <label>Estado</label><br />
                <select v-model="filters.status">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendiente</option>
                    <option value="in_progress">En progreso</option>
                    <option value="completed">Completada</option>
                </select>
            </div>

            <div>
                <label>Prioridad</label><br />
                <select v-model="filters.priority">
                    <option value="">Todas las prioridades</option>
                    <option value="low">Baja</option>
                    <option value="medium">Media</option>
                    <option value="high">Alta</option>
                </select>
            </div>

            <div>
                <label>Desde</label><br />
                <input type="date" v-model="filters.dueDateFrom" />
            </div>

            <div>
                <label>Hasta</label><br />
                <input type="date" v-model="filters.dueDateTo" />
            </div>

            <div>
                <button @click="loadTasks">
                    Filtrar
                </button>
            </div>

        </div>

        <!-- LOADING -->
        <div v-if="loading">
            Cargando tareas...
        </div>

        <!-- ERROR -->
        <div v-if="error" style="color:red;">
            {{ error }}
        </div>

        <!-- TABLA -->
        <table v-if="!loading && sortedTasks.length" border="1" cellpadding="5" width="100%">
            <thead>
                <tr>
                    <th @click="sortBy('title')">Título</th>
                    <th>Descripción</th>
                    <th @click="sortBy('status')">Estado</th>
                    <th @click="sortBy('priority')">Prioridad</th>
                    <th>Asignado a</th>
                    <th @click="sortBy('dueDate')">Vencimiento</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="task in sortedTasks" :key="task.id">

                    <td>{{ task.title }}</td>
                    <td>{{ task.description || 'Sin descripción' }}</td>

                    <td :style="{ color: getStatusColor(task.status) }">
                        {{ formatStatus(task.status) }}
                    </td>

                    <td :style="{ color: getPriorityColor(task.priority) }">
                        {{ formatPriority(task.priority) }}
                    </td>

                    <td>
                        {{ task.assignedToEmail || 'No asignado' }}
                    </td>

                    <td>{{ formatDate(task.dueDate) }}</td>

                    <td>
                        <button v-if="canDoIt.editTask" @click="editTask(task)">
                            Editar
                        </button>

                        <button v-if="canDoIt.deleteTask" @click="deleteTask(task.id)">
                            Eliminar
                        </button>
                    </td>

                </tr>
            </tbody>
        </table>

        <!-- EMPTY -->
        <div v-if="!loading && sortedTasks.length === 0">
            No hay tareas registradas
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

import TaskForm from './TaskForm.vue';

import {
    getTasks,
    createTask,
    updateTask,
    deleteTask as deleteTaskService
} from '../services/taskService';

import { getUsers } from '../services/userService';
import { authState } from '../authentication/authState';

// ROUTER
const router = useRouter();

// STATE
const tasks = ref([]);
const users = ref([]);

const loading = ref(true);
const error = ref(null);

const canDoIt = authState.canDoIt;

// FORM
const showForm = ref(false);
const selectedTask = ref(null);

// FILTROS
const filters = ref({
    status: '',
    priority: '',
    title: '',
    dueDateFrom: '',
    dueDateTo: ''
});

// SORT
const sortKey = ref('');
const sortOrder = ref('asc');


// NAV REPORTES
const goReports = () => {
    router.push('/reports');
};

// NAV USERS
const goUsers = () => {
    router.push('/users');
};


// LOGOUT
const logout = () => {
    authState.user = null;
    authState.canDoIt = {};

    localStorage.removeItem('token');
    localStorage.removeItem('user');

    router.push('/');
};


// LOAD
const loadTasks = async () => {
    loading.value = true;
    error.value = null;

    try {
        tasks.value = await getTasks(filters.value);
    } catch (e) {
        error.value = 'Error al cargar tareas';
    } finally {
        loading.value = false;
    }
};

const loadUsers = async () => {
    try {
        users.value = await getUsers();
    } catch (e) {
        console.error('Error cargando usuarios');
    }
};


// CRUD
const openCreate = () => {
    selectedTask.value = null;
    showForm.value = true;
};

const editTask = (task) => {
    selectedTask.value = { ...task };
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    selectedTask.value = null;
};

const saveTask = async (data) => {
    try {
        if (selectedTask.value) {
            await updateTask(selectedTask.value.id, data);
        } else {
            await createTask(data);
        }

        closeForm();
        loadTasks();
    } catch (e) {
        error.value = 'Error al guardar tarea';
    }
};

const deleteTask = async (id) => {
    if (!confirm('¿Eliminar tarea?')) return;

    await deleteTaskService(id);
    loadTasks();
};


// SORT
const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
};

const sortedTasks = computed(() => {
    if (!sortKey.value) return tasks.value;

    return [...tasks.value].sort((a, b) => {
        let aVal = a[sortKey.value];
        let bVal = b[sortKey.value];

        if (sortKey.value === 'dueDate') {
            aVal = new Date(aVal);
            bVal = new Date(bVal);
        }

        if (typeof aVal === 'string') {
            return sortOrder.value === 'asc'
                ? aVal.localeCompare(bVal)
                : bVal.localeCompare(aVal);
        }

        return sortOrder.value === 'asc'
            ? aVal - bVal
            : bVal - aVal;
    });
});


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
    await loadTasks();
    await loadUsers();
});
</script>
