<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, inject, watch, onMounted, computed } from 'vue';
import Header from '@/Components/App/Header.vue';
import Footer from '@/Components/App/Footer.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';

const props = defineProps({
  doctors: {
    type: Array,
    default: () => []
  },
  specialties: {
    type: Array,
    default: () => []
  }
});

function formatDisplayDate(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  const day = date.getDate().toString().padStart(2, '0');
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const year = date.getFullYear();
  return `${day}.${month}.${year}`;
}

function formatPhone(value) {
  if (!value) return '';
  const digits = value.replace(/\D/g, '');
  return digits.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '+$1 ($2) $3-$4-$5');
}

const { doctor, occupiedTimes: initialOccupiedTimes } = usePage().props;
const occupiedTimes = ref(initialOccupiedTimes || []);
const user = usePage().props.auth.user;
const showModal = ref(false);
const selectedDoctor = ref(null);
const currentStep = ref(1);
const selectedDate = ref(null);
const selectedTime = ref(null);
const currentWeekStart = ref(new Date());
const userData = ref({
  fullName: '',
  birthDate: '',
  phone: ''
});
const errorMessage = ref('');
const appointmentSubmitted = ref(false);

const searchQuery = ref('');
const activeSpecialty = ref('all');

const filteredDoctors = computed(() => {
  let result = props.doctors;

  if (activeSpecialty.value !== 'all') {
    result = result.filter(doctor => doctor.specialty_id == activeSpecialty.value);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(doctor =>
      doctor.user.full_name.toLowerCase().includes(query) ||
      doctor.specialty.name.toLowerCase().includes(query)
    );
  }

  return result;
});

const prevWeek = () => {
  const date = new Date(currentWeekStart.value);
  date.setDate(date.getDate() - 7);
  currentWeekStart.value = date;
};

const nextWeek = () => {
  const date = new Date(currentWeekStart.value);
  date.setDate(date.getDate() + 7);
  currentWeekStart.value = date;
};

const formatDate = (date) => {
  const options = { weekday: 'short', day: 'numeric', month: 'short' };
  return new Date(date).toLocaleDateString('ru-RU', options);
};

const availableDays = computed(() => {
  if (!selectedDoctor.value) return [];

  const days = [];
  const scheduleDays = selectedDoctor.value.schedules.map(s => s.day_of_week);

  for (let i = 0; i < 7; i++) {
    const date = new Date(currentWeekStart.value);
    date.setDate(date.getDate() + i);

    const dayOfWeek = date.getDay() || 7;
    const isAvailable = scheduleDays.includes(dayOfWeek);

    if (isAvailable) {
      days.push({
        date: date.toISOString().split('T')[0],
        dayName: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'][dayOfWeek - 1],
        dayNumber: date.getDate(),
        month: ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'][date.getMonth()],
        isAvailable: true
      });
    }
  }

  return days;
});

const availableTimes = computed(() => {
  if (!selectedDoctor.value || !selectedDate.value || !occupiedTimes.value) return []; // Добавил проверку на undefined

  const times = [];
  const schedule = selectedDoctor.value.schedules.find(s => s.day_of_week === new Date(selectedDate.value).getDay());

  if (schedule) {
    let currentTime = new Date(selectedDate.value);
    const startTime = new Date(selectedDate.value).setHours(schedule.start_time.split(':')[0], schedule.start_time.split(':')[1]);
    const endTime = new Date(selectedDate.value).setHours(schedule.end_time.split(':')[0], schedule.end_time.split(':')[1]);

    while (currentTime < endTime) {
      const timeString = currentTime.toTimeString().substring(0, 5);
      if (!occupiedTimes.value.includes(timeString)) {
        times.push(timeString);
      }
      currentTime = new Date(currentTime.getTime() + selectedDoctor.value.appointment_duration * 60000);
    }
  }
  return times;
});

const isBusy = (time) => {
  return occupiedTimes.value.includes(time);
};

