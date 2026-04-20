<template>
  <div style="max-width:1100px;margin:20px auto;">

    <h2>Usuarios</h2>

    <button @click="goDashboard">Dashboard</button>

    <table border="1" cellpadding="5" width="100%">
      <thead>
        <tr>
          <th>Email</th>
          <th>Acción</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="u in users" :key="u.id">

          <td>{{ u.email }}</td>
          <td>
            <button @click="generateReset(u.id)">
              Reset password
            </button>
          </td>

        </tr>
      </tbody>
    </table>

    <div v-if="token" style="margin-top:20px;">
      <h3>Token generado</h3>
      <p>{{ token }}</p>

      <button @click="copyToken">
        Copiar
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';
import { getUsers } from '../services/userService';

const router = useRouter();

const users = ref([]);
const token = ref('');

const load = async () => {
  users.value = await getUsers();
};

const generateReset = async (id) => {
  const res = await api.post(`/admin/users/${id}/reset-password`);
  token.value = res.data.token;
};

const copyToken = () => {
  navigator.clipboard.writeText(token.value);
};

const goDashboard = () => {
  router.push('/dashboard');
};

onMounted(load);
</script>
