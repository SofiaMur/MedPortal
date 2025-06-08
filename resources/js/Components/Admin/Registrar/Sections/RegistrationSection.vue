<template>
  <!-- Кнопка переключения между формами -->
  <div class="flex mb-5 shadow-sm">
    <button 
      @click="showAppointmentForm = false"
      class="px-4 py-3 rounded-l-md border border-gray-300 dark:border-gray-600/20 shadow dark:shadow-900 text-sm font-md transition-colors"
      :class="{
        'bg-blue-600 text-[#fafafa] hover:bg-blue-700 dark:hover:bg-blue-500': !showAppointmentForm,
        'bg-[#fafafa] text-gray-700 dark:text-[#fafafa] hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700': showAppointmentForm
      }"
    >
      Регистрация пациента
    </button>
    <button 
      @click="showAppointmentForm = true"
      class="px-4 py-3 rounded-r-md border border-gray-300 dark:border-gray-600/20 shadow dark:shadow-900 text-sm font-md transition-colors"
      :class="{
        'bg-blue-600 text-[#fafafa] hover:bg-blue-700 dark:hover:bg-blue-500': showAppointmentForm,
        'bg-[#fafafa] text-gray-700 dark:text-[#fafafa] hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700': !showAppointmentForm
      }"
    >
      Запись на прием
    </button>
  </div>

  <!-- Уведомления -->
  <transition name="fade">
    <div v-if="appointmentSuccess" class="mt-4 p-4 bg-green-50 rounded-md border border-green-200">
      <div class="flex items-center">
        <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2" />
        <span class="text-green-800">Запись на прием успешно создана!</span>
      </div>
    </div>
  </transition>
  
  <transition name="fade">
    <div v-if="registrationSuccess" class="mt-4 p-4 bg-green-50 rounded-md border border-green-200">
      <div class="flex items-center">
        <CheckCircleIcon class="h-5 w-5 text-green-500 mr-2" />
        <span class="text-green-800">Пациент успешно зарегистрирован!</span>
      </div>
    </div>
  </transition>

  <!-- Форма регистрации пациента -->
  <PatientRegistrationForm
    v-if="!showAppointmentForm"
    v-model="registrationForm"
    :is-loading="isRegistering"
    :errors="registrationErrors"
    @register="registerPatient"
    @reset="resetRegistrationForm"
    @format-phone="formatPhoneInput"
    @format-insurance="formatInsuranceInput"
    @format-snils="formatSnilsInput"
  />

  <!-- Форма записи на прием -->
  <!-- <AppointmentForm
  v-model="appointmentForm"
  :specialties="specialties"
  :doctors="doctors"
  :available-times="availableTimes"
  :common-reasons="commonReasons"
  :is-searching="isSearching"
  :is-loading-specialties="isLoadingSpecialties"
  :is-loading-doctors="isLoadingDoctors"
  :is-loading-times="isLoadingTimes"
  :is-loading="isCreatingAppointment"
  @create="createAppointment"
  @reset="resetAppointmentForm"
  @search="searchPatientBySnils"
  @load-doctors="loadDoctorsBySpecialty"
  @update-specialty="updateSpecialtyFromDoctor"
  @load-times="loadAvailableTimes"
  @update:filters="updateFilters"
/> -->
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import PatientRegistrationForm from '@/Components/Admin/Registrar/PatientRegistrationForm.vue'
import AppointmentForm from '@/Components/Admin/Registrar/AppointmentForm.vue'

interface AppointmentFormModel {
  snils: string
  patient_name: string
  patient_id: number | null
  specialty_id: string
  doctor_id: string
  date: string
  time: string
  reason: string
}

const props = defineProps({
  appointments: Array,
  specialties: Array,
  patient: Object,
  doctors: Array,
  availableTimes: Array,
  filters: Object
})

const appointmentForm = ref<AppointmentFormModel>({
  snils: props.filters?.snils || '',
  patient_name: '',
  patient_id: null,
  specialty_id: props.filters?.specialty_id || '',
  doctor_id: props.filters?.doctor_id || '',
  date: props.filters?.date || '',
  time: '',
  reason: ''
})

const availableTimes = ref([]);

// Состояние для переключения между формами
const showAppointmentForm = ref(false)

// Состояния загрузки
const isRegistering = ref(false)
const isSearching = ref(false)
const isLoadingSpecialties = ref(false)
const isLoadingDoctors = ref(false)
const isLoadingTimes = ref(false)
const isCreatingAppointment = ref(false)

// Форма регистрации пациента
const registrationForm = ref({
  login: '',
  last_name: '',
  first_name: '',
  middle_name: '',
  birth_date: '',
  gender: '',
  phone: '',
  email: '',
  insurance_number: '',
  snils: '',
  chronic_diseases: '',
  allergies: ''
})

// Состояния для фильтров
const filters = ref({
  snils: '',
  specialty_id: '',
  doctor_id: '',
  date: ''
})

// Поиск пациента по СНИЛС
// const searchPatientBySnils = async () => {
//   isSearching.value = true;
  
//   await router.get('/registrar/dashboard', { snils: cleanSnils }, {
//     preserveState: true,
//     onSuccess: () => {
//       if (props.patient) {
//         appointmentForm.value.patient_name = props.patient.full_name;
//         appointmentForm.value.patient_id = props.patient.id;
//       } else {
//         appointmentForm.value.patient_name = '';
//         appointmentForm.value.patient_id = null;
//       }
//     },
//     onFinish: () => {
//       isSearching.value = false;
//     }
//   });
// };

