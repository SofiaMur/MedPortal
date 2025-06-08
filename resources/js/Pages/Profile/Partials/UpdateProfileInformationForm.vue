<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import RadioButton from '@/Components/RadioButton.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    last_name: user.last_name,
    first_name: user.first_name,
    middle_name: user.middle_name,
    gender: user.gender,
    birth_date: user.birth_date ? new Date(user.birth_date).toISOString().split('T')[0] : null,
    phone: user.phone,
    insurance_number: user.insurance_number,
    snils: user.snils,
    allergies: user.allergies,
    chronic_diseases: user.chronic_diseases,
    email: user.email,
    is_profile_completed: user.is_profile_completed
});

const originalFormData = ref(JSON.parse(JSON.stringify(form.data())));

const hasChanges = computed(() => {
    return Object.keys(originalFormData.value).some(key => {
        if (key === 'birth_date') {
            const current = form.birth_date ? new Date(form.birth_date).toISOString().split('T')[0] : null;
            const original = originalFormData.value.birth_date ? new Date(originalFormData.value.birth_date).toISOString().split('T')[0] : null;
            return current !== original;
        }
        return JSON.stringify(form[key]) !== JSON.stringify(originalFormData.value[key]);
    });
});

const display = {
    phone: ref(''),
    insurance: ref(''),
    snils: ref('')
};

display.phone.value = formatPhone(user.phone);
display.insurance.value = formatInsurance(user.insurance_number);
display.snils.value = formatSnils(user.snils);

function formatPhone(value) {
    if (!value) return '';
    const digits = value.replace(/\D/g, '');
    return digits.replace(/(\d{1})(\d{3})(\d{3})(\d{2})(\d{2})/, '$1 ($2) $3-$4-$5');
}

function formatInsurance(value) {
    if (!value) return '';
    const digits = value.replace(/\D/g, '');
    return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{5})/, '$1-$2-$3-$4');
}

function formatSnils(value) {
    if (!value) return '';
    const digits = value.replace(/\D/g, '');
    return digits.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1-$2-$3 $4');
}

watch(() => display.phone.value, (newVal) => {
    form.phone = newVal.replace(/\D/g, '').slice(0, 11);
    display.phone.value = formatPhone(form.phone);
});

watch(() => display.insurance.value, (newVal) => {
    form.insurance_number = newVal.replace(/\D/g, '').slice(0, 16);
    display.insurance.value = formatInsurance(form.insurance_number);
});

watch(() => display.snils.value, (newVal) => {
    form.snils = newVal.replace(/\D/g, '').slice(0, 11);
    display.snils.value = formatSnils(form.snils);
});

const submit = () => {
    if (!hasChanges.value) {
        return;
    }

    const payload = {
        ...form.data(),
        birth_date: form.birth_date ? formatDateForBackend(form.birth_date) : null
    };

    form.transform((data) => payload).patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            originalFormData.value = JSON.parse(JSON.stringify(form.data()));
            display.phone.value = formatPhone(form.phone);
            display.insurance.value = formatInsurance(form.insurance_number);
            display.snils.value = formatSnils(form.snils);
        },
        onError: () => {
            display.phone.value = formatPhone(form.phone);
            display.insurance.value = formatInsurance(form.insurance_number);
            display.snils.value = formatSnils(form.snils);
        }
    });
};

const hasErrors = computed(() => Object.keys(form.errors).length > 0);

function formatDateForBackend(dateString) {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0]; // Формат YYYY-MM-DD
}
</script>