const selectDate = async (day) => {
  selectedDate.value = day.date;
  selectedTime.value = null;

  try {
    const response = await axios.get(route('public.doctors.occupied-times', {
      doctor: selectedDoctor.value.id,
      date: day.date
    }));
    occupiedTimes.value = response.data;
  } catch (error) {
    console.error('Error fetching occupied times:', error);
    occupiedTimes.value = [];
  }
};

const formattedSelectedDate = computed(() => {
  if (!selectedDate.value) return '';
  return formatDate(selectedDate.value);
});

const currentWeekRange = computed(() => {
  const start = new Date(currentWeekStart.value);
  const end = new Date(start);
  end.setDate(end.getDate() + 6);

  return `${formatDate(start)} - ${formatDate(end)}`;
});

const openModal = (doctor) => {
  selectedDoctor.value = doctor;
  showModal.value = true;
  currentStep.value = 1;
  selectedDate.value = null;
  selectedTime.value = null;
  userData.value = { fullName: '', birthDate: '', phone: '' };
  errorMessage.value = '';
  appointmentSubmitted.value = false;

  const today = new Date();
  const dayOfWeek = today.getDay() || 7;
  currentWeekStart.value = new Date(today.setDate(today.getDate() - dayOfWeek + 1));
};

const closeModal = () => {
  showModal.value = false;
};

const appointmentForm = useForm({
  doctor_id: null,
  start_time: null,
  reason: ''
});

const submitAppointment = () => {
  const startTime = `${selectedDate.value}T${selectedTime.value}:00`;

  appointmentForm.doctor_id = selectedDoctor.value.id;
  appointmentForm.start_time = startTime;

  appointmentForm.post(route('public.doctors.appointments.store'), {
    preserveScroll: true,
    onSuccess: () => {
      appointmentSubmitted.value = true;
      currentStep.value = 4;
    },
    onError: (errors) => {
      errorMessage.value = errors.message || 'Ошибка при создании записи';
      currentStep.value = 5;
    }
  });
};

const darkMode = ref(false);
const translation = inject('translation');
const selectedLanguage = ref(localStorage.getItem('selectedLanguage') || 'ru');
const t = (key) => translation.t(key);

const languages = [
  { name: 'Русский', code: 'ru' },
  { name: 'English', code: 'en' }
];

function updateTheme() {
  if (darkMode.value) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}

function toggleDarkMode() {
  darkMode.value = !darkMode.value;
}

const changeLanguage = async (langCode) => {
  if (selectedLanguage.value === langCode) return;
  await translation.setLocale(langCode);

  selectedLanguage.value = langCode;
  localStorage.setItem('selectedLanguage', langCode);
};

const selectTime = (time) => {
  if (!isBusy(time)) {
    selectedTime.value = time;
  }
};

watch(darkMode, (newVal) => {
  localStorage.setItem('darkMode', newVal);
  updateTheme();
}, { immediate: true });

watch(() => translation.state.locale, () => {
  document.title = t('app.title');
});

onMounted(() => {
  const savedMode = localStorage.getItem('darkMode');
  if (savedMode) darkMode.value = savedMode === 'true';
});
</script>

