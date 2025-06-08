<template>
    <AdminLayout>

        <Head title="Управление пользователями" />

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
                            <h2 class="text-xl font-semibold">Список пользователей</h2>
                            <div class="relative w-64">
                                <TextInput v-model="search" type="text" placeholder="Поиск пользователей..."
                                    @update:modelValue="handleSearch" />
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            ФИО
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Email
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Роль
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Действия
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-4 py-4 break-words max-w-[200px]">{{ user.name }}</td>
                                        <td class="px-4 py-4 break-all max-w-[200px]">{{ user.email }}</td>
                                        <td class="px-4 py-4 break-words max-w-[200px]">
                                            <span class="px-2 py-1 text-xs rounded-full" :class="{
                                                'bg-purple-100 text-purple-800': user.roles.some(role => role.name === 'admin'),
                                                'bg-blue-100 text-blue-800': user.roles.some(role => role.name === 'doctor'),
                                                'bg-gray-100 text-gray-800': !user.roles.some(role => role.name === 'admin' || role.name === 'doctor')
                                            }">
                                                {{user.roles.some(role => role.name === 'admin') ? 'Админ' :
                                                    user.roles.some(role => role.name === 'doctor') ? 'Врач' :
                                                'Пользователь' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap space-x-4">
                                            <button @click="editUser(user)"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                Изменить
                                            </button>
                                            <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900"
                                                :disabled="user.id === $page.props.auth.user.id">
                                                Удалить
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="users.data.length === 0">
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                            Пользователи не найдены
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination v-if="users.data.length > 0" :links="users.links" class="mt-4" />
                    </div>
                </div>
            </div>

            <div class="lg:w-5/12">
                <div class="sticky top-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h1 class="text-2xl font-semibold mb-6">
                                {{ editingUser ? 'Редактирование данных пользователя' : 'Добавление нового пользователя'
                                }}
                            </h1>

                            <form @submit.prevent="submit" class="space-y-4">
                                <div v-if="editingUser" class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <InputLabel value="Дата регистрации" />
                                        <div class="mt-1 p-2 text-sm text-gray-600 bg-gray-100 rounded">
                                            {{ formatDate(editingUser.created_at) }}
                                        </div>
                                    </div>
                                    <div>
                                        <InputLabel value="Последнее обновление" />
                                        <div class="mt-1 p-2 text-sm text-gray-600 bg-gray-100 rounded">
                                            {{ formatDate(editingUser.updated_at) }}
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

                                    <div>
                                        <InputLabel for="role" value="Роль" />
                                        <select id="role" v-model="form.role" @change="handleRoleChange"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="user">Пользователь</option>
                                            <option value="doctor">Врач</option>
                                            <option value="admin">Администратор</option>
                                        </select>
                                        <InputError :message="form.errors.role" />
                                    </div>

                                    <!-- Поле специальности (появляется только для врача) -->
                                    <div v-if="form.role === 'doctor'">
                                        <InputLabel for="specialization" value="Специальность" />
                                        <TextInput id="specialization" v-model="form.specialization" type="text"
                                            class="mt-1 block w-full" :required="form.role === 'doctor'" />
                                        <InputError :message="form.errors.specialization" />
                                    </div>

                                    <div v-if="!editingUser">
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

                                    <div v-if="!editingUser">
                                        <InputLabel for="password_confirmation" value="Подтвердите пароль" />
                                        <div class="relative">
                                            <TextInput id="password_confirmation" v-model="form.password_confirmation"
                                                class="mt-1 block w-full pr-10" required autocomplete="new-password"
                                                type="password" />
                                        </div>
                                        <InputError :message="form.errors.password_confirmation" />
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
                                            <button v-if="editingUser && !showPasswordFields"
                                                @click="showPasswordFields = true" type="button"
                                                class="text-sm text-gray-900 hover:text-gray-600">
                                                Сбросить пароль?
                                            </button>
                                        </div>
                                        <div class="flex gap-4">
                                            <button v-if="editingUser" @click="handleCancel" type="button"
                                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                                                Отмена
                                            </button>
                                            <PrimaryButton :disabled="form.processing">
                                                {{ form.processing ? 'Сохранение...' : (editingUser ? 'Обновить данные'
                                                : 'Зарегистрировать пользователя') }}
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
    users: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showPasswordFields = ref(false);
const showPassword = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref('');
const editingUser = ref(null);
let timeoutId = null;

const form = useForm({
    id: null,
    name: '',
    email: '',
    role: 'user',
    specialization: '',
    password: '',
    password_confirmation: '',
});

const handleRoleChange = () => {
    if (form.role !== 'doctor') {
        form.specialization = '';
    }
};

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
    router.get(route('admin.users.index'), { search: search.value }, {
        preserveState: true,
        replace: true,
    });
};

const editUser = (user) => {
    editingUser.value = user;
    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role = user.roles.some(role => role.name === 'admin') ? 'admin' :
        user.roles.some(role => role.name === 'doctor') ? 'doctor' : 'user';
    form.specialization = user.doctor?.specialization || '';
    form.password = '';
    form.password_confirmation = '';
    showPasswordFields.value = false;
};

const deleteUser = (user) => {
    if (confirm('Вы уверены, что хотите удалить этого пользователя? Это действие нельзя отменить.')) {
        router.delete(route('admin.users.destroy', { user: user.id }), {
            onSuccess: () => {
                successMessage.value = 'Пользователь успешно удален!';
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
    editingUser.value = null;
    showPasswordFields.value = false;
    form.reset();
    form.role = 'user';
    form.specialization = '';
};

const submit = () => {
    if (showPasswordFields.value || !editingUser.value) {
        if (form.password !== form.password_confirmation) {
            form.setError('password_confirmation', 'Пароли не совпадают');
            return;
        }
        if (form.password.length < 8) {
            form.setError('password', 'Пароль должен быть не менее 8 символов');
            return;
        }
    }

    const formData = new FormData();
    formData.append('name', form.name);
    formData.append('email', form.email);
    formData.append('role', form.role);

    if (form.role === 'doctor') {
        if (!form.specialization) {
            form.setError('specialization', 'Укажите специальность врача');
            return;
        }
        formData.append('specialization', form.specialization);
    }

    if (form.password) {
        formData.append('password', form.password);
        formData.append('password_confirmation', form.password_confirmation);
    }

    if (editingUser.value) {
        formData.append('_method', 'PUT');

        axios.post(route('admin.users.update', { user: editingUser.value.id }), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(response => {
                successMessage.value = 'Данные пользователя успешно обновлены!';
                showSuccessMessage.value = true;
                editingUser.value = null;
                form.reset();
                form.role = 'user';
                form.specialization = '';
                showPasswordFields.value = false;

                timeoutId = setTimeout(() => {
                    showSuccessMessage.value = false;
                }, 5000);
            })
            .catch(error => {
                if (error.response.data.errors.email) {
                    form.setError('email', error.response.data.errors.email[0]);
                }
            });
    } else {
        axios.post(route('admin.users.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
            .then(response => {
                successMessage.value = 'Пользователь успешно зарегистрирован!';
                showSuccessMessage.value = true;
                form.reset();
                form.role = 'user';
                form.specialization = '';

                timeoutId = setTimeout(() => {
                    showSuccessMessage.value = false;
                }, 5000);
            });
    }
};
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('ru-RU', options);
};
</script>