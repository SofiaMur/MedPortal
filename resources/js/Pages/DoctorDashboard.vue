<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import DoctorLayout from '@/Layouts/DoctorLayout.vue';
import DoctorDashboardSection from '@/Components/Profile/Sections/DoctorDashboardSection.vue';

const props = defineProps({
    auth: Object,
    appointments: Array,
    completedAppointments: Array,
    completedAppointmentsHistory: Array,
    errors: Object
});
</script>
<template>

    <Head title="Личный кабинет" />

    <DoctorLayout>
        <!-- Только для врачей -->
        <div v-if="auth.user?.is_doctor">
            <DoctorDashboardSection :auth="auth" :appointments="appointments"
                :completed-appointments="completedAppointments"  :completed-appointments-history="completedAppointmentsHistory"  />
        </div>

        <!-- Запасной вариант, если middleware не сработал -->
        <div v-else class="p-40 flex flex-col items-center justify-center space-y-6">
            <div class="relative">
                <!-- Замок -->
                <svg class="w-20 h-20 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
            </div>

            <!-- Текст сообщения -->
            <div class="text-center space-y-2">
                <p class="text-2xl font-bold text-red-700">Доступ запрещен</p>
                <p class="text-gray-600">Эта страница доступна только для авторизованных врачей</p>
            </div>

            <!-- Кнопка возврата -->
            <button @click="$inertia.visit(route('dashboard'))"
                class="mt-4 px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Вернуться на главную
            </button>
        </div>
    </DoctorLayout>
</template>