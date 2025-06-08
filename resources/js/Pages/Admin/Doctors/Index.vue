<template>
    <AdminLayout>

        <Head title="Управление врачами" />

        <!-- Уведомление -->
        <Transition enter-active-class="transition ease-out duration-300"
            enter-from-class="transform opacity-0 -translate-y-2" enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200" leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-2">
            <div v-if="showSuccessMessage" class="fixed top-16 left-1/2 -translate-x-1/2 mx-auto z-50">
                <div
                    class="bg-green-50 border-l-4 border-green-500 text-green-700 px-6 py-3 rounded shadow-lg max-w-md w-full">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>{{ successMessage }}</span>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="flex flex-col lg:flex-row gap-6 py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="lg:w-7/12">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-semibold">Список врачей</h2>
                            <div class="relative w-64">
                                <TextInput v-model="search" type="text" placeholder="Поиск врачей..."
                                    @update:modelValue="handleSearch" />
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            ФИО</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Специальность</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Email</th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Действия</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="doctor in doctors.data" :key="doctor.id">
                                        <td class="px-4 py-4 break-words max-w-[200px]">{{ doctor.user.name }}</td>
                                        <td class="px-4 py-4 break-words max-w-[200px]">{{ doctor.specialization }}</td>
                                        <td class="px-4 py-4 break-all max-w-[200px]">{{ doctor.user.email }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap space-x-4">
                                            <button @click="editDoctor(doctor)"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                Изменить
                                            </button>
                                            <button @click="deleteDoctor(doctor)"
                                                class="text-red-600 hover:text-red-900">
                                                Удалить
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="doctors.data.length === 0">
                                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">
                                            Врачи не найдены
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination v-if="doctors.data.length > 0" :links="doctors.links" class="mt-4" />
                    </div>
                </div>
            </div>

            <div class="lg:w-5/12">
                <div class="sticky top-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h1 class="text-2xl font-semibold mb-6">
                                {{ editingDoctor ? 'Редактирование данных врача' : 'Добавление нового врача' }}
                            </h1>

                            <form @submit.prevent="submit" class="space-y-4">
                                <div v-if="editingDoctor" class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <InputLabel value="Дата регистрации" />
                                        <div class="mt-1 p-2 text-sm text-gray-600 bg-gray-100 rounded">
                                            {{ formatDate(editingDoctor.created_at) }}
                                        </div>
                                    </div>
                                    <div>
                                        <InputLabel value="Последнее обновление" />
                                        <div class="mt-1 p-2 text-sm text-gray-600 bg-gray-100 rounded">
                                            {{ formatDate(editingDoctor.updated_at) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <InputLabel for="name" value="ФИО" />
                                        <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full"
                                            required autofocus />
                                        <InputError :message="form.errors.name" />
                                    </div>

                                    <div>
                                        <InputLabel for="email" value="Email" />
                                        <TextInput id="email" v-model="form.email" type="email"
                                            class="mt-1 block w-full" required />
                                        <InputError :message="form.errors.email" />
                                    </div>

                                    <div v-if="!editingDoctor">
                                        <InputLabel for="password" value="Пароль" />
                                        <div class="relative">
                                            <TextInput id="password" v-model="form.password"
                                                :type="showPassword ? 'text' : 'password'"
                                                class="mt-1 block w-full pr-10" required autocomplete="new-password" />
                                            <button @click="showPassword = !showPassword" type="button"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                <span class="text-xs">{{ showPassword ? 'Скрыть' : 'Показать' }}</span>
                                            </button>
                                        </div>
                                        <div v-if="form.password" class="text-xs mt-1">
                                            Сложность пароля:
                                            <span :class="{
                                                'text-red-500': passwordStrength < 3,
                                                'text-yellow-500': passwordStrength === 3,
                                                'text-green-500': passwordStrength > 3
                                            }">
                                                {{ passwordStrength }}/5
                                            </span>
                                        </div>
                                        <InputError :message="form.errors.password" />
                                    </div>

                                    <div v-if="!editingDoctor">
                                        <InputLabel for="password_confirmation" value="Подтвердите пароль" />
                                        <div class="relative">
                                            <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                                class="mt-1 block w-full pr-10" required autocomplete="new-password"
                                                type="password" />
                                        </div>
                                        <InputError :message="form.errors.password_confirmation" />
                                    </div>
                                    <div>
                                        <InputLabel for="specialization" value="Специальность" />
                                        <TextInput id="specialization" v-model="form.specialization" type="text"
                                            class="mt-1 block w-full" required />
                                        <InputError :message="form.errors.specialization" />
                                    </div>
                                </div>

                                <div class="flex flex-col gap-4 mt-4">
                                    <!-- Поля для нового пароля (появляются при нажатии "Сбросить пароль?") -->
                                    <div v-if="showPasswordFields" class="grid grid-cols-1 gap-4">
                                        <div>
                                            <InputLabel for="new_password" value="Новый пароль" />
                                            <div class="relative">
                                                <TextInput id="new_password" v-model="form.password"
                                                    :type="showPassword ? 'text' : 'password'"
                                                    class="mt-1 block w-full pr-10" autocomplete="new-password" />
                                                <button @click="showPassword = !showPassword" type="button"
                                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                                    <span class="text-xs">{{ showPassword ? 'Скрыть' : 'Показать'
                                                        }}</span>
                                                </button>
                                            </div>
                                            <div v-if="form.password" class="text-xs mt-1">
                                                Сложность пароля:
                                                <span :class="{
                                                    'text-red-500': passwordStrength < 3,
                                                    'text-yellow-500': passwordStrength === 3,
                                                    'text-green-500': passwordStrength > 3
                                                }">
                                                    {{ passwordStrength }}/5
                                                </span>
                                            </div>
                                            <InputError :message="form.errors.password" />
                                        </div>
                                        <div>
                                            <InputLabel for="new_password_confirmation"
                                                value="Подтвердите новый пароль" />
                                            <TextInput id="new_password_confirmation"
                                                v-model="form.password_confirmation" type="password"
                                                class="mt-1 block w-full" autocomplete="new-password" />
                                            <InputError :message="form.errors.password_confirmation" />
                                        </div>
                                    </div>

                                    <!-- Кнопки действий -->
                                    <div class="flex justify-between items-center gap-4">
                                        <div>
                                            <button v-if="editingDoctor && !showPasswordFields"
                                                @click="showPasswordFields = true" type="button"
                                                class="text-sm text-gray-900 hover:text-gray-600">
                                                Сбросить пароль?
                                            </button>
                                        </div>
                                        <div class="flex gap-4">
                                            <button v-if="editingDoctor" @click="handleCancel" type="button"
                                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                                                Отмена
                                            </button>
                                            <PrimaryButton :disabled="form.processing">
                                                {{ form.processing ? 'Сохранение...' : (editingDoctor ? 'Обновить данные' : 'Зарегистрировать врача') }}
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    doctors: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showPasswordFields = ref(false);
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref('');
const editingDoctor = ref(null);
let timeoutId = null;

const form = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    specialization: ''
});