<template>

  <Head title="Медицинский портал - наши врачи" />

  <Header :darkMode="darkMode" :selectedLanguage="selectedLanguage" :languages="languages" :t="t"
    @toggleDarkMode="toggleDarkMode" @changeLanguage="changeLanguage" />
  <ScrollToTop />

  <main class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
    <div v-if="!$page.props.auth.user" class="bg-blue-700">
      <div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
          <span class="block">Готовы начать?</span>
          <span class="block">Зарегистрируйтесь прямо сейчас.</span>
        </h2>
        <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100">
          Получите доступ ко всем возможностям нашего медицинского портала
        </p>
        <Link href="/register"
          class="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 sm:w-auto">
        Бесплатная регистрация
        </Link>
      </div>
    </div>

    <div class="py-12 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
          <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Наша команда</h2>
          <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Профессиональные врачи
          </p>
          <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
            Наши специалисты - это высококвалифицированные врачи с большим опытом работы
          </p>
        </div>

        <div class="mt-12 space-y-8">
          <!-- Поиск и кнопка "Все специалисты" -->
          <div class="mt-12 flex justify-center">
            <div class="flex w-full sm:w-2/3 md:w-1/2">
              <!-- Поиск -->
              <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input v-model="searchQuery" type="text" placeholder="Поиск врачей..."
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-l-full rounded-r-none leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
              </div>

              <!-- Кнопка "Все специалисты" -->
              <button @click="activeSpecialty = 'all'" :class="[
                'px-5 py-2 border border-l-0 rounded-r-full rounded-l-none text-sm font-medium shadow transition whitespace-nowrap',
                activeSpecialty === 'all'
                  ? 'bg-blue-600 text-white border-blue-600'
                  : 'bg-white text-gray-700 hover:bg-gray-100 border-gray-300'
              ]">
                Все специалисты
              </button>
            </div>
          </div>

          <!-- Специальности -->
          <div class="flex flex-wrap justify-center gap-3">
            <div v-for="spec in specialties" :key="spec.id" @click="activeSpecialty = spec.id.toString()" :class="[
              'cursor-pointer px-4 py-2 rounded-full shadow text-sm font-medium transition select-none',
              activeSpecialty === spec.id.toString() ? 'bg-blue-600 text-white' : 'bg-white text-gray-800 hover:bg-gray-100 border border-gray-200'
            ]">
              {{ spec.name }}
            </div>
          </div>

          <div v-if="filteredDoctors.length > 0" class="grid pt-12 grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="doctor in filteredDoctors" :key="doctor.id"
              class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow border border-gray-200 p-6">
              <!-- Аватар -->
              <div class="flex items-center mb-4">
                <div class="w-20 aspect-square rounded-full overflow-hidden border border-gray-200">
                  <img
                    :src="doctor.user.photo_path || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor.user.full_name)"
                    :alt="doctor.user.full_name" class="w-full h-full object-cover" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-semibold text-gray-800">
                    Др. {{ doctor.user.full_name }}
                  </h3>
                  <!-- Специальность -->
                  <div class="mt-1" v-if="doctor.specialty">
                    <p class="text-blue-600 font-medium text-sm">{{ doctor.specialty.name }}</p>
                  </div>
                </div>
              </div>

              <!-- Кабинет -->
              <div class="flex items-center text-sm text-gray-500 mb-1">
                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Кабинет: {{ doctor.room }}
              </div>

              <!-- Опыт -->
              <div class="flex items-center text-sm text-gray-500 mb-4" v-if="doctor.experience">
                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Стаж: {{ doctor.experience.years }} лет
              </div>

              <!-- Описание специальности -->
              <p class="my-4 text-gray-600" v-if="doctor.specialty?.description">
                {{ doctor.specialty.description }}
              </p>

              <!-- Кнопка -->
              <button @click="openModal(doctor)"
                class="w-full flex justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                Записаться на прием
              </button>
            </div>
          </div>


          <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Врачи не найдены</h3>
            <p class="mt-1 text-sm text-gray-500">Попробуйте изменить параметры поиска или фильтрации.</p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Модальное окно записи -->
  <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6 relative max-h-[90vh] overflow-y-auto">
      <button v-if="currentStep < 4" @click="closeModal"
        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Шаг 1: Информация о враче -->
      <div v-if="currentStep === 1" class="space-y-6">
        <div class="flex items-center space-x-4">
          <!-- Аватар -->
          <div class="w-20 aspect-square rounded-full overflow-hidden border border-gray-200">
            <img
              :src="selectedDoctor.user.photo_path || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedDoctor.user.full_name)"
              :alt="selectedDoctor.user.full_name" class="w-full h-full object-cover" />
          </div>

          <!-- Информация о враче -->
          <div>
            <h3 class="text-xl font-bold text-gray-900">Др. {{ selectedDoctor.user.full_name }}</h3>
            <p class="text-blue-600 font-medium">{{ selectedDoctor.specialty.name }}</p>
            <div class="flex items-center text-sm  mt-2 text-gray-500">
              <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Длительность приема: {{ selectedDoctor.appointment_duration }} мин
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <h4 class="font-bold text-gray-900 mb-2">О специалисте:</h4>
            <p class="text-gray-700 text-sm">{{ selectedDoctor.experience.description }}</p>
          </div>

          <div class="space-y-3">
            <h4 class="font-bold text-gray-900">Образование:</h4>
            <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
              <li v-for="(edu, idx) in selectedDoctor.educations" :key="idx">
                {{ edu.institution }} ({{ edu.ended_year ?? 'не указано' }})
              </li>
            </ul>
          </div>

          <div class="space-y-3" v-if="selectedDoctor.achievements">
            <h4 class="font-bold text-gray-900">Достижения:</h4>
            <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">
              <li v-for="(ach, idx) in selectedDoctor.achievements" :key="idx">
                {{ ach.title }}
              </li>
            </ul>
          </div>
        </div>

        <button v-if="$page.props.auth.user" @click="currentStep = 2"
          :disabled="!$page.props.auth.user.is_profile_completed" :class="{
            'bg-blue-600 hover:bg-blue-700': $page.props.auth.user.is_profile_completed,
            'bg-gray-600 cursor-not-allowed': !$page.props.auth.user.is_profile_completed
          }" class="w-full mt-6 text-white py-3 px-4 rounded-md transition-colors font-medium">
          <span v-if="$page.props.auth.user.is_profile_completed">
            Выбрать время приема
          </span>
          <span v-else>
            Завершите заполнение профиля
          </span>
        </button>

        <button v-else class="w-full mt-6 bg-gray-600 cursor-not-allowed text-white py-3 px-4 rounded-md font-medium">
          Необходимо авторизоваться
        </button>
      </div>

      <!-- Шаг 2: Выбор даты и времени -->
      <div v-if="currentStep === 2" class="space-y-6">
        <h3 class="text-xl font-bold text-gray-900 text-center">Выберите время приема</h3>

        <div class="flex justify-between items-center mb-4">
          <button @click="prevWeek" class="p-2 rounded-full hover:bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <span class="font-medium text-gray-800">{{ currentWeekRange }}</span>
          <button @click="nextWeek" class="p-2 rounded-full hover:bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
          <div v-for="day in availableDays" :key="day.date" @click="selectDate(day)"
            :class="{ 'border-blue-500 bg-blue-50': selectedDate === day.date, 'border-gray-200': selectedDate !== day.date }"
            class="border rounded-lg p-3 text-center cursor-pointer transition-colors">
            <div class="text-sm font-medium">{{ day.dayName }}</div>
            <div class="text-lg font-bold mt-1">{{ day.dayNumber }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ day.month }}</div>
          </div>
        </div>

        <div v-if="selectedDate" class="mt-4">
          <h4 class="font-medium text-gray-900 mb-3">Доступное время:</h4>
          <div class="grid grid-cols-3 gap-2">
            <button v-for="time in availableTimes" :key="time" @click="!isBusy(time) && selectTime(time)" :class="{
              'bg-blue-600 text-white': selectedTime === time,
              'bg-gray-100 text-gray-800 hover:bg-gray-200': selectedTime !== time && !isBusy(time),
              'bg-gray-300 text-gray-600 cursor-not-allowed': isBusy(time)
            }" class="py-2 px-3 rounded-md text-sm font-medium transition-colors" :disabled="isBusy(time)">
              <!-- Блокируем клик -->
              {{ time }}
            </button>
          </div>
        </div>

        <div class="flex justify-between mt-6">
          <button @click="currentStep = 1" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
            Назад
          </button>
          <button @click="currentStep = 3" :disabled="!selectedDate || !selectedTime"
            :class="{ 'bg-blue-600 hover:bg-blue-700': selectedDate && selectedTime, 'bg-gray-300 cursor-not-allowed': !selectedDate || !selectedTime }"
            class="px-6 py-2 text-white rounded-md font-medium transition-colors">
            Продолжить
          </button>
        </div>
      </div>

      <!-- Шаг 3: Подтверждение данных -->
      <div v-if="currentStep === 3" class="space-y-6">
        <h3 class="text-xl font-bold text-gray-900 text-center">Подтвердите запись</h3>

        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="flex items-center space-x-4">
            <!-- Аватар -->
            <div class="w-16 aspect-square rounded-full overflow-hidden border border-gray-200">
              <img
                :src="selectedDoctor.user.photo_path || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedDoctor.user.full_name)"
                :alt="selectedDoctor.user.full_name" class="w-full h-full object-cover" />
            </div>

            <!-- Информация о враче -->
            <div>
              <h3 class="font-bold text-gray-900">Др. {{ selectedDoctor.user.full_name }}</h3>
              <p class="text-blue-600 text-sm">{{ selectedDoctor.specialty.name }}</p>
            </div>
          </div>
          <div class="mt-4 space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">Дата:</span>
              <span class="font-medium">{{ formattedSelectedDate }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Время:</span>
              <span class="font-medium">{{ selectedTime }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Кабинет:</span>
              <span class="font-medium">{{ selectedDoctor.room }}</span>
            </div>
          </div>
        </div>

        <div class="space-y-4">
          <!-- ФИО -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ваше ФИО*</label>
            <input type="text" :value="`${user.full_name}`" disabled
              class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md focus:outline-none" />
          </div>

          <!-- Дата рождения -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Дата рождения*</label>
            <input type="text" :value="formatDisplayDate(user.birth_date)" disabled
              class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md focus:outline-none" />
          </div>

          <!-- Телефон -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Телефон*</label>
            <input type="text" :value="formatPhone(user.phone)" disabled
              class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md focus:outline-none" />
          </div>

          <!-- Жалоба -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Жалоба</label>
            <textarea v-model="appointmentForm.reason" placeholder="Пожалуйста, опишите свою проблему"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              rows="4"></textarea>
          </div>

          <!-- Кнопка отправки -->
          <div class="flex justify-between mt-6">
            <button @click="currentStep = 2" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
              Назад
            </button>
            <button @click="submitAppointment" :disabled="!selectedDate || !selectedTime || appointmentForm.processing"
              :class="{
                'bg-blue-600 hover:bg-blue-700': selectedDate && selectedTime,
                'bg-gray-300 cursor-not-allowed': !selectedDate || !selectedTime
              }" class="px-6 py-2 text-white rounded-md font-medium transition-colors">
              <span v-if="appointmentForm.processing">Отправка...</span>
              <span v-else>Подтвердить запись</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Шаг 4: Успешная запись -->
      <div v-if="currentStep === 4" class="text-center pt-6 pb-2">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Запись успешно оформлена!</h3>
        <p class="text-gray-600 mb-6">Детали записи можно посмотреть в личном кабинете</p>

        <button @click="closeModal"
          class="mt-2 bg-blue-600 text-white py-2.5 px-4 rounded-md hover:bg-blue-700 transition-colors font-medium">
          Закрыть
        </button>
      </div>

      <!-- Шаг 5: Ошибка записи -->
      <div v-if="currentStep === 5" class="text-center py-6">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Произошла ошибка</h3>
        <p class="text-gray-600 mb-6">
          {{ errorMessage || appointmentForm.errors[Object.keys(appointmentForm.errors)[0]] || 'Не удалось оформить запись' }}
        </p>
        <button @click="currentStep = 3"
          class="mt-4 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors font-medium">
          Попробовать снова
        </button>
      </div>
    </div>
  </div>

  <Footer :t="t" />
</template>