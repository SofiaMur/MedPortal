<script setup>
import { ref, watch, computed } from 'vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  show: Boolean,
  appointment: Object
})

const emit = defineEmits(['close', 'confirm'])

const statusOptions = [
  {
    value: 'pending',
    label: 'На рассмотрении',
    description: 'Запись ожидает подтверждения',
    inputClass: 'text-yellow-500 focus:ring-yellow-300 dark:focus:ring-yellow-500'
  },
  {
    value: 'confirmed',
    label: 'Подтверждено',
    description: 'Подтвердите запись пациента',
    inputClass: 'text-green-500 focus:ring-green-300 dark:focus:ring-green-500'
  },
  {
    value: 'cancelled',
    label: 'Отменено',
    description: 'Отмените запись с указанием причины',
    inputClass: 'text-red-500 focus:ring-red-300 dark:focus:ring-red-500'
  }
]

const newStatus = ref('pending')
const cancelReason = ref('')
const errorMessage = ref('')

const isStatusChanged = computed(() => {
  return props.appointment?.status !== newStatus.value
})

const isCancelReasonChanged = computed(() => {
  return props.appointment?.cancel_reason !== cancelReason.value
})

const isFormChanged = computed(() => {
  return isStatusChanged.value ||
    (newStatus.value === 'cancelled' && isCancelReasonChanged.value)
})

watch(() => props.appointment, (newAppointment) => {
  newStatus.value = newAppointment?.status || 'pending'
  cancelReason.value = newAppointment?.cancel_reason || ''
  errorMessage.value = ''
}, { immediate: true })

const close = () => {
  newStatus.value = props.appointment?.status || 'pending'
  cancelReason.value = props.appointment?.cancel_reason || ''
  errorMessage.value = ''
  emit('close')
}

const confirm = () => {
  if (!isStatusChanged.value) {
    errorMessage.value = 'Статус уже установлен на выбранное значение!'
    return
  }

  if (newStatus.value === 'cancelled' && !cancelReason.value.trim()) {
    errorMessage.value = 'Пожалуйста, укажите причину отмены!'
    emit('error', errorMessage.value)
    return
  }

  errorMessage.value = ''
  emit('confirm', {
    status: newStatus.value,
    cancelReason: cancelReason.value
  })

  if (newStatus.value !== 'cancelled') {
    cancelReason.value = ''
  }
}
</script>

<template>
  <Modal :show="show" @close="close" class="fixed z-[-10]">
    <!-- Заголовок -->
    <div class="bg-blue-600 dark:bg-blue-700 px-6 py-4 dark:from-blue-800 dark:to-blue-900">
      <h3 class="text-lg font-bold text-[#fafafa]">Изменение статуса записи</h3>
    </div>

    <!-- Основное -->
    <div class="p-6 bg-[#fafafa] dark:bg-gray-800">
      <div class="space-y-4">
        <!-- Опции -->
        <div v-for="option in statusOptions" :key="option.value"
          class="flex items-start p-3 rounded-md transition-colors cursor-pointer" :class="{
            'bg-blue-50 border border-blue-200 dark:bg-blue-600/20 dark:border-blue-800/20': newStatus === option.value,
            'hover:bg-gray-100 dark:hover:bg-gray-700/50': newStatus !== option.value
          }" @click="newStatus = option.value">
          <div class="flex items-center h-5 mt-0.5">
            <input :id="`status-${option.value}`" v-model="newStatus" type="radio" :value="option.value" class="h-4 w-4"
              :class="option.inputClass">
          </div>
          <label :for="`status-${option.value}`" class="ml-3 block">
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-300">{{ option.label }}</span>
            <span v-if="option.description" class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ option.description }}
            </span>
          </label>
        </div>

        <!-- Поле отмены -->
        <div v-if="newStatus === 'cancelled'" class="mt-2 transition-all duration-200 ease-in-out">
          <label for="cancel-reason" class="block text-sm font-medium text-gray-800 dark:text-gray-300 mb-1">
            Причина отмены <span class="text-red-500 dark:text-red-400">*</span>
          </label>
          <textarea id="cancel-reason" v-model="cancelReason" rows="3" placeholder="Укажите причину отмены записи..."
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 sm:text-sm dark:bg-gray-700 dark:border-gray-600/20 dark:text-gray-300 dark:placeholder-gray-400"
            required @input="errorMessage = ''"></textarea>
        </div>
      </div>

      <!-- Кнопки -->
      <div class="mt-6 flex justify-end space-x-3">
        <button type="button" @click="close"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-800 hover:bg-gray-100 focus:outline-none dark:text-gray-300 dark:border-gray-600/20 dark:hover:bg-gray-700">
          Отмена
        </button>
        <button type="button" @click="confirm" :disabled="!isFormChanged" :class="{
          'px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-[#fafafa] bg-blue-600 hover:bg-blue-700 focus:outline-none dark:bg-blue-700 dark:hover:bg-blue-600': isFormChanged,
          'px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-[#fafafa] bg-gray-400 cursor-not-allowed dark:bg-gray-600': !isFormChanged
        }">
          Сохранить
        </button>
      </div>
    </div>
  </Modal>
</template>