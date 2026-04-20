import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import { initAuth } from './authentication/initAuth';

const bootstrap = async () => {
    await initAuth();

    createApp(App)
        .use(router)
        .mount('#app');
};

bootstrap();
