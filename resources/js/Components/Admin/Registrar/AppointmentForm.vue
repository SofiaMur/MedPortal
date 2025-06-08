<template>
  <div class="bg-white rounded-lg shadow-md p-6 mb-8 border border-gray-200">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Запись на прием</h2>
    
    <form @submit.prevent="emit('create')" class="space-y-6">
      <!-- Поиск пациента -->
      <!-- <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Поиск пациента по СНИЛС*</label>
        <div class="mt-1 flex">
          <input
            type="text"
            v-model="model.snils"
            @input="handleSnilsInput"
            class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3"
            placeholder="___-___-___ __"
            required
          >
          <button
            type="button"
            @click="emit('search')"
            class="inline-flex items-center px-4 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 hover:bg-gray-100 transition-colors"
          >
            <MagnifyingGlassIcon class="h-5 w-5" />
          </button>
        </div>
        <div v-if="model.patient_name" class="mt-2 text-sm text-gray-600">
          Найден пациент: <span class="font-medium">{{ model.patient_name }}</span>
        </div>
        <div v-else-if="model.snils && !model.patient_name && !isSearching" class="mt-2 text-sm text-red-600">
          Пациент не найден. Зарегистрируйте нового пациента.
        </div>
        <div v-if="isSearching" class="mt-2 text-sm text-gray-500">
          Поиск пациента...
        </div>
      </div> -->
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Выбор специальности -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Специальность</label>
          <select 
            v-model="model.specialty_id"
            @change="emit('load-doctors')"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3"
            :disabled="isLoadingSpecialties"
          >
            <option value="" selected>Любая специальность</option>
            <option v-for="specialty in props.specialties" :key="specialty.id" :value="specialty.id">
              {{ specialty.name }}
            </option>
          </select>
          <div v-if="isLoadingSpecialties" class="mt-1 text-xs text-gray-500">
            Загрузка специальностей...
          </div>
        </div>
        
        <!-- Выбор врача -->
        <!-- <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Врач*</label>
          <select 
            v-model="model.doctor_id"
            @change="handleDoctorChange"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3"
            required
            :disabled="isLoadingDoctors"
          >
            <option value="" disabled selected>Выберите врача</option>
            <option 
              v-for="doctor in filteredDoctors" 
              :key="doctor.id" 
              :value="doctor.id"
              :disabled="!isDoctorAvailable(doctor.id)"
            >
              {{ doctor.full_name }} ({{ doctor.specialty.name }})
              <template v-if="!isDoctorAvailable(doctor.id)"> - Недоступен</template>
            </option>
          </select>
          <div v-if="isLoadingDoctors" class="mt-1 text-xs text-gray-500">
            Загрузка врачей...
          </div>
        </div> -->
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Выбор даты -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Дата приема*</label>
          <input 
            type="date" 
            v-model="model.date"
            @change="emit('load-times')"
            :min="new Date().toISOString().split('T')[0]"
            :max="maxDate"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3"
            required
            :disabled="!model.doctor_id"
          >
        </div>
        
        <!-- Выбор времени -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Время приема*</label>
          <select 
            v-model="model.time"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3"
            :disabled="!props.availableTimes.length || isLoadingTimes"
            required
          >
            <option value="" disabled selected>Выберите время</option>
            <option v-for="time in props.availableTimes" :key="time" :value="time">
              {{ formatTime(time) }}
            </option>
          </select>
          <div v-if="isLoadingTimes" class="mt-1 text-xs text-gray-500">
            Загрузка доступного времени...
          </div>
        </div>
      </div>
      
      <!-- Причина обращения -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Причина обращения*</label>
        <div class="mt-1 flex flex-wrap gap-2 mb-2">
          <button 
            v-for="reason in props.commonReasons" 
            :key="reason"
            @click.prevent="model.reason = reason"
            type="button"
            class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-full transition-colors"
          >
            {{ reason }}
          </button>
        </div>
        <textarea 
          v-model="model.reason"
          rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3"
          required
          placeholder="Опишите причину обращения"
        ></textarea>
      </div>
      
      <div class="flex justify-end space-x-3 pt-2">
        <button 
          type="button" 
          @click="emit('reset')" 
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
        >
          Очистить
        </button>
        <button 
          type="submit" 
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors"
          :disabled="!model.patient_id || props.isLoading"
        >
          <span v-if="props.isLoading">Создание записи...</span>
          <span v-else>Записать на прием</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import type { PropType } from 'vue';
import { computed } from 'vue';

interface AppointmentModel {
  snils: string
  patient_name: string
  patient_id: number | null
  specialty_id: string
  doctor_id: string
  date: string
  time: string
  reason: string
}

interface Doctor {
  id: string
  full_name: string
  specialty: {
    id: string
    name: string
  }
  specialty_id: string
  available: boolean
}

interface Specialty {
  id: string
  name: string
}

const props = defineProps({
  model: {
    type: Object as PropType<AppointmentModel>,
    required: true,
    default: () => ({
      snils: '',
      patient_name: '',
      patient_id: null,
      specialty_id: '',
      doctor_id: '',
      date: '',
      time: '',
      reason: ''
    })
  },
  specialties: {
    type: Array as PropType<Specialty[]>,
    default: () => []
  },
  doctors: {
    type: Array as PropType<Doctor[]>,
    default: () => []
  },
  availableTimes: {
    type: Array as PropType<string[]>,
    default: () => []
  },
  commonReasons: {
    type: Array as PropType<string[]>,
    default: () => [
      'Консультация',
      'Диагностика',
      'Лечение',
      'Профилактический осмотр',
      'Оформление справки'
    ]
  },
  isSearching: {
    type: Boolean,
    default: false
  },
  isLoadingSpecialties: {
    type: Boolean,
    default: false
  },
  isLoadingDoctors: {
    type: Boolean,
    default: false
  },
  isLoadingTimes: {
    type: Boolean,
    default: false
  },
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits([
  'create',
  'reset',
  'search',
  'load-doctors',
  'update-specialty',
  'load-times',
  'update:filters'
])

const maxDate = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 14)
  return date.toISOString().split('T')[0]
})

const filteredDoctors = computed(() => {
  if (!props.model.specialty_id) return props.doctors
  return props.doctors.filter(doctor => 
    doctor.specialty_id === props.model.specialty_id
  )
})

// const isDoctorAvailable = (doctorId) => {
//   const doctor = props.doctors.find(d => d.id === doctorId)
//   return doctor ? doctor.available : false
// }

// const loadAvailableTimes = async () => {
//   if (!props.model.doctor_id || !props.model.date) {
//     return;
//   }
  
//   emit('load-times'); 
// };

const formatTime = (time) => {
  return time
}

// const handleSnilsInput = (e) => {
//   const value = e.target.value;
//   emit('update:model', { ...props.model, snils: value });
//   emit('update:filters', { 
//     snils: value,
//     doctor_id: '',
//     date: '',
//     time: '' 
//   });
// };

// const handleDoctorChange = () => {
//   emit('update-specialty')
//   emit('update:filters', { 
//     ...props.model, 
//     date: '',
//     time: '' 
//   })
// }
</script>