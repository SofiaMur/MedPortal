<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
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
        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
    </AuthenticatedLayout>
</template>
