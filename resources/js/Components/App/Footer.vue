<template>
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
            <div class="flex flex-col md:grid md:grid-cols-12 gap-8 lg:gap-12">
                <!-- Лого и описание -->
                <div class="md:col-span-6 space-y-4">
                    <Link href="/" class="flex items-center">
                        <svg class="h-8 w-8 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                        <h3 class="text-lg sm:text-xl md:text-lg lg:text-xl font-bold text-white">MedPortal</h3>
                    </Link>
                    <p class="text-gray-400 text-sm sm:text-base md:text-sm lg:text-base leading-relaxed">
                        {{ t('footer.description') || 'Мы предлагаем комплексный подход к медицинскому обслуживанию с использованием современных технологий и индивидуального подхода к каждому пациенту.' }}
                    </p>
                    <!-- Социальные сети -->
                    <div class="space-x-4 pt-1 hidden md:flex">
                        <a href="#" class="pt-0.5 text-gray-400 hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                                <path 
                                    d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="flex justify-between gap-8 md:contents">
                    <!-- Контактная информация -->
                    <div class="md:col-span-3 col-span-2 text-sm sm:text-base md:text-sm lg:text-base">
                        <h3 class="text-base sm:text-lg md:text-base lg:text-lg font-semibold text-white">{{ t('footer.contacts') || 'Контакты' }}</h3>
                        <ul class="mt-4 space-y-3">
                            <!-- Адрес -->
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <a href="#" @click.prevent="scrollToMap" class="text-gray-400 hover:text-white transition-colors">{{ t('header.address') || 'г. Великий Новгород,<br>ул. Медицинская, 15' }}</a>
                            </li>
                            <!-- Телефон -->
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a href="tel:+79081234567" class="text-gray-400 hover:text-white transition-colors">
                                    {{ t('header.phone') || '+7 (908) 123-45-67' }}</a>
                            </li>
                            <!-- Почта -->
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <a href="mailto:info@medbook.ru"
                                    class="text-gray-400 hover:text-white transition-colors">info@medportal.ru</a>
                            </li>
                            <!-- Время работы -->
                            <li class="flex items-center">
                                <svg class="h-5 w-5 text-gray-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-gray-400"> {{ t('header.working_hours') || 'Пн-Пт: 8:00-20:00' }}</span>
                            </li>
                        </ul>

                        <!-- Социальные сети -->
                        <!-- <div class="flex space-x-4 pt-6 md:hidden flex">
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition-colors">
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                        </div> -->
                        <div class="flex space-x-4 pt-6 md:hidden flex">
                        <a href="#" class="pt-0.5 text-gray-400 hover:text-white transition-colors">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24">
                                <path 
                                    d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.31-.07-.46-.52-.19L7.74 13.3 3.64 12c-.88-.25-.89-.86.2-1.3l15.97-6.16c.73-.33 1.43.18 1.15 1.3l-2.72 12.81c-.19.91-.74 1.13-1.5.71L12.6 16.3l-1.99 1.93c-.23.23-.42.42-.83.42z"/>
                            </svg>
                        </a>
                    </div>
                    </div>

                    <!-- Подписка -->
                    <div class="max-w-sm md:col-span-3 text-sm sm:text-base md:text-sm lg:text-base">
                        <h3 class="text-base sm:text-lg md:text-base lg:text-lg font-semibold text-white"> {{ t('footer.subscribe') || 'Подписаться' }}
                        </h3>
                        <div class="mt-4 space-y-4">
                            <p class="text-gray-400">
                                {{ t('footer.subscribe_text') || 'Получайте полезные статьи и персональные предложения' }}
                            </p>
                            <form class="flex">
                                <input type="email" v-bind:placeholder="t('footer.email_placeholder') || 'Ваш email'"
                                    class="px-4 py-2 rounded-l-md w-full bg-gray-700 text-white text-sm sm:text-base md:text-sm lg:text-base placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                <button type="submit"
                                    class="bg-blue-600 px-4 py-2 rounded-r-md hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </button>
                            </form>
                            <p class="text-xs text-gray-500">
                                {{ t('footer.privacy_text') || 'Подписываясь, вы соглашаетесь с нашей' }} <a href="#"
                                    class="underline hover:text-gray-300"> {{ t('footer.privacy') || 'Политикой конфиденциальности' }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Копирайт -->
            <div class="mt-8 pt-8 border-t border-gray-700">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <Link href="/">
                    <p class="text-gray-400 text-xs sm:text-sm">
                        <!-- &copy; 2025-{{ currentYear }} MedPortal -->
                        &copy; {{ currentYear }} MedPortal
                    </p>
                    </Link>
                    <div class="mt-4 md:mt-0 flex space-x-6">
                        <a href="#"
                            class="text-gray-400 hover:text-white text-xs sm:text-sm md:text-xs lg:text-sm transition-colors">
                            {{ t('footer.privacy') || 'Политика конфиденциальности' }}</a>
                        <a href="#"
                            class="text-gray-400 hover:text-white text-xs sm:text-sm md:text-xs lg:text-sm transition-colors">
                            {{ t('footer.terms') || 'Условия использования' }}</a>
                        <a href="#"
                            class="text-gray-400 hover:text-white text-xs sm:text-sm md:text-xs lg:text-sm transition-colors">
                            {{ t('footer.sitemap') || 'Карта сайта' }}</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { inject } from 'vue';

const props = defineProps({
    t: Function,
    darkMode: Boolean
});

const currentYear = new Date().getFullYear();

const mapSection = inject('mapSectionRef', null);

const scrollToMap = () => {
    if (mapSection?.value) {
        mapSection.value.scrollIntoView({ behavior: 'smooth' });
    } else {
        console.log('Секция с картой не найдена');
    }
};
</script>