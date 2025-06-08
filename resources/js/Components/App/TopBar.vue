<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  darkMode: Boolean,
  selectedLanguage: String,
  languages: Array,
  t: Function,
  mapSectionRef: Object
});

const emits = defineEmits(['toggleDarkMode', 'changeLanguage']);

const isLanguageMenuOpen = ref(false);
const currentLanguage = computed(() => {
  return props.languages.find(lang => lang.code === props.selectedLanguage) || props.languages[0];
});

const scrollToMap = () => {
  if (props.mapSectionRef?.value) {
    props.mapSectionRef.value.scrollIntoView({ behavior: 'smooth' });
  } else {
    console.log('Секция с картой не найдена');
  }
};

function toggleLanguageMenu() {
  isLanguageMenuOpen.value = !isLanguageMenuOpen.value;
}

function toggleDarkMode() {
  emits('toggleDarkMode');
}

function changeLanguage(langCode) {
  emits('changeLanguage', langCode);
  isLanguageMenuOpen.value = false;
}
</script>

<style scoped>
@media (min-width: 768px) and (max-width: 980px) {
    .contact-info {
        display: none;
    }
}

@media (min-width: 1024px) and (max-width: 1080px) {
    .contact-info {
        display: none;
    }
}
</style>

<template>
  <div class="py-2 px-4 border-b border-gray-100 hidden md:block whitespace-nowrap dark:border-gray-700">
    <div class="md:mx-2 lg:mx-4 flex justify-between items-center lg:text-sm md:text-xs">
      <div class="flex space-x-2">
        <!-- Выбор языка -->
        <div class="relative group after:absolute after:-bottom-2 after:left-0 after:right-0 after:h-4">
          <button @click="toggleLanguageMenu"
                  class="flex items-center text-gray-700 hover:text-blue-600 focus:outline-none dark:text-gray-300 dark:hover:text-blue-400">
            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z"
                    clip-rule="evenodd"/>
            </svg>
            {{ currentLanguage.name }}
            <svg class="ml-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"/>
            </svg>
          </button>
          <div v-show="isLanguageMenuOpen"
               class="absolute z-10 bg-white shadow-md rounded-md mt-1 py-1 w-24 dark:bg-gray-800">
            <a v-for="lang in languages" :key="lang.code" href="#"
               @click.prevent="changeLanguage(lang.code)"
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 dark:text-gray-300 dark:hover:bg-gray-700"
               :class="{ 'bg-blue-50 text-blue-600 dark:bg-gray-700 dark:text-blue-400': lang.code === selectedLanguage }">
              {{ lang.name }}
            </a>
          </div>
        </div>

        <!-- Версия для слабовидящих -->
        <button class="flex items-center text-gray-700 hover:text-blue-600 focus:outline-none dark:text-gray-300 dark:hover:text-blue-400">
          <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
            <path fill-rule="evenodd"
                  d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                  clip-rule="evenodd"/>
          </svg>
          <span class="sm:inline">{{ t('header.vision_version') || 'Версия для слабовидящих' }}</span>
        </button>

        <!-- Смена темы -->
        <button @click="toggleDarkMode"
                class="flex items-center text-gray-700 hover:text-blue-600 focus:outline-none ml-2 dark:text-gray-300 dark:hover:text-blue-400">
          <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path v-if="darkMode" fill-rule="evenodd"
                  d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                  clip-rule="evenodd"/>
            <path v-else d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
          </svg>
          <span class="sm:inline">{{ darkMode ? t('header.light_theme') || 'Светлая тема' :
            t('header.dark_theme') || 'Темная тема' }}</span>
        </button>
      </div>

      <!-- Контактная информация -->
      <div class="flex md:space-x-2 lg:space-x-4">
        <!-- Адрес -->
        <a href="#" @click.prevent="scrollToMap"
           class="flex items-center text-gray-700 hover:text-blue-600 transition-colors dark:text-gray-300 dark:hover:text-blue-400">
          <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                  clip-rule="evenodd"/>
          </svg>
          <span class="contact-info">
            {{ t('header.address') || 'г. Великий Новгород, ул. Медицинская, 15' }}
          </span>
        </a>

        <!-- Телефон -->
        <div class="flex items-center text-gray-700 hover:text-blue-600 transition-colors important dark:text-gray-300 dark:hover:text-blue-400">
          <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path
                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
          </svg>
          <a href="tel:+79081234567">{{ t('header.phone') || '+7 (908) 123-45-67' }}</a>
        </div>

        <!-- Время работы -->
        <div class="flex items-center text-gray-700 dark:text-gray-300">
          <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                  clip-rule="evenodd"/>
          </svg>
          {{ t('header.working_hours') || 'Пн-Пт: 8:00-20:00' }}
        </div>
      </div>
    </div>
  </div>
</template>