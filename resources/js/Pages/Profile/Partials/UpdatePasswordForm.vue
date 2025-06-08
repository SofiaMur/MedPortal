<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Оценка сложности пароля
const passwordStrength = computed(() => {
    if (!form.password) return 0;

    let strength = 0;

    // Соответствует правилу min(8)
    if (form.password.length >= 8) strength += 1;
    if (form.password.length >= 12) strength += 1;

    // Соответствует правилу numbers()
    if (/\d/.test(form.password)) strength += 1;

    // Соответствует правилу symbols()
    if (/[!@#$%^&*(),.?":{}|<>]/.test(form.password)) strength += 1;

    // Соответствует правилу mixedCase()
    if (/[a-z]/.test(form.password) && /[A-Z]/.test(form.password)) strength += 1;

    return Math.min(strength, 5);
});

const passwordStrengthText = computed(() => {
    const strengths = ['Очень слабый', 'Слабый', 'Средний', 'Сильный', 'Очень сильный', 'Отличный'];
    return strengths[passwordStrength.value];
});

const passwordStrengthColor = computed(() => {
    const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500', 'bg-green-600'];
    return colors[passwordStrength.value];
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.current_password = '';
            form.password = '';
            form.password_confirmation = '';
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <div class="w-full">
        <h2 class="text-lg font-medium text-gray-900">
            Обновление пароля
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Используйте сложный пароль для обеспечения безопасности вашего аккаунта
        </p>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Текущий пароль" />

                <div class="mt-1 relative rounded-md shadow-sm">
                    <TextInput id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                        type="password"
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        autocomplete="off" data-lpignore="true" :readonly="true"
                        @focus="(e) => { e.target.removeAttribute('readonly'); }" />
                </div>

                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" value="Новый пароль" />

                <div class="mt-1 relative rounded-md shadow-sm">
                    <TextInput id="password" ref="passwordInput" v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        autocomplete="new-password" />
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        @click="showPassword = !showPassword" aria-label="Показать/скрыть пароль">
                        <svg v-if="showPassword" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <!-- Индикатор сложности пароля -->
                <div v-if="form.password" class="mt-2 space-y-1">
                    <div class="flex items-center justify-between text-xs">
                        <span>Сложность: {{ passwordStrengthText }}</span>
                        <span v-if="passwordStrength < 3" class="text-red-500">Слишком слабый</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full transition-all duration-300" :class="passwordStrengthColor"
                            :style="`width: ${(passwordStrength / 5) * 100}%`"></div>
                    </div>
                    <ul class="text-xs text-gray-500 list-disc pl-5">
                        <li>Минимум 8 символов</li>
                        <li>Заглавные и строчные буквы</li>
                        <li>Хотя бы одна цифра</li>
                        <li>Специальный символ (!@#$%^&*)</li>
                    </ul>
                </div>

                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Подтверждение пароля" />

                <div class="mt-1 relative rounded-md shadow-sm">
                    <TextInput id="password_confirmation" v-model="form.password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        autocomplete="new-password" />
                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        @click="showConfirmPassword = !showConfirmPassword" aria-label="Показать/скрыть пароль">
                        <svg v-if="showConfirmPassword" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>

                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                <!-- Всплывающее окно об успешном сохранении -->
                <Transition name="fade">
                    <div v-if="form.recentlySuccessful" class="fixed inset-0 flex items-center justify-center z-50">
                        <div class="absolute inset-0 bg-black bg-opacity-30"></div>
                        <div class="bg-white rounded-lg p-6 shadow-xl z-10 max-w-sm w-full mx-4">
                            <div class="flex items-center justify-center">
                                <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="mt-3 text-lg font-medium text-center text-gray-900">Пароль успешно изменен</h3>
                        </div>
                    </div>
                </Transition>
            </div>
        </form>
    </div>
</template>