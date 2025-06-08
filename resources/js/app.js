import './bootstrap';
import '../css/app.css';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';

import { createApp, h, ref } from 'vue'; // Добавляем импорт ref
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import translationService from './services/translation';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// ===== [1] Инициализация темы (до создания приложения) =====
const getInitialDarkMode = () => {
    // Проверяем localStorage и системные настройки
    const savedMode = localStorage.getItem('darkMode');
    if (savedMode !== null) {
        return savedMode === 'true';
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

// Применяем тему сразу (чтобы избежать мерцания при загрузке)
const initialDarkMode = getInitialDarkMode();
if (initialDarkMode) {
    document.documentElement.classList.add('dark');
}

// ===== [2] Создаём Inertia-приложение =====
createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        // Создаём реактивное состояние для темы
        const darkMode = ref(initialDarkMode);
        
        const vueApp = createApp({ 
            render: () => h(App, props) 
        })
        .use(plugin)
        .use(ZiggyVue)
        .provide('translation', translationService);

        // ===== [3] Глобальное управление темой =====
        vueApp.provide('darkMode', {
            value: darkMode,
            toggle: () => {
                // Инвертируем текущее значение
                darkMode.value = !darkMode.value;
                
                // Обновляем localStorage
                localStorage.setItem('darkMode', darkMode.value);
                
                // Обновляем класс в DOM
                document.documentElement.classList.toggle('dark', darkMode.value);
            }
        });

        // Обработчик ошибок
        router.on('error', (error) => {
            if (error.response?.status === 403) {
                alert('Доступ запрещён!');
                window.location.href = '/login';
            }
        });

        // Инициализация переводов перед монтированием
        translationService.init().then(() => {
            vueApp.mount(el);
        });
    },
    progress: {
        color: '#4B5563',
    },
});