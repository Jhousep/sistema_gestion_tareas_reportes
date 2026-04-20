<template>
  <div style="min-height: 100vh; display: flex; justify-content: center; align-items: center; background: #f3f4f6; padding: 20px;">

    <div style="width: 100%; max-width: 420px; background: white; padding: 32px; border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); display: flex; flex-direction: column; gap: 20px;">

      <h2 style="text-align: center; margin: 0; font-size: 22px;">
        Reset password
      </h2>

      <div style="display: flex; flex-direction: column; gap: 14px;">

        <input
          v-model="form.email"
          placeholder="Email"
          style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
        />

        <input
          v-model="form.token"
          placeholder="Token"
          style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
        />

        <input
          v-model="form.password"
          type="password"
          placeholder="Nueva contraseña"
          style="padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;"
        />

      </div>

      <div style="display: flex; gap: 12px;">

        <button
          @click="submit"
          style="flex: 1; padding: 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; background: #4a90e2; color: white;"
        >
          Cambiar contraseña
        </button>

        <button
          @click="goHome"
          style="flex: 1; padding: 12px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; background: #e5e7eb;"
        >
          Regresar
        </button>

      </div>

      <p style="text-align: center; font-size: 14px; margin: 0; color: #444;">
        {{ message }}
      </p>

    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();

const form = ref({
  email: '',
  token: '',
  password: ''
});

const message = ref('');

const submit = async () => {
  try {
    await api.post('/reset-password', form.value);
    message.value = 'Contraseña actualizada';
  } catch (e) {
    message.value = 'Error al cambiar contraseña';
  }
};

const goHome = () => {
  router.push('/');
};
</script>
