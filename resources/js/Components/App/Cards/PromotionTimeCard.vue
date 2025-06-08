<template>
  <div
    :class="['p-6 rounded-lg shadow-lg transition-all duration-300 border flex flex-col h-full',
      isExpired ? 'bg-gray-50 border-gray-200 ended' : 'bg-white border-blue-100 hover:shadow-xl hover:-translate-y-1']">
    <!-- Изображение и скидка -->
    <div class="relative aspect-w-16 aspect-h-9 overflow-hidden rounded-lg">
      <img :src="imageUrl" alt="Акция"
        :class="['object-cover w-full h-full transition-all duration-300', isExpired ? 'opacity-60 brightness-75' : '']" />
      <div v-if="!isExpired" :class="discountBadgeClass">
        {{ discount }}
      </div>
      <div v-else class="absolute top-2 right-2 bg-gray-600 text-white text-xs font-bold px-2 py-1 rounded">
        Завершено
      </div>
    </div>

    <!-- Основной контент -->
    <div class="mt-4 flex-grow">
      <div class="flex items-center">
        <svg class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"
          :class="isExpired ? 'text-gray-400' : (bgColor === 'red' ? 'text-red-500' : 'text-purple-500')">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        <h3
          :class="['text-base sm:text-lg md:text-base lg:text-lg font-bold', isExpired ? 'text-gray-600' : 'text-gray-900']">
          {{ title }}
        </h3>
      </div>
      <p
        :class="['mt-2 text-sm sm:text-base md:text-sm lg:text-base description', isExpired ? 'text-gray-500' : 'text-gray-600']">
        {{ description }}
      </p>
    </div>

    <!-- Таймер или даты акции -->
    <div v-if="!isExpired" :class="timerContainerClass">
      <p class="text-xs text-gray-500">Акция заканчивается через:</p>
      <div class="flex justify-center space-x-2 mt-1">
        <span class="bg-white px-2 py-1 rounded text-sm font-bold">{{ time.hours }}</span>
        <span class="bg-white px-2 py-1 rounded text-sm font-bold">{{ time.minutes }}</span>
        <span class="bg-white px-2 py-1 rounded text-sm font-bold">{{ time.seconds }}</span>
      </div>
    </div>
    <div v-else class="mt-4 bg-gray-100 p-3 rounded text-center flex-shrink-0">
      <p class="text-xs text-gray-600 mb-1">Период проведения:</p>
      <p class="text-sm font-medium text-gray-700">
        {{ formatDate(startDate) }} — {{ formatDate(endDate) }}
      </p>
    </div>

    <!-- Кнопка -->
    <div class="mt-4">
      <button v-if="$page.props.auth.user" :class="['w-full text-sm sm:text-base md:text-sm lg:text-base px-4 py-2 text-white rounded-md font-medium transition-colors duration-300',
        isExpired ? 'bg-gray-600 cursor-not-allowed' : buttonClass]" :disabled="isExpired">
        {{ isExpired ? 'Акция завершена' : 'Подробнее' }}
      </button>
      <Link href="/login">
      <button :class="['w-full text-sm sm:text-base md:text-sm lg:text-base px-4 py-2 text-white rounded-md font-medium transition-colors duration-300',
        isExpired ? 'bg-gray-600 cursor-not-allowed' : buttonClass]" :disabled="isExpired">
        {{ isExpired ? 'Акция завершена' : 'Записаться онлайн' }}
      </button>
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
  title: String,
  description: String,
  discount: String,
  imageUrl: String,
  durationHours: Number,
  isExpired: {
    type: Boolean,
    default: false
  },
  startDate: {
    type: [String, Date],
    required: true
  },
  endDate: {
    type: [String, Date],
    required: true
  }
})

// Форматирование даты в формате DD.MM.YYYY
const formatDate = (dateString) => {
  const date = new Date(dateString)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}.${month}.${year}`
}

const localStorageKey = `offer-timer-${props.title}-${props.discount}`
const endTime = ref(0)
const time = ref({ hours: '00', minutes: '00', seconds: '00' })
const isExpired = ref(props.isExpired)

// Цвет фона в зависимости от оставшегося времени
const bgColor = computed(() => {
  if (isExpired.value) return 'gray'

  // Если таймер уже идёт, используем оставшиеся часы
  if (time.value.hours !== '00') {
    const remainingHours = parseInt(time.value.hours)
    return remainingHours <= 24 ? 'red' : 'purple'
  }

  // Иначе используем начальную продолжительность
  return props.durationHours <= 24 ? 'red' : 'purple'
})

// Вычисляемые классы
const discountBadgeClass = computed(() => {
  return `absolute top-2 right-2 ${bgColor.value === 'red' ? 'bg-red-500' : 'bg-purple-500'} text-white text-xs font-bold px-2 py-1 rounded`
})

const timerContainerClass = computed(() => {
  return `mt-4 ${bgColor.value === 'red' ? 'bg-red-50' : 'bg-purple-50'} p-2 rounded text-center flex-shrink-0`
})

const buttonClass = computed(() => {
  if (bgColor.value === 'red') {
    return 'bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600'
  } else {
    return 'bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600'
  }
})

function initializeEndTime() {
  if (props.isExpired) return;

  const saved = localStorage.getItem(localStorageKey)
  const now = Date.now()

  if (saved && !isNaN(Number(saved)) && Number(saved) > now) {
    endTime.value = Number(saved)
  } else {
    endTime.value = now + Number(props.durationHours) * 60 * 60 * 1000
    localStorage.setItem(localStorageKey, String(endTime.value))
  }
}

function updateTimer() {
  if (props.isExpired) return;

  const now = Date.now()
  const diff = endTime.value - now

  if (diff <= 0) {
    isExpired.value = true
    return
  }

  const h = Math.floor(diff / 1000 / 60 / 60)
  const m = Math.floor((diff / 1000 / 60) % 60)
  const s = Math.floor((diff / 1000) % 60)

  time.value = {
    hours: String(h).padStart(2, '0'),
    minutes: String(m).padStart(2, '0'),
    seconds: String(s).padStart(2, '0')
  }
}

onMounted(() => {
  if (!props.isExpired) {
    initializeEndTime()
    updateTimer()
    const timer = setInterval(() => {
      if (isExpired.value) {
        clearInterval(timer)
        return
      }
      updateTimer()
    }, 1000)
  }
})
</script>

<style scoped>
@media (min-width: 1022.5px) {
  .ended {
    display: none;
  }
}

.description {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
  -webkit-line-clamp: 3;
  text-overflow: ellipsis;
}
</style>