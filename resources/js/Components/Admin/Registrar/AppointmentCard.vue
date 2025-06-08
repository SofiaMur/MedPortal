<script setup>
import { computed } from 'vue'

const props = defineProps({
  appointment: {
    type: Object,
    required: true
  }
})

defineEmits(['registrar.appointments.update-status', 'dragstart'])

const statusTranslation = computed(() => {
  const statusMap = {
    'pending': 'Ожидание',
    'confirmed': 'Подтверждено',
    'cancelled': 'Отменено'
  }
  return statusMap[props.appointment.status] || props.appointment.status
})

const formattedDate = computed(() => {
  return props.appointment.start_time.split(' ')[0]
})

const formattedTime = computed(() => {
  return props.appointment.start_time.split(' ')[1]
})

const statusBackground = computed(() => {
  switch (props.appointment.status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200'
    case 'confirmed': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
    case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200'
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
  }
})

const statusColor = computed(() => {
  switch (props.appointment.status) {
    case 'pending': return 'hover:border-yellow-300 dark:hover:border-yellow-500'
    case 'confirmed': return 'hover:border-green-300 dark:hover:border-green-500'
    case 'cancelled': return 'hover:border-red-300 dark:hover:border-red-500'
    default: return 'hover:border-gray-300 dark:hover:border-gray-500'
  }
})
</script>

<style scoped>
.card {
  transition: all 0.3s ease;
}
.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
</style>

<template>
  <div
    class="card bg-[#fafafa] dark:bg-gray-800 border border-gray-200 dark:border-gray-600/20 rounded-md p-3 cursor-move transition-colors duration-300"
    :class="statusColor"
    draggable="true"
    @dragstart="$emit('dragstart', $event)"
    @click.stop
  >
    <div class="flex justify-between items-start">
      <div>
        <h3 class="font-bold text-blue-600 dark:text-blue-300">{{ appointment.specialty }}</h3>
        <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ appointment.doctor_name }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ formattedDate }} на {{ formattedTime }}</p>
      </div>
      <span class="text-xs px-2 py-1 rounded-full" :class="statusBackground">
        {{ statusTranslation }}
      </span>
    </div>
    <div class="mt-2">
      <p class="text-sm dark:text-gray-300">
        <span class="font-medium text-gray-800 dark:text-gray-200">Пациент:</span> {{ appointment.patient_name }}
      </p>
      <p class="text-sm dark:text-gray-300">
        <span class="font-medium text-gray-800 dark:text-gray-200">Причина обращения:</span> 
        {{ appointment.reason || 'Не указана' }}
      </p>
      <!-- Показываем причину отмены только для отмененных записей -->
      <p 
        v-if="appointment.status === 'cancelled' && appointment.cancel_reason" 
        class="text-sm text-red-600 dark:text-red-400 mt-1"
      >
        <span class="font-medium">Причина отмены:</span> {{ appointment.cancel_reason }}
      </p>
    </div>
    <div class="mt-3 flex justify-end">
      <button 
        @click.stop="$emit('change-status', appointment.id)" 
        class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-800 py-1 px-2 rounded dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300"
      >
        Изменить
      </button>
    </div>
  </div>
</template>