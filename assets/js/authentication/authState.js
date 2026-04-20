import { reactive } from "vue";

export const authState = reactive({
    user: null,
    canDoIt: { createTask: false, editTask: false, deleteTask: false, manageUsers: false },
});
