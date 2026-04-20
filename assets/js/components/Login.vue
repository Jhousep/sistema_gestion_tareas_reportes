<template>
    <div style="height: 100vh; display: flex; justify-content: center; align-items: center; background: #f5f6f8;">

        <div style="width: 100%; max-width: 380px; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center;">

            <h2 style="margin-bottom: 25px;">Login</h2>

            <div v-if="error" style="color: red; margin-bottom: 15px;">
                {{ error }}
            </div>

            <form @submit.prevent="handleLogin" style="display: flex; flex-direction: column; gap: 18px;">

                <div style="display: flex; flex-direction: column; text-align: left;">
                    <label style="margin-bottom: 6px;">Email</label>
                    <input
                        v-model="email"
                        type="email"
                        required
                        style="padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                    />
                </div>

                <div style="display: flex; flex-direction: column; text-align: left;">
                    <label style="margin-bottom: 6px;">Password</label>
                    <input
                        v-model="password"
                        type="password"
                        required
                        style="padding: 10px; border: 1px solid #ccc; border-radius: 6px;"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    style="padding: 10px; border: none; border-radius: 6px; cursor: pointer; background: #2d6cdf; color: white; font-weight: bold;"
                >
                    {{ loading ? 'Cargando...' : 'Ingresar' }}
                </button>

            </form>

            <div style="margin-top: 18px;">
                <button
                    type="button"
                    @click="goResetPassword"
                    style="background: transparent; border: none; color: #2d6cdf; cursor: pointer;"
                >
                    ¿Olvidaste tu contraseña?
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { login } from '../auth';

const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref(null);

const router = useRouter();

const handleLogin = async () => {
    error.value = null;
    loading.value = true;

    try {
        await login(email.value, password.value);
        router.push('/dashboard');
    } catch (e) {
        error.value = 'Credenciales inválidas';
    } finally {
        loading.value = false;
    }
};

const goResetPassword = () => {
    router.push('/reset-password');
};
</script>