// Загрузка врачей по специальности
// const loadDoctorsBySpecialty = async () => {
//    if (!appointmentForm.value.doctor_id || !appointmentForm.value.date) {
//     availableTimes.value = []; // Используем .value для ref
//     return;
//   }
  
//   isLoadingDoctors.value = true;
//   filters.value.specialty_id = appointmentForm.value.specialty_id;
//   filters.value.doctor_id = '';
//   filters.value.date = '';
  
//   await router.get('/registrar/dashboard', filters.value, {
//     preserveState: true,
//     onSuccess: () => {
//       appointmentForm.value.doctor_id = '';
//       availableTimes.value = [];
//     },
//     onFinish: () => {
//       isLoadingDoctors.value = false;
//     }
//   });
// };

// Загрузка доступного времени
// const loadAvailableTimes = async () => {
//   if (!appointmentForm.value.doctor_id || !appointmentForm.value.date) {
//     availableTimes.value = [];
//     return;
//   }
  
//   isLoadingTimes.value = true;
//   filters.value.doctor_id = appointmentForm.value.doctor_id;
//   filters.value.date = appointmentForm.value.date;
  
//   await router.get('/registrar/dashboard', filters.value, {
//     preserveState: true,
//     onSuccess: () => {
//       appointmentForm.value.time = '';
//     },
//     onFinish: () => {
//       isLoadingTimes.value = false;
//     }
//   });
// };

// Инициализация формы из filters
onMounted(() => {
  if (props.filters) {
    filters.value = { ...props.filters };
    appointmentForm.value.snils = props.filters.snils || '';
    appointmentForm.value.specialty_id = props.filters.specialty_id || '';
    appointmentForm.value.doctor_id = props.filters.doctor_id || '';
    appointmentForm.value.date = props.filters.date || '';
  }
});

// Успешные операции
const appointmentSuccess = ref(false)
const registrationSuccess = ref(false)

// Добавим состояние для ошибок
const registrationErrors = ref({});


// Фильтрация врачей по специальности
// const filteredDoctors = computed(() => {
//   if (!appointmentForm.value.specialty_id) return doctors.value
//   return doctors.value.filter(doctor => 
//     doctor.specialty_id === appointmentForm.value.specialty_id
//   )
// })

// Обновление специальности при выборе врача
// const updateSpecialtyFromDoctor = () => {
//   const selectedDoctor = doctors.value.find(d => d.id === appointmentForm.value.doctor_id)
//   if (selectedDoctor) {
//     appointmentForm.value.specialty_id = selectedDoctor.specialty_id
//   }
// }

const updateFilters = (newFilters) => {
  filters.value = { ...filters.value, ...newFilters };
};

// Сброс формы регистрации
const resetRegistrationForm = () => {
  registrationForm.value = {
    login: '',
    last_name: '',
    first_name: '',
    middle_name: '',
    birth_date: '',
    gender: '',
    phone: '',
    email: '',
    insurance_number: '',
    snils: '',
    chronic_diseases: '',
    allergies: ''
  }
  registrationSuccess.value = false
}

// Сброс формы записи
const resetAppointmentForm = () => {
  appointmentForm.value = {
    snils: '',
    patient_name: '',
    patient_id: null,
    specialty_id: '',
    doctor_id: '',
    date: '',
    time: '',
    reason: ''
  }
  availableTimes.value = []
  appointmentSuccess.value = false
}

// Регистрация нового пациента
const registerPatient = async () => {
  isRegistering.value = true;
  registrationErrors.value = {};

  // Обеспечиваем, что email будет строкой (даже если null)
  const formData = {
    ...registrationForm.value,
    phone: registrationForm.value.phone.replace(/\D/g, ''),
    insurance_number: registrationForm.value.insurance_number.replace(/\D/g, ''),
    snils: registrationForm.value.snils.replace(/\D/g, ''),
    email: registrationForm.value.email || ''
  };
  
  try {
    await router.post('/registrar/dashboard/patients', formData, {
      onSuccess: () => {
        registrationSuccess.value = true;
        resetRegistrationForm();
      },
      onError: (errors) => {
        registrationErrors.value = errors;
      }
    });
  } finally {
    isRegistering.value = false;
  }
};

// Форматирование телефона при вводе
const formatPhoneInput = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length > 11) value = value.slice(0, 11)
  
  let formattedValue = value
  if (value.length > 0) {
    formattedValue = value.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '$1 ($2) $3-$4-$5')
  }
  
  registrationForm.value.phone = formattedValue
}

// Форматирование полиса при вводе
const formatInsuranceInput = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length > 14) value = value.slice(0, 14)
  
  let formattedValue = value
  if (value.length > 0) {
    formattedValue = value.replace(/(\d{3})(\d{3})(\d{3})(\d{5})/, '$1-$2-$3-$4')
  }
  
  registrationForm.value.insurance_number = formattedValue
}

// Форматирование СНИЛС при вводе
const formatSnilsInput = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length > 11) value = value.slice(0, 11)
  
  let formattedValue = value
  if (value.length > 0) {
    formattedValue = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1-$2-$3 $4')
  }
  
  registrationForm.value.snils = formattedValue
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>