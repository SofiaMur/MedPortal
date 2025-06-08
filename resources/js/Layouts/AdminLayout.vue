<script setup>
import { inject, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  auth: Object,
});

const translation = inject('translation');
const selectedLanguage = ref(localStorage.getItem('selectedLanguage') || 'ru');

const t = (key) => translation.t(key);

const languages = [
    { name: 'Русский', code: 'ru' },
    { name: 'English', code: 'en' }
];

const changeLanguage = async (langCode) => {
    if (selectedLanguage.value === langCode) return;
    await translation.setLocale(langCode);
    isLanguageMenuOpen.value = false;
    selectedLanguage.value = langCode;
    localStorage.setItem('selectedLanguage', langCode);
};

const mobileMenuOpen = ref(false);

const darkMode = inject('darkMode');

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

watch(() => translation.state.locale, () => {
    document.title = t('app.title');
});
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Боковое меню начало -->
        <button @click="toggleMobileMenu"
            class="xl:hidden fixed left-4 top-3 z-[60] bg-[#fafafa] dark:bg-gray-700 dark:hover:bg-gray-600 rounded p-2 shadow transition-colors duration-200"
            :class="{
                'bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500': mobileMenuOpen,
                'text-gray-800 dark:text-gray-200': !mobileMenuOpen,
            }">
            <svg class="h-6 w-6 text-gray-800 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Боковая панель -->
        <div class="fixed left-0 border-r border-gray-200 dark:border-gray-600/20 top-0 w-64 h-screen bg-[#fafafa] dark:bg-gray-800 shadow-md flex flex-col z-50 transition-transform duration-300 ease-in-out xl:translate-x-0"
            :class="{ '-translate-x-full': !mobileMenuOpen }">
            <div class="p-4 border-b border-gray-200 dark:border-gray-600/20">
                <Link v-if="auth.user?.is_admin" href="/" class="flex items-center justify-center">
                <span class="text-xl font-bold text-gray-900 dark:text-gray-100">MedPortal</span>
                </Link>
                <Link v-else :href="route('registrar.dashboard')" class="flex items-center justify-center">
                <span class="text-xl font-bold text-gray-900 dark:text-gray-100">MedPortal</span>
                </Link>
            </div>

            <div class="flex-1 overflow-y-auto p-4">
                <nav class="flex flex-col gap-2 mt-4">
                    <Link v-if="auth.user?.is_admin" :href="route('registrar.dashboard')"
                        class="text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 hover:text-blue-600 dark:hover:text-blue-300 rounded"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('registrar.dashboard') }">
                    Список врачей
                    </Link>
                    <Link v-if="auth.user?.is_admin" :href="route('registrar.dashboard')"
                        class="text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 hover:text-blue-600 dark:hover:text-blue-300 rounded"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('registrar.dashboard') }">
                    Список пользователей
                    </Link>
                    <Link :href="route('registrar.dashboard')"
                        class="text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 hover:text-blue-600 dark:hover:text-blue-300 rounded"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('registrar.dashboard') }">
                    Назначение на прием
                    </Link>
                    <Link :href="route('registrar.dashboard')"
                        class="text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 hover:text-blue-600 dark:hover:text-blue-300 rounded"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('registrar.dashboard') }">
                    Расписание
                    </Link>
                    <Link :href="route('registrar.dashboard')"
                        class="text-gray-800 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 px-3 py-2 hover:text-blue-600 dark:hover:text-blue-300 rounded"
                        :class="{ 'bg-gray-100 dark:bg-gray-700': route().current('') }">
                    Медицинское заключение
                    </Link>
                </nav>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-600/20 mt-auto">
                <!-- Выход -->
                <Link :href="route('logout')" method="post" as="button"
                    class="w-full px-4 py-2 bg-gray-700 text-[#fafafa] rounded-md hover:bg-gray-800 dark:hover:bg-gray-600 font-medium text-center">
                Выйти
                </Link>

                <!-- Контактная информация -->
                <div class="mt-4 space-y-2 text-sm">
                    <a href="tel:+79081234567"
                        class="flex items-center text-gray-800 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-300">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                        </svg>
                        <span>+7 (495) 123-45-67</span>
                    </a>

                    <div class="flex items-center text-gray-800 dark:text-gray-300">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Пн-Пт: 8:00-20:00</span>
                    </div>

                    <div class="flex items-center text-gray-800 dark:text-gray-300">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>г. Великий Новгород, ул. Медицинская, 15</span>
                    </div>
                </div>

                <!-- Утилиты -->
                <div class="flex justify-center gap-3 mt-4">
                    <!-- Выбор языка -->
                    <div class="relative flex items-center">
                        <select :value="selectedLanguage" @change="changeLanguage($event.target.value)"
                            class="appearance-none font-md bg-[#fafafa] hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 px-6 py-1 pr-8 text-sm border rounded border-gray-300 dark:border-gray-600/20 text-gray-800 dark:text-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-blue-300">
                            <option v-for="lang in languages" :key="lang.code" :value="lang.code"
                                class="bg-[#fafafa] font-md dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                {{ lang.name }}
                            </option>
                        </select>

                    </div>

                    <!-- Смена темы -->
                    <button @click="darkMode.toggle()" class="p-1 rounded hover:bg-gray-200 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path v-if="darkMode.value.value" class="text-[#fafafa]" fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                clip-rule="evenodd" />
                            <path v-else class="text-gray-800"
                                d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Оверлей для мобильного меню -->
        <transition enter-active-class="transition-opacity duration-200"
            leave-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
            leave-to-class="opacity-0">
            <div v-if="mobileMenuOpen" @click="toggleMobileMenu"
                class="fixed inset-0 bg-[#333A3F] bg-opacity-50 z-40 md:hidden"></div>
        </transition>
        <!-- Боковое меню конец -->

        <!-- Основное содержимое -->
        <div class="flex-1 flex flex-col">
            <main class="flex-1 bg-blue-50 dark:bg-gray-600 transition-colors duration-300">
                <div class="xl:py-8 xl:pl-60">
                    <div class="mx-auto bg-[#fafafa] dark:bg-gray-800 xl:rounded-lg max-w-7xl pb-6 xl:px-0 px-4">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>