<template>
    <h2 class="text-lg font-medium text-gray-900 pt-6">
        Информация профиля
    </h2>

    <p class="mt-1 text-sm text-gray-600 pb-4">
        Обязательные поля для заполнения отмечены звездочкой
    </p>

    <!-- Общее сообщение об ошибках -->
    <div v-if="hasErrors" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
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
                    Пожалуйста, проверьте введенные данные и попробуйте снова.
                </h3>
            </div>
        </div>
    </div>

    <form @submit.prevent="submit" class="mt-6 space-y-6">
        <!-- Личные данные -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <InputLabel for="last_name" value="Фамилия *" />
                <TextInput id="last_name" type="text" class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.last_name }" v-model="form.last_name" required />
                <InputError class="mt-2" :message="form.errors.last_name" />
            </div>

            <div>
                <InputLabel for="first_name" value="Имя *" />
                <TextInput id="first_name" type="text" class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.first_name }" v-model="form.first_name" required />
                <InputError class="mt-2" :message="form.errors.first_name" />
            </div>

            <div>
                <InputLabel for="middle_name" value="Отчество *" />
                <TextInput id="middle_name" type="text" class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.middle_name }" v-model="form.middle_name" />
                <InputError class="mt-2" :message="form.errors.middle_name" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <InputLabel for="email" value="Email *" />
                <TextInput id="email" type="email" class="mt-1 block w-full bg-gray-100" v-model="form.email"
                    required disabled />
                <InputError class="mt-2" :message="form.errors.email" />
                     <!-- Подтверждение email -->
            <div v-if="mustVerifyEmail && user.email_verified_at === null">
            <p class="mt-2 text-sm text-gray-800">
                Ваш email не подтвержден.
            </p>

            <div v-show="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                Новое письмо подтверждения отправлено на ваш email.
            </div>
        </div>
            </div>

            <div>
                <InputLabel for="phone" value="Телефон *" />
                <TextInput id="phone" type="tel" inputmode="numeric" class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.phone }" v-model="display.phone.value" required
                    placeholder="7 (___) ___-__-__" maxlength="17" />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div>
                <InputLabel for="birth_date" value="Дата рождения *" />
                <input id="birth_date" type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-500': form.errors.birth_date }" v-model="form.birth_date"
                    :max="new Date().toISOString().split('T')[0]" />
                <InputError class="mt-2" :message="form.errors.birth_date" />
            </div>
        </div>

        <!-- Страховые данные -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <InputLabel for="insurance_number" value="Полис ОМС *" />
                <TextInput id="insurance_number" type="text" inputmode="numeric" class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.insurance_number }" v-model="display.insurance.value"
                    required placeholder="___-___-___-____" maxlength="16" />
                <InputError class="mt-2" :message="form.errors.insurance_number" />
            </div>

            <div>
                <InputLabel for="snils" value="СНИЛС *" />
                <TextInput id="snils" type="text" inputmode="numeric" class="mt-1 block w-full"
                    :class="{ 'border-red-500': form.errors.snils }" v-model="display.snils.value" required
                    placeholder="___-___-___ __" maxlength="14" />
                <InputError class="mt-2" :message="form.errors.snils" />
            </div>
        </div>

        <div>
            <InputLabel value="Пол *" />
            <div class="mt-1 flex space-x-4">
                <label class="inline-flex items-center">
                    <RadioButton v-model="form.gender" value="male" :checked="form.gender === 'male'" />
                    <span class="ml-2 text-sm text-gray-600">Мужской</span>
                </label>
                <label class="inline-flex items-center">
                    <RadioButton v-model="form.gender" value="female" :checked="form.gender === 'female'" />
                    <span class="ml-2 text-sm text-gray-600">Женский</span>
                </label>
            </div>
            <InputError class="mt-2" :message="form.errors.gender" />
        </div>

        <!-- Медицинские данные -->
        <div>
            <InputLabel for="allergies" value="Аллергии" />
            <textarea id="allergies"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :class="{ 'border-red-500': form.errors.allergies }" v-model="form.allergies" rows="3"></textarea>
            <InputError class="mt-2" :message="form.errors.allergies" />
        </div>

        <div>
            <InputLabel for="chronic_diseases" value="Хронические заболевания" />
            <textarea id="chronic_diseases"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                :class="{ 'border-red-500': form.errors.chronic_diseases }" v-model="form.chronic_diseases"
                rows="3"></textarea>
            <InputError class="mt-2" :message="form.errors.chronic_diseases" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton 
                type="submit" 
                :disabled="form.processing || !hasChanges"
                class="tracking-widest active:bg-blue-900 transition ease-in-out duration-150 disabled:opacity-50"
            >
                <span v-if="form.processing">Сохранение...</span>
                <span v-else>Сохранить изменения</span>
            </PrimaryButton>
        </div>
    </form>

    <!-- Всплывающее окно об успешном сохранении -->
    <Transition name="fade">
        <div v-if="form.recentlySuccessful" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            <div class="bg-white rounded-lg p-6 shadow-xl z-10 max-w-sm w-full mx-4">
                <div class="flex items-center justify-center">
                    <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="mt-3 text-lg font-medium text-center text-gray-900">Данные успешно сохранены</h3>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>