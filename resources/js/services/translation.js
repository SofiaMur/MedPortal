import { reactive, readonly } from 'vue';

const state = reactive({
  locale: localStorage.getItem('locale') || 'ru',
  translations: {},
  isLoaded: false
});

export default {
  state: readonly(state),
  
  async setLocale(locale) {
    if (state.locale === locale && state.isLoaded) return;
    
    try {
      const response = await fetch(`/translations/${locale}`);
      state.translations = await response.json();
      state.locale = locale;
      state.isLoaded = true;
      localStorage.setItem('locale', locale);
      return true;
    } catch (error) {
      console.error('Не удалось загрузить перевод:', error);
      return false;
    }
  },
  
  t(key) {
    if (!state.isLoaded) return key;
    
    return key.split('.').reduce((obj, k) => {
      if (obj && obj[k] !== undefined) {
        return obj[k];
      }
      console.warn(`Пропущен ключ перевода: ${key}`);
      return key;
    }, state.translations);
  },
  
  async init() {
    if (!state.isLoaded) {
      await this.setLocale(state.locale);
    }
  }
};