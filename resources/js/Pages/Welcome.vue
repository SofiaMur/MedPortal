<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { inject, ref, onMounted, watch, onUnmounted, provide, defineExpose } from 'vue';
import Header from '@/Components/App/Header.vue';
import Footer from '@/Components/App/Footer.vue';
import HeroSection from '@/Components/App/Sections/HeroSection.vue';
import ServicesSection from '@/Components/App/Sections/ServicesSection.vue';
import PromotionsSection from '@/Components/App/Sections/PromotionsSection.vue';
import ReviewsSection from '@/Components/App/Sections/ReviewsSection.vue';
import DoctorsSection from '@/Components/App/Sections/DoctorsSection.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const darkMode = ref(localStorage.getItem('darkMode') === 'true');

const translation = inject('translation');
const selectedLanguage = ref(localStorage.getItem('selectedLanguage') || 'ru');
const showLanguageChangeNotice = ref(false);
const t = (key) => translation.t(key);

const languages = [
    { name: 'Русский', code: 'ru' },
    { name: 'English', code: 'en' }
];

const mapSectionRef = ref(null);

provide('mapSectionRef', mapSectionRef);

const mapContainer = ref(null);
const isMapLoaded = ref(false);
let observer = null;
let map = ref(null);

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

    showLanguageChangeNotice.value = true;
};

const initObserver = () => {
    observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !isMapLoaded.value) {
                loadMap();
                observer.unobserve(entry.target); 
            }
        });
    }, {
        threshold: 0.1 
    });

    if (mapContainer.value) {
        observer.observe(mapContainer.value);
    }
};

const initYandexMap = () => {
    ymaps.ready(() => {
        if (!mapContainer.value) return;

        map.value = new ymaps.Map(mapContainer.value, {
            center: [58.5420250, 31.2659636],
            zoom: 16,
            controls: ['zoomControl'],
            suppressObsoleteBrowserNotifier: true
        });

        const myPlacemark = new ymaps.Placemark([58.5417250, 31.2643636], {
            hintContent: selectedLanguage.value === 'ru'
                ? 'Медицинский центр "MedPortal"'
                : 'Medical Center "MedPortal"',
            balloonContent: selectedLanguage.value === 'ru'
                ? 'г. Великий Новгород, ул. Медицинская, д. 15'
                : 'Velikiy Novgorod, Medical St., 15'
        }, {
            iconLayout: 'default#image',
            iconImageHref: 'https://cdn1.iconfinder.com/data/icons/social-messaging-ui-color/254000/66-512.png',
            iconImageSize: [44, 44],
            iconImageOffset: [-20, -48],
            balloonPanelMaxMapArea: 0,
            hideIconOnBalloonOpen: false,
            balloonCloseButton: true,
            balloonOffset: [0, -40],
            balloonPanelMaxMapArea: Infinity,
            balloonAutoPan: false,
            balloonCursor: 'pointer'
        });

        map.value.geoObjects.add(myPlacemark);
    });
};

const loadYandexMaps = () => {
    return new Promise((resolve, reject) => {
        if (window.ymaps) return resolve();

        const script = document.createElement('script');
        //   script.src = `https://api-maps.yandex.ru/2.1/?apikey=${import.meta.env.VITE_YANDEX_MAPS_API_KEY}&lang=${selectedLanguage.value}_RU`;
        script.src = `https://api-maps.yandex.ru/2.1/?lang=${selectedLanguage.value}_RU&apikey=demo&load=package.full`;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Не удалось загрузить Яндекс-карту'));
        document.head.appendChild(script);
    });
};

const loadMap = async () => {
    try {
        await initApp();
        isMapLoaded.value = true;
    } catch (error) {
        console.error('Ошибка загрузки карты:', error);
    }
};

watch(darkMode, (newVal) => {
    localStorage.setItem('darkMode', newVal);
    updateTheme();
}, { immediate: true });

