<script setup>
import { ref, computed, inject } from 'vue';
import { Link } from '@inertiajs/vue3';
import TopBar from '@/Components/App/TopBar.vue';

const props = defineProps({
    darkMode: Boolean,
    selectedLanguage: String,
    languages: Array,
    t: Function,
    auth: Object
});

const emits = defineEmits(['toggleDarkMode', 'changeLanguage']);

const mobileMenuOpen = ref(false);

const isLanguageMenuOpen = ref(false);
const currentLanguage = computed(() => {
    return props.languages.find(lang => lang.code === props.selectedLanguage) || props.languages[0];
});

const mapSection = inject('mapSectionRef', null);

const scrollToMap = () => {
    if (mapSection?.value) {
        mapSection.value.scrollIntoView({ behavior: 'smooth' });
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

function toggleMobileMenu() {
    mobileMenuOpen.value = !mobileMenuOpen.value;
}

function changeLanguage(langCode) {
    emits('changeLanguage', langCode);
    isLanguageMenuOpen.value = false;
}

function handleLanguageChange(event) {
    changeLanguage(event.target.value);
}
</script>


<style scoped>
.dark {
    --bg-color: #1a202c;
    --text-color: #e2e8f0;
    --header-bg: #2d3748;
    --border-color: #4a5568;
}

.dark header {
    background-color: var(--header-bg);
    border-color: var(--border-color);
}

.dark .bg-white {
    background-color: var(--header-bg);
}

.dark .text-gray-700,
.dark .text-gray-500,
.dark .text-gray-900 {
    color: var(--text-color);
}

.dark .shadow-md {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
}

.dark .border-gray-100 {
    border-color: var(--border-color);
}

.mobile-menu {
    position: fixed;
    top: 42px;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: -2;
    overflow-y: auto;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    background: white;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

header {
    position: relative;
    z-index: 50;
    position: sticky;
}

.mobile-menu.open {
    transform: translateY(0);
}

@media (max-width: 640px) {
    .mobile-menu {
        top: 48px;
    }
}
</style>

<template>
    <header class="bg-white shadow-md sticky md:static top-0 z-50" :class="{ 'dark': darkMode }">
        <!-- Верхняя полоса с контактами и утилитами -->
        <TopBar :dark-mode="darkMode" :selected-language="selectedLanguage" :languages="languages" :t="t"
            :map-section-ref="mapSection" @toggle-dark-mode="$emit('toggleDarkMode')"
            @change-language="$emit('changeLanguage', $event)" />

        <!-- Основная навигация -->
        <div class="max-w-7xl mx-auto px-4 py-3 sm:px-6 lg:px-8 flex justify-between items-center">
            <!-- Лого -->
            <a href="/" class="flex items-center space-x-6">
                <span class="md:text-base lg:text-xl font-bold text-gray-900">MedPortal</span>
            </a>

            <!-- Навигация -->
            <nav class="hidden md:flex md:space-x-2 md:text-sm lg:space-x-6 lg:text-base font-medium">
                <Link :href="route('dashboard')" class="text-gray-500 hover:text-blue-600 px-2 py-1 rounded"
                    :class="{ 'text-blue-600': route().current('dashboard') }">
                Личный профиль
                </Link>
                <Link v-if="auth.user?.is_doctor" :href="route('doctor.dashboard')"
                    class="text-gray-500 hover:text-blue-600 px-2 py-1 rounded"
                    :class="{ 'text-blue-600': route().current('doctor.dashboard') }">
                Панель врача
                </Link>
                <Link :href="route('profile.edit')" class="text-gray-500 hover:text-blue-600 px-2 py-1 rounded"
                    :class="{ 'text-blue-600': route().current('profile.edit') }">
                Редактировать данные
                </Link>
                <Link :href="route('profile.security')" class="text-gray-500 hover:text-blue-600 px-2 py-1 rounded"
                    :class="{ 'text-blue-600': route().current('profile.update') }">
                Безопасность
                </Link>
            </nav>

            <div class="flex items-center md:space-x-2 lg:space-x-4">
                <!-- Выход -->
                <Link :href="route('logout')" method="post" as="button"
                    class="md:text-sm lg:text-base ml-3 px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 font-medium md:block hidden sm:inline">
                Выйти
                </Link>

                <!-- Мобильное меню (бургер) -->
                <button @click="toggleMobileMenu"
                    class="ml-4 md:hidden text-gray-500 hover:text-blue-600 focus:outline-none">
                    <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Мобильное меню -->
        <div class="mobile-menu md:hidden bg-white dark:bg-gray-800" :class="{ 'open': mobileMenuOpen }">
            <!-- Навигация -->
            <div class="px-4 py-2 space-y-2">
                <Link :href="route('dashboard')"
                    class="block px-2 py-2 text-gray-900 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-700 text-center rounded"
                    :class="{ 'text-blue-600': route().current('dashboard') }">
                Личный профиль
                </Link>
                <Link v-if="auth.user?.is_doctor" :href="route('doctor.dashboard')"
                    class="block px-2 py-2 text-gray-900 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-700 text-center rounded"
                    :class="{ 'text-blue-600': route().current('doctor.dashboard') }">
                Панель врача
                </Link>
                <Link :href="route('profile.edit')"
                    class="block px-2 py-2 text-gray-900 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-700 text-center rounded"
                    :class="{ 'text-blue-600': route().current('profile.edit') }">
                Редактировать данные
                </Link>
                <Link :href="route('profile.security')"
                    class="block px-2 py-2 text-gray-900 dark:text-gray-100 hover:bg-blue-50 dark:hover:bg-gray-700 text-center rounded"
                    :class="{ 'text-blue-600': route().current('profile.update') }">
                Безопасность
                </Link>
            </div>

            <!-- Выход -->
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:hidden">
                <Link :href="route('logout')" method="post" as="button"
                    class="block w-full px-2 py-2 bg-gray-600 text-white hover:bg-gray-700 rounded text-center">
                Выйти</Link>
            </div>

            <!-- Контактная информация и утилиты -->
            <div
                class="px-4 py-3 bg-white border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <!-- Контактная информация -->
                <div class="mb-3 space-y-2">
                    <div class="flex justify-center items-center text-gray-700 dark:text-gray-300 text-sm">
                        <!-- Телефон -->
                        <div class="flex items-center hover:text-blue-600 transition-colors important">
                            <svg class="h-4 w-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                            <a href="tel:+79081234567" class="mr-3">{{ t('header.phone') || '+7 (495) 123-45-67' }}</a>
                        </div>

                        <!-- Время работы -->
                        <div class="flex items-center important">
                            <svg class="h-4 w-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>{{ t('header.working_hours') || 'Пн-Пт: 8:00-20:00' }}</span>
                        </div>
                    </div>

                    <!-- Адрес -->
                    <a href="#" @click.prevent="scrollToMap"
                        class="flex justify-center text-sm items-center text-gray-700 hover:text-blue-600 transition-colors dark:text-gray-300">
                        <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>
                            {{ t('header.address') || 'г. Великий Новгород, ул. Медицинская, 15' }}
                        </span>
                    </a>
                </div>

                <!-- Утилиты -->
                <div class="flex flex-wrap gap-3 justify-center">
                    <!-- Выбор языка -->
                    <div class="relative flex items-center hover:text-blue-600 text-gray-700 dark:text-gray-300">
                        <svg class="h-4 w-4 mr-1 absolute left-2 pointer-events-none" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM4.332 8.027a6.012 6.012 0 011.912-2.706C6.512 5.73 6.974 6 7.5 6A1.5 1.5 0 019 7.5V8a2 2 0 004 0 2 2 0 011.523-1.943A5.977 5.977 0 0116 10c0 .34-.028.675-.083 1H15a2 2 0 00-2 2v2.197A5.973 5.973 0 0110 16v-2a2 2 0 00-2-2 2 2 0 01-2-2 2 2 0 00-1.668-1.973z"
                                clip-rule="evenodd" />
                        </svg>

                        <select :value="selectedLanguage" @change="handleLanguageChange($event)"
                            class="appearance-none bg-transparent px-8 py-1 text-sm border-transparent hover:text-blue-600 focus:outline-none">
                            <option v-for="lang in languages" :key="lang.code" :value="lang.code"
                                class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                {{ lang.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Версия для слабовидящих -->
                    <button
                        class="flex items-center text-gray-700 dark:text-gray-300 text-sm p-1 rounded hover:text-blue-600 focus:outline-none">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd"
                                d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Смена темы -->
                    <button @click="toggleDarkMode"
                        class="flex items-center text-gray-700 dark:text-gray-300 text-sm p-1 rounded hover:text-blue-600 focus:outline-none">
                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path v-if="darkMode" fill-rule="evenodd"
                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
                                clip-rule="evenodd" />
                            <path v-else d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>