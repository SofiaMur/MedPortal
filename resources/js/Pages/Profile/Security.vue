<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>

    <Head title="Редактирование данных" />

    <AuthenticatedLayout>
        <div class="flex justify-center pt-6 pb-4 gap-12">
                <UpdatePasswordForm class="max-w-xl" />

                <DeleteUserForm class="max-w-xl" />
            </div>
    </AuthenticatedLayout>
</template>
