<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    current_password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <div class="w-full">
        <h2 class="text-lg font-medium text-gray-900">
            Удаление аккаунта
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            После удаления Вашего аккаунта все его данные и ресурсы будут безвозвратно удалены.
            Перед удалением аккаунта, пожалуйста, сохраните все данные, которые Вы хотите оставить!
        </p>

        <DangerButton @click="confirmUserDeletion" class="mt-4">
            Удалить аккаунт
        </DangerButton>

        <div v-if="confirmingUserDeletion"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium font-medium text-gray-900">Подтверждение удаления аккаунта</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">
                            Вы уверены, что хотите удалить свой аккаунт? Все данные будут безвозвратно удалены.
                            <br>Для подтверждения введите свой пароль:
                        </p>
                    </div>
                    <div class="mt-4">
                        <InputLabel for="password" value="Пароль" class="sr-only" />
                        <TextInput id="current_password" ref="currentPasswordInput" v-model="form.current_password"
                            type="password"
                            class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            autocomplete="off" data-lpignore="true" :readonly="true"
                            @focus="(e) => { e.target.removeAttribute('readonly'); }" />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </div>
                <div class="bg-gray-50 rounded-b-lg px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <DangerButton @click="deleteUser" :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">
                        Удалить аккаунт
                    </DangerButton>
                    <SecondaryButton @click="closeModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Отмена
                    </SecondaryButton>
                </div>
            </div>
        </div>
    </div>
</template>