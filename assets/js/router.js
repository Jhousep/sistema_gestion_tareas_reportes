import { createRouter, createWebHistory } from "vue-router";
import Login from "./components/Login.vue";
import Dashboard from "./components/Dashboard.vue";
import Reportes from "./components/Reportes.vue";
import UsersView from "./components/UsersView.vue";
import ResetPassword from "./components/ResetPassword.vue";
import { authState } from "./authentication/authState";

// defino rutas principales de la app
const routes = [
    {
        path: "/",
        component: Login,
    },
    {
        path: "/dashboard",
        component: Dashboard,
        meta: { requiresAuth: true }, // marco esta ruta que necesita loguearse primero
    },
    {
        path: "/reports",
        component: Reportes,
        meta: { requiresAuth: true },
    },
    {
        path: "/users",
        component: UsersView,
        meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
        path: "/reset-password",
        component: ResetPassword,
    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        component: () => import("./components/NotFound.vue"), // importo el componente NotFound
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// guard global para controlar acceso según autenticación
router.beforeEach((to, from, next) => {
    // obtengo el token desde localStorage
    const token = localStorage.getItem("token");

    // si la ruta requiere autenticación y no hay token, redireccionar al login
    if (to.meta.requiresAuth && !token) {
        return next("/");
    }

    // si el usuario ya está autenticado e intenta ir al login, redireccionar al dashboard
    if (to.path === "/" && token) {
        return next("/dashboard");
    }

    // si la ruta requiere admin y el usuario no es admin, redireccionar a not found
    if (to.meta.requiresAdmin) {
        const isAdmin = authState.canDoIt?.manageUsers === true;

        if (!isAdmin) {
            return next("/");
        }
    }

    // en cualquier otro caso permito la navegación
    next();
});

export default router;
