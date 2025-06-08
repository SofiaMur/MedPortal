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
    <Head title="Медицинский портал - акции и спецпредложения" />

    <Header :darkMode="darkMode" :selectedLanguage="selectedLanguage" :languages="languages" :t="t"
        @toggleDarkMode="toggleDarkMode" @changeLanguage="changeLanguage" />
    <ScrollToTop />

    <main class="min-h-screen bg-gradient-to-br from-blue-50 to-white">

        <div class="bg-blue-700">
            <div class="max-w-7xl mx-auto px-4 py-16 sm:px-6 lg:px-8 text-center">
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Специальные предложения для вас
                </h1>
                <p class="mt-6 max-w-3xl mx-auto text-xl text-blue-100">
                    Воспользуйтесь выгодными акциями и сэкономьте на медицинских услугах
                </p>
            </div>
        </div>

        <!-- Текущие акции -->
        <div class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Текущие акции</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Выгодные предложения
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Мы регулярно обновляем специальные условия для наших пациентов
                    </p>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Акция 1 - Консультация терапевта -->
                    <div
                        class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                        <div class="relative">
                            <div
                                class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold absolute top-4 right-4 z-10">
                                -20%
                            </div>
                            <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&h=300&q=80"
                                alt="Консультация терапевта" class="w-full h-48 object-cover">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                До 30.06.2025
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Консультация терапевта</h3>
                            <p class="mt-3 text-base text-gray-600">
                                Только в этом месяце специальное предложение для наших постоянных клиентов
                            </p>
                            <div class="mt-6">
                                <Link href="#"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
                                Записаться
                                <svg class="ml-2 -mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Акция 2 - Комплексная диагностика -->
                    <div
                        class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                        <div class="relative">
                            <div
                                class="bg-purple-600 text-white px-3 py-1 rounded-full text-sm font-bold absolute top-4 right-4 z-10">
                                -30%
                            </div>
                            <img src="https://images.unsplash.com/photo-1530026186672-2cd00ffc50fe?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&h=300&q=80"
                                alt="Комплексная диагностика" class="w-full h-48 object-cover">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                До 15.07.2025
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Комплексная диагностика</h3>
                            <p class="mt-3 text-base text-gray-600">
                                Полное обследование организма со скидкой для новых пациентов<br><br>
                            </p>
                            <div class="mt-6">
                                <Link href="#"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 transition-colors">
                                Записаться
                                <svg class="ml-2 -mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Новинка - Цифровая медицинская карта -->
                    <div
                        class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-all duration-300">
                        <div class="relative">
                            <div
                                class="bg-green-600 text-white px-3 py-1 rounded-full text-sm font-bold absolute top-4 left-4 z-10">
                                Новинка
                            </div>
                            <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&h=300&q=80"
                                alt="Цифровая медицинская карта" class="w-full h-48 object-cover">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                Доступно онлайн
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Цифровая медицинская карта</h3>
                            <p class="mt-3 text-base text-gray-600">
                                Полный переход на электронные карты - вся Ваша медицинская история всегда под рукой в
                                удобном формате
                            </p>
                            <div class="mt-6">
                                <Link href="#"
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                                Подробнее
                                <svg class="ml-2 -mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2">
                        <div class="p-8 sm:p-10 lg:p-12 bg-gradient-to-r from-blue-600 to-blue-700">
                            <h2 class="text-2xl font-bold text-white">
                                Подпишитесь на рассылку
                            </h2>
                            <p class="mt-4 text-blue-100">
                                Получайте информацию о новых акциях и специальных предложениях первыми
                            </p>
                            <div class="mt-8">
                                <form class="space-y-4">
                                    <div>
                                        <label for="email" class="sr-only">Email</label>
                                        <input id="email" name="email" type="email" required
                                            class="w-full px-5 py-3 border border-transparent rounded-md shadow-sm text-base placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400 bg-blue-500 text-white"
                                            placeholder="Ваш email">
                                    </div>
                                    <div>
                                        <button type="submit"
                                            class="w-full flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                                            Подписаться
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="p-8 sm:p-10 lg:p-12 bg-white">
                            <h2 class="text-2xl font-bold text-gray-900">
                                Программа лояльности
                            </h2>
                            <p class="mt-4 text-gray-600">
                                Станьте участником программы лояльности и получайте дополнительные скидки и бонусы
                            </p>
                            <div class="mt-8 space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-base text-gray-700">
                                        Накопительные скидки до 20%
                                    </p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-base text-gray-700">
                                        Персональные предложения
                                    </p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <p class="ml-3 text-base text-gray-700">
                                        Приоритетная запись к специалистам
                                    </p>
                                </div>
                            </div>
                            <div class="mt-8">
                                <Link href="#" class="text-base font-medium text-blue-600 hover:text-blue-500">
                                Узнать больше о программе →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Дополнительные прошедшие акции -->
        <div class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Прошедшие акции</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Возможно, они вернутся снова
                    </p>
                    <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
                        Следите за нашими новостями, чтобы не пропустить повторение акций
                    </p>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-200">
                        <div class="relative">
                            <div
                                class="bg-gray-600 text-white px-3 py-1 rounded-full text-sm font-bold absolute top-4 right-4 z-10">
                                Завершено
                            </div>
                            <img src="https://images.unsplash.com/photo-1581595219315-a187dd40c322?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&h=300&q=80"
                                alt="Годовое комплексное обследование"
                                class="w-full h-48 object-cover grayscale opacity-90">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                01.05.2025 - 31.05.2025
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Годовое комплексное обследование</h3>
                            <p class="mt-3 text-base text-gray-600">
                                Годовое медицинское сопровождение с фиксированной стоимостью
                            </p>
                            <div class="mt-4 flex items-center">
                                <span class="text-2xl font-bold text-gray-900">25 000 ₽</span>
                                <span class="ml-2 text-lg text-gray-500 line-through">30 000 ₽</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-200">
                        <div class="relative">
                            <div
                                class="bg-gray-600 text-white px-3 py-1 rounded-full text-sm font-bold absolute top-4 right-4 z-10">
                                Завершено
                            </div>
                            <img src="https://images.unsplash.com/photo-1505751172876-fa1923c5c528?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&h=300&q=80"
                                alt="Семейная стоматология" class="w-full h-48 object-cover grayscale opacity-90">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                01.03.2025 - 15.03.2025
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Семейная стоматология</h3>
                            <p class="mt-3 text-base text-gray-600">
                                Скидка 20% на все стоматологические услуги для всей семьи
                            </p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow overflow-hidden border border-gray-200">
                        <div class="relative">
                            <div
                                class="bg-gray-600 text-white px-3 py-1 rounded-full text-sm font-bold absolute top-4 right-4 z-10">
                                Завершено
                            </div>
                            <img src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&h=300&q=80"
                                alt="Детская вакцинация" class="w-full h-48 object-cover grayscale opacity-90">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                01.04.2025 - 30.04.2025
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900">Детская вакцинация</h3>
                            <p class="mt-3 text-base text-gray-600">
                                Бесплатный осмотр педиатра при проведении плановой вакцинации
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <Footer :t="t" />
</template>