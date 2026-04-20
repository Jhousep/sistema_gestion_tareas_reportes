<template>
  <div style="border:1px solid #ccc;padding:20px;margin-bottom:20px;">

    <h3>{{ isEdit ? 'Editar tarea' : 'Nueva tarea' }}</h3>

    <form @submit.prevent="handleSubmit">

      <div>
        <label>Título</label>
        <input v-model="form.title" required />
      </div>

      <div>
        <label>Descripción</label>
        <textarea v-model="form.description" required></textarea>
      </div>

      <div>
        <label>Estado</label>
        <select v-model="form.status">
          <option value="pending">Pendiente</option>
          <option value="in_progress">En progreso</option>
          <option value="completed">Completada</option>
        </select>
      </div>

      <div>
        <label>Prioridad</label>
        <select v-model="form.priority">
          <option value="low">Baja</option>
          <option value="medium">Media</option>
          <option value="high">Alta</option>
        </select>
      </div>

      <div>
        <label>Fecha vencimiento</label>
        <input type="date" v-model="form.dueDate" required />
      </div>

      <!-- NUEVO CAMPO -->
      <div>
        <label>Asignado a</label>
        <select v-model="form.assignedTo" required>

          <option
            v-for="user in users"
            :key="user.id"
            :value="user.id"
          >
            {{ user.email }}
          </option>
        </select>
      </div>

      <div style="margin-top:10px;">
        <button type="submit">
          {{ isEdit ? 'Actualizar' : 'Crear' }}
        </button>

        <button type="button" @click="$emit('cancel')">
          Cancelar
        </button>
      </div>

    </form>
  </div>
</template>

<script setup>
import { reactive, watch, computed } from 'vue';

const props = defineProps({
  task: Object,
  users: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['save', 'cancel']);

const form = reactive({
  title: '',
  description: '',
  status: 'pending',
  priority: 'medium',
  dueDate: '',
  assignedTo: null
});

const isEdit = computed(() => !!props.task);

// sincroniza task -> form
watch(
  () => props.task,
  (t) => {
    if (!t) {
      Object.assign(form, {
        title: '',
        description: '',
        status: 'pending',
        priority: 'medium',
        dueDate: '',
        assignedTo: null
      });
      return;
    }

    Object.assign(form, {
      title: t.title ?? '',
      description: t.description ?? '',
      status: t.status ?? 'pending',
      priority: t.priority ?? 'medium',
      dueDate: t.dueDate ?? '',
      assignedTo: t.assignedToId ?? null
    });
  },
  { immediate: true }
);

const handleSubmit = () => {
  emit('save', { ...form });
};
</script>
