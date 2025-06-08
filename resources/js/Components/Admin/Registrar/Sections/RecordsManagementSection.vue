<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import StatusModal from '@/Components/Admin/Registrar/StatusModal.vue'
import StatusColumn from '@/Components/Admin/Registrar/StatusColumn.vue'

const props = defineProps({
  appointments: Array
})

const draggingOver = ref(null)
const draggedAppointment = ref(null)
const showStatusModal = ref(false)
const currentAppointment = ref(null)

const filteredAppointments = (status) => {
  return props.appointments
    .filter(a => a.status === status)
    .sort((a, b) => new Date(a.updated_at) - new Date(b.updated_at))
}

const handleDragStart = (appointment) => {
  draggedAppointment.value = appointment
}

const handleDragOver = (status) => {
  draggingOver.value = status
}

const handleDragLeave = () => {
  draggingOver.value = null
}

const handleDrop = (status) => {
  if (draggedAppointment.value && draggedAppointment.value.status !== status) {
    if (status === 'cancelled' && !draggedAppointment.value.cancel_reason) {
      currentAppointment.value = draggedAppointment.value;
      showStatusModal.value = true;
    } else {
      currentAppointment.value = draggedAppointment.value;
      updateAppointmentStatus({ status, cancelReason: null });
    }
  }
  draggingOver.value = null;
};

const openStatusModal = (appointmentId) => {
  currentAppointment.value = props.appointments.find(a => a.id === appointmentId)
  showStatusModal.value = true
}

const closeStatusModal = () => {
  showStatusModal.value = false
}

const updateAppointmentStatus = ({ status, cancelReason }) => {
  if (!currentAppointment.value) return;

  router.put(route('registrar.appointments.update-status', currentAppointment.value.id), {
    status,
    cancel_reason: cancelReason || null
  }, {
    preserveScroll: true,
    onSuccess: () => {
      closeStatusModal();
    },
    onError: (errors) => {
      const errorMessage = errors.message ||
        errors.cancel_reason?.[0] ||
        'Произошла ошибка при обновлении статуса';
      emit('error', errorMessage);
      console.error('Ошибка обновления статуса:', errors);
    }
  });
};

const emit = defineEmits([
  'registrar.appointments.update-status',
  'error'
]);
</script>

<template>
  <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">Управление записями</h1>

  <div class="flex flex-col lg:flex-row gap-4">
    <!-- Записи на рассмотрении -->
    <StatusColumn status="pending" :appointments="filteredAppointments('pending')"
      :dragging-over="draggingOver === 'pending'" @drag-over="handleDragOver('pending')" @drag-leave="handleDragLeave"
      @drop="handleDrop('pending')" @change-status="openStatusModal" @drag-start="handleDragStart" color="yellow"
      title="На рассмотрении" />

    <!-- Подтвержденные записи -->
    <StatusColumn status="confirmed" :appointments="filteredAppointments('confirmed')"
      :dragging-over="draggingOver === 'confirmed'" @drag-over="handleDragOver('confirmed')"
      @drag-leave="handleDragLeave" @drop="handleDrop('confirmed')" @change-status="openStatusModal"
      @drag-start="handleDragStart" color="green" title="Подтвержденные" />

    <!-- Отмененные записи -->
    <StatusColumn status="cancelled" :appointments="filteredAppointments('cancelled')"
      :dragging-over="draggingOver === 'cancelled'" @drag-over="handleDragOver('cancelled')"
      @drag-leave="handleDragLeave" @drop="handleDrop('cancelled')" @change-status="openStatusModal"
      @drag-start="handleDragStart" color="red" title="Отмененные" />
  </div>

  <!-- Модальное окно изменения статуса -->
  <StatusModal :show="showStatusModal" :appointment="currentAppointment" @close="closeStatusModal"
    @confirm="updateAppointmentStatus" @error="$emit('error', $event)" />
</template>