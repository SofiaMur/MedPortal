<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue'
import 'swiper/css'
import AppointmentCard from '@/Components/Admin/Registrar/AppointmentCard.vue'

defineProps({
  status: String,
  appointments: Array,
  draggingOver: Boolean,
  title: String,
  color: {
    type: String,
    default: 'gray'
  }
})

defineEmits(['drag-over', 'drag-leave', 'drop', 'change-status', 'drag-start'])
</script>

<style scoped>
.status-column {
  transition: all 0.3s ease;
}

.swiper-slide {
  width: auto !important;
  height: auto !important;
}

.swiper {
  padding: 20px 0;
  margin: -20px 0;
}
</style>

<template>
  <div 
    class="bg-[#fafafa] dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900 p-4 status-column flex-1 border border-gray-200 dark:border-gray-600/20" 
    :class="{ [`border-2 border-${color}-400 dark:border-${color}-500`]: draggingOver }"
  >
    <h2 class="text-lg font-semibold mb-4 flex justify-between items-center" :class="`text-${color}-600 dark:text-${color}-300`">
      <span>{{ title }}</span>
      <span :class="`bg-${color}-100 dark:bg-${color}-900/30 text-${color}-800 dark:text-${color}-200 text-xs font-medium px-2.5 py-0.5 rounded-full`">
        {{ appointments.length }}
      </span>
    </h2>
    
    <!-- Мобильная версия -->
    <div 
      class="lg:hidden"
      @dragover.prevent="$emit('drag-over')"
      @dragleave="$emit('drag-leave')"
      @drop.prevent="$emit('drop')"
    >
      <swiper
        :slides-per-view="1.2"
        :space-between="12"
        :grab-cursor="true"
        class="pb-2"
        v-if="appointments.length > 0"
      >
        <swiper-slide 
          v-for="appointment in appointments" 
          :key="`${status}-${appointment.id}`"
        >
          <AppointmentCard
            :appointment="appointment"
            @change-status="$emit('change-status', appointment.id)"
            draggable="true"
            @dragstart="$emit('drag-start', appointment)"
          />
        </swiper-slide>
      </swiper>
      <p v-else class="text-gray-500 dark:text-gray-400 text-sm">Нет записей</p>
    </div>
    
    <!-- Компьютерная версия -->
    <div 
      :id="`${status}-appointments`"
      class="hidden lg:block space-y-3 min-h-[200px]"
      @dragover.prevent="$emit('drag-over')"
      @dragleave="$emit('drag-leave')"
      @drop.prevent="$emit('drop')"
    >
      <AppointmentCard
        v-for="appointment in appointments"
        :key="`${status}-${appointment.id}`"
        :appointment="appointment"
        @change-status="$emit('change-status', appointment.id)"
        draggable="true"
        @dragstart="$emit('drag-start', appointment)"
      />
      <p v-if="appointments.length === 0" class="text-gray-500 dark:text-gray-400 text-sm">Нет записей</p>
    </div>
  </div>
</template>