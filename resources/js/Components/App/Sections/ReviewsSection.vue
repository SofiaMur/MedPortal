<template>
  <!-- Секция отзывов -->
  <section class="relative pb-16 pt-14 bg-gradient-to-b from-white to-gray-50">
    <div class="container mx-auto md:px-4 relative">
      <!-- Заголовок -->
      <div class="md:text-center md:ml-0 ml-4 mb-8">
        <span class="hidden md:block mb-4 w-16 h-1 rounded bg-blue-600 mx-auto"></span>
        <p
          class="title-with-underline mt-2 text-2xl sm:text-3xl md:text-2xl lg:text-3xl leading-8 font-bold tracking-tight text-gray-900 relative 
           before:md:hidden before:content-[''] before:absolute before:left-0 before:bottom-0 sm:before:w-[41%] before:w-[47%] before:h-[40%] before:bg-blue-50 before:rounded-[4px] before:z-0">
          <span class="relative z-10">Что говорят о нас</span>
        </p>
      </div>

      <!-- Слайдер -->
      <div class="relative max-w-4xl mx-auto">
        <div class="overflow-hidden">
          <div ref="slider" class="flex transition-transform duration-500 pb-10 ease-in-out">
            <!-- Динамическое создание карточек -->
            <div v-for="(testimonial, index) in testimonials" :key="index" class="min-w-full px-4">
              <blockquote
                class="bg-white p-8 shadow-lg rounded-lg border border-gray-100 relative h-full flex flex-col">
                <svg class="absolute top-6 left-6 w-8 h-8 text-blue-100" fill="currentColor" viewBox="0 0 24 24">
                  <path
                    d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                </svg>
                <p class="text-lg italic text-gray-700 mb-4 pl-12 relative flex-grow">
                  «{{ testimonial.text }}»
                </p>
                <footer class="text-gray-900 font-medium flex justify-end items-center mt-auto">
                  {{ testimonial.author }}
                </footer>
              </blockquote>
            </div>
          </div>
        </div>

        <!-- Навигация -->
        <div class="flex justify-center mt-4 space-x-3">
          <button v-for="(_, index) in testimonials.length" :key="index" @click="goToSlide(index)"
            class="w-8 h-1 rounded-full transition-colors relative"
            :class="{ 'bg-blue-600': currentSlide === index, 'bg-gray-300': currentSlide !== index }"
            aria-label="Перейти к отзыву">
            <span class="sr-only">Отзыв {{ index + 1 }}</span>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const testimonials = ref([
  {
    text: 'Очень внимательные врачи, современное оборудование. Записался через приложение без очереди. Особенно понравился невролог - профессиональный подход',
    author: 'Анна К.'
  },
  {
    text: 'Привел ребенка на прием к педиатру. Очень понравилось отношение к маленьким пациентам - без страха и слез. Теперь только сюда!',
    author: 'Дмитрий С.'
  },
  {
    text: 'Проходила комплексное обследование. Все организовано четко, без ожидания. Результаты прислали на email, с подробными комментариями врача',
    author: 'Елена П.'
  },
  {
    text: 'Отличный сервис и квалифицированные специалисты. Всегда нахожу здесь решение своих проблем со здоровьем',
    author: 'Иван М.'
  },
  {
    text: 'Удобное расположение, приветливый персонал. Никогда не было проблем с записью на удобное время',
    author: 'Ольга В.'
  },
  {
    text: 'Рекомендую этот медицинский центр всем знакомым. Качественное обслуживание и индивидуальный подход',
    author: 'Сергей Н.'
  },
]);

const slider = ref(null);
const currentSlide = ref(0);
let slideInterval;

const goToSlide = (index) => {
  currentSlide.value = index;
  if (slider.value) {
    slider.value.style.transform = `translateX(-${index * 100}%)`;
  }
};

const startSlider = () => {
  slideInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % testimonials.value.length;
    goToSlide(currentSlide.value);
  }, 6000);
};

onMounted(() => {
  startSlider();
});

onUnmounted(() => {
  clearInterval(slideInterval);
});
</script>

<style scoped>
@media (min-width: 480px) and (max-width: 520px) {
  .title-with-underline::before {
    width: 43%;
  }
}

@media (min-width: 520px) and (max-width: 560px) {
  .title-with-underline::before {
    width: 39%;
  }
}

@media (min-width: 560px) and (max-width: 600px) {
  .title-with-underline::before {
    width: 36%;
  }
}

@media (min-width: 600px) and (max-width: 640px) {
  .title-with-underline::before {
    width: 34%;
  }
}
</style>