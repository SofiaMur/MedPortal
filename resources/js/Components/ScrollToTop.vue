<template>
    <transition name="fade">
      <button 
        v-show="showButton && !nearFooter"
        @click="scrollToTop"
        class="fixed right-8 z-[9999] p-3 bg-blue-600 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 md:block hidden"
        :style="{ bottom: buttonBottom }"
        aria-label="Scroll to top"
      >
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
      </button>
    </transition>
</template>
  
<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const showButton = ref(false);
const lastScrollPosition = ref(0);
const buttonBottom = ref('2rem');
const nearFooter = ref(false);
const footerHeight = 200; 

const checkScrollPosition = () => {
    const scrollPosition = window.scrollY || document.documentElement.scrollTop;
    const pageHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrollPercent = (scrollPosition / pageHeight) * 100;
    const distanceToBottom = pageHeight - scrollPosition;   
    showButton.value = scrollPercent > 55;

    nearFooter.value = distanceToBottom < footerHeight * 1.5;   
    if (scrollPosition > lastScrollPosition.value && scrollPercent > 55 && !nearFooter.value) {
      const moveAmount = Math.min(5, scrollPosition - lastScrollPosition.value);
      const currentBottom = parseInt(buttonBottom.value) || 32;
      buttonBottom.value = `${Math.max(16, currentBottom - moveAmount)}px`;
    } else if (scrollPercent > 55 && !nearFooter.value) {
      const currentBottom = parseInt(buttonBottom.value) || 32;
      buttonBottom.value = `${Math.min(32, currentBottom + 5)}px`;
    }   
    lastScrollPosition.value = scrollPosition;
};

const computedBottom = computed(() => {
    return nearFooter.value ? `${footerHeight + 16}px` : buttonBottom.value;
});

const scrollToTop = () => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
    buttonBottom.value = '2rem';
};

onMounted(() => {
    window.addEventListener('scroll', checkScrollPosition);
});

onUnmounted(() => {
    window.removeEventListener('scroll', checkScrollPosition);
});
</script>
  
<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

button {
  transition: bottom 0.2s ease-out;
}
</style>