watch(() => translation.state.locale, () => {
    document.title = t('app.title');
});

async function initApp() {
    try {
        await loadYandexMaps();
        initYandexMap();
    } catch (err) {
        console.error('Не удалось загрузить Яндекс-карту:', err);
        const mapContainer = document.getElementById('map-container');
        if (mapContainer) {
            mapContainer.innerHTML = '<p>Карта временно недоступна. Попробуйте позже.</p>';
        }
    }
}

onMounted(() => {
    updateTheme();
    if (!('IntersectionObserver' in window)) {
        loadMap(); // Грузим карту сразу, если Observer не поддерживается
    } else {
        initObserver();
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
    if (map.value) {
        map.value.destroy();
        map.value = null;
    }
});
</script>

<template>

    <Head title="Медицинский портал" />

    <Header :darkMode="darkMode" :selectedLanguage="selectedLanguage" :languages="languages" :t="t"
        @toggleDarkMode="toggleDarkMode" @changeLanguage="changeLanguage" />
    <ScrollToTop />

    <main class="min-h-screen bg-blue-50">
        <HeroSection />

        <ServicesSection />

        <div v-if="$page.props.auth.user" class="bg-blue-700 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:text-center">
                    <h2 class="text-base text-white font-semibold tracking-wide uppercase">Запись на прием</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                        Выберите удобное время
                    </p>
                </div>

                <div class="mt-10 sm:mt-12">
                    <form class="max-w-2xl mx-auto bg-blue-50 p-8 rounded-lg shadow">
                        <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="doctor" class="block text-sm font-medium text-gray-700">Специалист</label>
                                <select id="doctor" name="doctor"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4">
                                    <option>Терапевт</option>
                                    <option>Кардиолог</option>
                                    <option>Невролог</option>
                                    <option>Офтальмолог</option>
                                </select>
                            </div>

                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Дата</label>
                                <input type="date" id="date" name="date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4">
                            </div>

                            <div>
                                <label for="time" class="block text-sm font-medium text-gray-700">Время</label>
                                <select id="time" name="time"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4">
                                    <option>09:00</option>
                                    <option>10:00</option>
                                    <option>11:00</option>
                                    <option>12:00</option>
                                    <option>14:00</option>
                                    <option>15:00</option>
                                    <option>16:00</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="reason" class="block text-sm font-medium text-gray-700">Причина
                                    посещения</label>
                                <textarea id="reason" name="reason" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-3 px-4"></textarea>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                class="w-full flex justify-center py-3 px-6 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-gray-900 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Записаться на прием
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <DoctorsSection />

        <!-- Секция карты -->
        <section ref="mapSectionRef" class="relative text-white overflow-hidden">
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="md:text-center mb-6 lg:mb-8">
                    <h2 class="text-2xl sm:text-3xl md:text-2xl lg:text-3xl font-extrabold text-gray-900">
                        Расположение нашего медицинского учреждения
                    </h2>
                    <p class="mt-2 text-lg sm:text-xl md:text-lg lg:text-xl text-gray-600 md:mx-auto">
                        Мы создали все условия для Вашего комфортного визита
                    </p>
                </div>

                <div class="relative">
                    <!-- Яндекс.Карта -->
                    <div ref="mapContainer" id="map"
                        class="w-full h-64 md:h-[500px] bg-gray-800 rounded-t-sm md:rounded-md border-b md:border-4 md:shadow-xl md:border-blue-300">
                    </div>

                    <!-- Уведомление о смене языка -->
                    <div v-if="showLanguageChangeNotice"
                        class="absolute inset-0 bg-black/70 rounded-lg flex items-center justify-center z-20 backdrop-blur-sm">
                        <div class="bg-white rounded-md p-6 max-w-md mx-4 border border-gray-200 shadow-2xl">
                            <h3 class="text-lg sm:text-xl md:text-lg lg:text-xl font-bold text-gray-900 mb-4">
                                {{ selectedLanguage === 'ru' ? 'Язык изменён' : 'Language changed' }}
                            </h3>
                            <p class="text-gray-700 text-md sm:text-lg md:text-md lg:text-lg mb-4">
                                {{ selectedLanguage === 'ru'
                                    ? 'Для применения изменений обновите страницу'
                                    : 'Please refresh the page to apply changes' }}
                            </p>
                            <div class="flex justify-end space-x-3">
                                <button @click="showLanguageChangeNotice = false"
                                    class="px-4 py-2 text-md sm:text-lg md:text-md lg:text-lg text-gray-700 hover:bg-gray-100 rounded transition-colors">
                                    {{ selectedLanguage === 'ru' ? 'Позже' : 'Later' }}
                                </button>
                                <button @click="window.location.reload()"
                                    class="px-4 py-2 text-md sm:text-lg md:text-md lg:text-lg bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                    {{ selectedLanguage === 'ru' ? 'Обновить' : 'Refresh' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Блок контактов (под картой на мобильных, поверх на десктопах) -->
                    <div
                        class="w-full md:absolute md:right-4 md:top-4 md:w-auto bg-white rounded-b-lg md:rounded-lg md:shadow-2xl shadow-xl border-t-0 md:border border-gray-200/50">
                        <div class="p-4 md:p-6">
                            <div class="flex items-start">
                                <div>
                                    <h3 class="text-md sm:text-lg md:text-md lg:text-lg font-bold text-gray-900">
                                        Медицинский центр
                                        "MedPortal"</h3>
                                    <div
                                        class="mt-1 text-xs sm:text-sm md:text-xs lg:text-sm text-blue-600 font-medium">
                                        Центральный филиал
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 md:mt-5 space-y-2 md:space-y-3">
                                <p class="flex items-start text-gray-700 text-sm sm:text-md md:text-sm lg:text-md">
                                    <svg class="h-4 w-4 md:h-5 md:w-5 text-blue-500 mr-1 md:mr-2 mt-0.5 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>г. Великий Новгород, ул. Медицинская, 15</span>
                                </p>
                                <p class="flex items-center text-gray-700 text-sm sm:text-md md:text-sm lg:text-md">
                                    <svg class="h-4 w-4 md:h-5 md:w-5 text-blue-500 mr-1 md:mr-2 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <a href="tel:+79081234567" class="hover:text-blue-600 transition-colors">+7 (908)
                                        123-45-67</a>
                                </p>
                                <p class="flex items-center text-gray-700 text-sm sm:text-md md:text-sm lg:text-md">
                                    <svg class="h-4 w-4 md:h-5 md:w-5 text-blue-500 mr-1 md:mr-2 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Пн-Пт: 8:00-20:00</span>
                                </p>
                            </div>

                            <div class="mt-4 md:mt-6 flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-3">
                                <Link href="/schedule"
                                    class="inline-flex items-center justify-center px-3 py-2 md:px-4 md:py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md hover:shadow-md text-sm sm:text-md md:text-sm lg:text-md">
                                    <svg class="h-4 w-4 md:h-5 md:w-5 mr-1 md:mr-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Записаться на прием
                                </Link>
                                <a href="https://yandex.ru/maps/24/veliky-novgorod/?from=api-maps&ll=31.266364%2C58.542725&mode=routes&origin=jsapi_2_1_79&rtext=~58.541725%2C31.264364&rtt=auto&ruri=~&z=16"
                                    class="inline-flex items-center justify-center px-3 py-2 md:px-4 md:py-2.5 bg-white text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors shadow-sm hover:shadow-sm text-sm sm:text-md md:text-sm lg:text-md">
                                    <svg class="h-4 w-4 md:h-5 md:w-5 mr-1 md:mr-2" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                    </svg>
                                    Маршрут
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <ReviewsSection />

        <PromotionsSection />

    </main>

    <Footer :t="t" />
</template>