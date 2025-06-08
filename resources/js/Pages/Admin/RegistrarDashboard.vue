<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RecordsManagementSection from '@/Components/Admin/Registrar/Sections/RecordsManagementSection.vue';
import RegistrationSection from '@/Components/Admin/Registrar/Sections/RegistrationSection.vue';

const props = defineProps({
  auth: Object,
  appointments: Array,
  specialties: Array,
  patient: Object,
  doctors: Array,
  availableTimes: Array,
  filters: Object
});

const showError = ref(false);
const currentError = ref('');

const handleError = (message) => {
  currentError.value = message;
  showError.value = true;

  setTimeout(() => {
    showError.value = false;
  }, 5000);
};
</script>


<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>

<template>
  <Head title="Регистратура" />

  <AdminLayout :auth="auth">
    <!-- Toast-уведомление об ошибке -->
    <div v-if="showError" class="fixed bottom-4 right-4 z-60">
      <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-start">
        <svg class="h-6 w-6 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="font-medium">Ошибка</p>
          <p class="text-sm">{{ currentError }}</p>
        </div>
        <button @click="showError = false" class="ml-4 text-white hover:text-gray-200">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Только для регистратуры и администрации -->
    <div v-if="auth.user?.is_registrar || auth.user?.is_admin"
      class="p-2 xl:mx-12 xl:py-8 py-16 transition-colors duration-300">
      <RegistrationSection :auth="auth" :appointments="appointments" :specialties="specialties" :patient="patient" :doctors="doctors" :availableTimes="availableTimes" :filters="filters"/>
      <RecordsManagementSection :auth="auth" :appointments="appointments" @error="handleError" />
    </div>

    <!-- Запасной вариант, если middleware не сработал -->
    <div v-else class="p-40 flex flex-col items-center justify-center space-y-6 transition-colors duration-300">
      <div class="relative">
        <!-- Замок -->
        <svg class="w-20 h-20 text-red-600 dark:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
          </path>
        </svg>
      </div>

      <!-- Текст сообщения -->
      <div class="text-center space-y-2">
        <p class="text-2xl font-bold text-red-600 dark:text-red-500">Доступ запрещен</p>
        <p class="text-gray-600 dark:text-gray-400">Эта страница доступна только для авторизованных администраторов</p>
      </div>

      <!-- Кнопка возврата -->
      <button @click="$inertia.visit(route('dashboard'))"
        class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 transition-colors">
        Вернуться на главную
      </button>
    </div>
  </AdminLayout>
</template>