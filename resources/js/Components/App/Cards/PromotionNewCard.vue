<template>
  <div
    class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 border border-blue-100 transform hover:-translate-y-1 transition-transform flex flex-col h-full">
    <!-- Изображение и тег -->
    <div class="relative aspect-w-16 aspect-h-9 overflow-hidden rounded-lg flex-shrink-0">
      <img :src="imageUrl" :alt="title" class="object-cover w-full h-full" />
      <div class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded">
        {{ tag }}
      </div>
    </div>

    <!-- Основной контент с flex-grow -->
    <div class="mt-4 flex-grow flex flex-col">
      <div class="flex items-center">
        <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
        <h3 class="text-lg font-bold text-gray-900">{{ title }}</h3>
      </div>

      <!-- Описание с ограничением строк -->
      <p class="mt-2 text-base text-gray-600 description flex-grow">
        {{ description }}
      </p>

      <!-- Кнопка внизу -->
      <div class="mt-4">
        <Link href="/profile">
        <button
          class="w-full px-4 py-2 bg-gradient-to-r from-green-600 to-green-500 text-white rounded-md hover:from-green-700 hover:to-green-600 transition-colors duration-300 font-medium">
          Подробнее
        </button>
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  tag: String,
  title: String,
  description: String,
  imageUrl: String
})
</script>

<style scoped>
.description {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
  -webkit-line-clamp: 3;
  text-overflow: ellipsis;
}
</style>