const passwordStrength = computed(() => {
    if (!form.password) return 0;
    let strength = 0;
    if (form.password.length >= 8) strength++;
    if (/[A-Z]/.test(form.password)) strength++;
    if (/[0-9]/.test(form.password)) strength++;
    if (/[^A-Za-z0-9]/.test(form.password)) strength++;
    return strength;
});

const handleSearch = () => {
    router.get(route('admin.doctors.index'), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
};

const editDoctor = (doctor) => {
    editingDoctor.value = doctor;
    form.id = doctor.id;
    form.name = doctor.user.name;
    form.email = doctor.user.email;
    form.specialization = doctor.specialization;
    form.password = '';
    form.password_confirmation = '';
    showPasswordFields.value = false;
};

const deleteDoctor = (doctor) => {
    if (confirm('Вы уверены, что хотите удалить этого врача? Это действие нельзя отменить.')) {
        router.delete(route('admin.doctors.destroy', { doctor: doctor.id }), {
            onSuccess: () => {
                successMessage.value = 'Врач успешно удален!';
                showSuccessMessage.value = true;

                timeoutId = setTimeout(() => {
                    showSuccessMessage.value = false;
                }, 5000);
            },
            preserveScroll: true,
        });
    }
};

const handleCancel = () => {
    if (showPasswordFields.value) {
        showPasswordFields.value = false;
        form.password = '';
        form.password_confirmation = '';
    } else {
        cancelEdit();
    }
};

const cancelEdit = () => {
    editingDoctor.value = null;
    showPasswordFields.value = false;
    form.reset();
};

const submit = () => {
    if (showPasswordFields.value || !editingDoctor.value) {
        if (form.password !== form.password_confirmation) {
            form.setError('password_confirmation', 'Пароли не совпадают');
            return;
        }
        if (form.password.length < 8) {
            form.setError('password', 'Пароль должен быть не менее 8 символов');
            return;
        }
    }

    if (editingDoctor.value) {
        const updateData = {
            name: form.name,
            email: form.email,
            specialization: form.specialization
        };

        if (form.password) {
            updateData.password = form.password;
            updateData.password_confirmation = form.password_confirmation;
        }

        form.transform((data) => updateData)
            .put(route('admin.doctors.update', { doctor: editingDoctor.value.id }), {
                onSuccess: () => {
                    successMessage.value = 'Данные врача успешно обновлены!';
                    showSuccessMessage.value = true;
                    editingDoctor.value = null;
                    form.reset();
                    showPasswordFields.value = false;

                    timeoutId = setTimeout(() => {
                        showSuccessMessage.value = false;
                    }, 5000);
                },
                preserveScroll: true,
                onError: (errors) => {
                    if (errors.email) {
                        form.setError('email', errors.email);
                    }
                }
            });
    } else {
        form.post(route('admin.doctors.store'), {
            onSuccess: () => {
                successMessage.value = 'Врач успешно зарегистрирован!';
                showSuccessMessage.value = true;
                form.reset();

                timeoutId = setTimeout(() => {
                    showSuccessMessage.value = false;
                }, 5000);
            },
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('ru-RU', options);
};
</script>