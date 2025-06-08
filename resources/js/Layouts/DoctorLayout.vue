<script setup>
import { inject, ref, onMounted, watch } from 'vue';
import Footer from '@/Components/App/Footer.vue';
import Header from '@/Components/Profile/Header.vue'

const darkMode = ref(localStorage.getItem('darkMode') === 'true');

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
    updateTheme();
});
</script>

<template>
   <Header 
        :darkMode="darkMode" 
        :selectedLanguage="selectedLanguage" 
        :languages="languages" 
        :t="t"
        :auth="$page.props.auth"
        @toggleDarkMode="toggleDarkMode" 
        @changeLanguage="changeLanguage" 
    />

    <!-- Основное содержимое -->
    <main class="bg-blue-50">
        <div class="xl:py-8 py-0">
            <div class="mx-auto bg-white rounded-lg max-w-7xl pb-6 px-6 lg:px-8">
            <slot />
        </div>
    </div>
    </main>

    <Footer :t="t" />
</template>

