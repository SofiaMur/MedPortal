<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, inject, watch, onMounted } from 'vue';
import Header from '@/Components/App/Header.vue';
import Footer from '@/Components/App/Footer.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

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
    <Head title="Медицинский портал - наши услуги" />

    <Header :darkMode="darkMode" :selectedLanguage="selectedLanguage" :languages="languages" :t="t"
        @toggleDarkMode="toggleDarkMode" @changeLanguage="changeLanguage" />
    <ScrollToTop />

    <main class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
        <div v-if="$page.props.auth.user">
        </div>

        <div v-else class="bg-blue-700">
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
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Наши услуги</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Полный спектр медицинских услуг
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Мы предлагаем комплексное медицинское обслуживание с использованием современных технологий
                    </p>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Профилактика -->
                    <div
                        class="group relative bg-blue-50 p-6 rounded-lg shadow-md border border-blue-100 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-5">
                            <h3 class="text-lg font-medium text-gray-900">Профилактика</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Программы профилактики и укрепления здоровья для всех возрастных групп
                            </p>
                            <div class="mt-4">
                                <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                                    <li>Диспансеризация</li>
                                    <li>Вакцинация</li>
                                    <li>Программы ЗОЖ</li>
                                    <li>Профилактические осмотры</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Лечение -->
                    <div
                        class="group relative bg-blue-50 p-6 rounded-lg shadow-md border border-blue-100 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                        </div>
                        <div class="mt-5">
                            <h3 class="text-lg font-medium text-gray-900">Лечение</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Эффективные методы лечения с индивидуальным подходом к каждому пациенту
                            </p>
                            <div class="mt-4">
                                <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                                    <li>Амбулаторное лечение</li>
                                    <li>Физиотерапия</li>
                                    <li>Массаж и ЛФК</li>
                                    <li>Мануальная терапия</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Диагностика -->
                    <div
                        class="group relative bg-blue-50 p-6 rounded-lg shadow-md border border-blue-100 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div class="mt-5">
                            <h3 class="text-lg font-medium text-gray-900">Диагностика</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Полный комплекс лабораторных и инструментальных исследований для точной диагностики
                            </p>
                            <div class="mt-4">
                                <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                                    <li>УЗИ диагностика</li>
                                    <li>Эндоскопия</li>
                                    <li>Лабораторные анализы</li>
                                    <li>Функциональная диагностика</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Реабилитация -->
                    <div
                        class="group relative bg-blue-50 p-6 rounded-lg shadow-md border border-blue-100 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div class="mt-5">
                            <h3 class="text-lg font-medium text-gray-900">Реабилитация</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Комплексные программы восстановления после заболеваний и травм
                            </p>
                            <div class="mt-4">
                                <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                                    <li>Постоперационная реабилитация</li>
                                    <li>Неврологическая реабилитация</li>
                                    <li>Кардиореабилитация</li>
                                    <li>Спортивная реабилитация</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Стоматология -->
                    <div
                        class="group relative bg-blue-50 p-6 rounded-lg shadow-md border border-blue-100 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </div>
                        <div class="mt-5">
                            <h3 class="text-lg font-medium text-gray-900">Стоматология</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Комплексный уход за полостью рта с использованием современных технологий
                            </p>
                            <div class="mt-4">
                                <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                                    <li>Терапевтическая стоматология</li>
                                    <li>Ортодонтия</li>
                                    <li>Имплантация</li>
                                    <li>Отбеливание зубов</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Педиатрия -->
                    <div
                        class="group relative bg-blue-50 p-6 rounded-lg shadow-md border border-blue-100 hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center justify-center h-12 w-12 rounded-md bg-blue-500 text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="mt-5">
                            <h3 class="text-lg font-medium text-gray-900">Педиатрия</h3>
                            <p class="mt-2 text-base text-gray-500">
                                Забота о здоровье вашего ребенка с первых дней жизни и до совершеннолетия
                            </p>
                            <div class="mt-4">
                                <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                                    <li>Плановые осмотры</li>
                                    <li>Вакцинация детей</li>
                                    <li>Лечение детских заболеваний</li>
                                    <li>Консультации по развитию</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="py-12 bg-blue-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Узкопрофильные направления
                    </p>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <div class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-shadow">
                        <div class="mx-auto h-12 w-12 text-blue-600">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Кардиология</h3>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-shadow">
                        <div class="mx-auto h-12 w-12 text-blue-600">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Неврология</h3>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-shadow">
                        <div class="mx-auto h-12 w-12 text-blue-600">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Эндокринология</h3>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-shadow">
                        <div class="mx-auto h-12 w-12 text-blue-600">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Гастроэнтерология</h3>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-shadow">
                        <div class="mx-auto h-12 w-12 text-blue-600">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Пульмонология</h3>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow text-center hover:shadow-md transition-shadow">
                        <div class="mx-auto h-12 w-12 text-blue-600">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Офтальмология</h3>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <Footer :t="t" />
</template>