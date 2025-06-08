<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import UserDashboard from '@/Components/Profile/UserDashboard.vue';

const props = defineProps({
    auth: Object,
    appointments: Array,
    completedAppointments: Array,
    errors: Object
});

const errorMessage = ref(props.errors?.message || '');
const recentlySuccessful = ref(false); 
let timeoutId = null; 

watch(() => props.errors, (newErrors) => {
    if (newErrors?.message) {
        errorMessage.value = newErrors.message;
    }
}, { deep: true });

const clearError = () => {
    errorMessage.value = '';
};

const cancelAppointment = (appointmentId) => {
    router.delete(route('appointments.cancel', { appointment: appointmentId }), {
        preserveScroll: true,
        onError: (errors) => {
            errorMessage.value = errors.message;
        }
    });
};

const updateReason = ({ id, reason }) => {
    router.patch(route('dashboard.update-reason', { appointment: id }), {
        reason: reason
    }, {
        preserveScroll: true,
        onSuccess: () => {
            recentlySuccessful.value = true;
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                recentlySuccessful.value = false;
            }, 3000);
            clearError();
        },
        onError: (errors) => {
            errorMessage.value = errors.message || 'Произошла ошибка при редактировании причины обращения';
        }
    });
};
</script>

<template>
    <Head title="Личный профиль" />

    <AuthenticatedLayout>
        <!-- Приветствие -->
        <div v-if="auth.user" class="py-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Добро пожаловать, <span class="text-indigo-600">{{ auth.user.login }}</span>!
            </h1>
        </div>

        <!-- Блок ошибок -->
        <div v-if="errorMessage" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        {{ errorMessage }}
                    </h3>
                </div>
            </div>
        </div>

        <!-- Уведомление об успешном сохранении -->
        <Transition name="fade">
            <div v-if="recentlySuccessful" class="inset-0 fixed flex items-center justify-center">
                <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                <div class="bg-white rounded-lg p-6 shadow-xl z-10 max-w-sm w-full mx-4">
                    <div class="flex items-center justify-center">
                        <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-3 text-lg font-medium text-center text-gray-900">Причина обращения успешно изменена</h3>
                </div>
            </div>
        </Transition>

        <UserDashboard 
            :auth="auth"
            :appointments="appointments"
            :completed-appointments="completedAppointments"
            @cancel-appointment="cancelAppointment"
            @update-reason="updateReason"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>