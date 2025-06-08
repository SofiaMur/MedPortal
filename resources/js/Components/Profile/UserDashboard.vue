<script setup>
import { ref, computed } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Pagination, Navigation } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import { jsPDF } from "jspdf";

const props = defineProps({
    auth: Object,
    appointments: Array,
    completedAppointments: Array,
});

const showMedicalReportModal = ref(false);
const medicalReportData = ref({
    patientName: '',
    doctorName: '',
    date: '',
    diagnosis: '',
    recommendations: '',
    prescription: ''
});

const openMedicalReport = (appointment) => {
    medicalReportData.value = {
        patientName: props.auth.user.full_name,
        doctorName: appointment.doctor.user.full_name,
        doctorSpeciality: appointment.doctor.specialty.name,
        date: formatDate(appointment.start_time),
        diagnosis: appointment.medical_report?.diagnosis || 'Диагноз не указан',
        recommendations: appointment.medical_report?.recommendations || 'Рекомендации не указаны',
        prescription: appointment.medical_report?.prescription || 'Назначения не указаны'
    };
    showMedicalReportModal.value = true;
};

const downloadPdf = async () => {
    const doc = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
    });

    try {
        const regularResponse = await fetch('/fonts/Roboto-Regular.ttf');
        if (!regularResponse.ok) throw new Error("Ошибка загрузки Roboto Regular");
        const regularBuffer = await regularResponse.arrayBuffer();
        const regularUint8Array = new Uint8Array(regularBuffer);
        const regularBinaryString = String.fromCharCode(...regularUint8Array);

        const boldResponse = await fetch('/fonts/Roboto-Bold.ttf');
        if (!boldResponse.ok) throw new Error("Ошибка загрузки Roboto Bold");
        const boldBuffer = await boldResponse.arrayBuffer();
        const boldUint8Array = new Uint8Array(boldBuffer);
        const boldBinaryString = String.fromCharCode(...boldUint8Array);

        doc.addFileToVFS('Roboto-Regular.ttf', regularBinaryString);
        doc.addFileToVFS('Roboto-Bold.ttf', boldBinaryString);

        doc.addFont('Roboto-Regular.ttf', 'Roboto', 'normal');
        doc.addFont('Roboto-Bold.ttf', 'Roboto', 'bold');

        // Установка цветов
        const mainColor = [0, 92, 171];
        const textColor = [0, 0, 0];

        // Шапка документа 
        doc.setFillColor(...mainColor);
        doc.rect(0, 0, 210, 30, 'F');

        doc.setFontSize(24);
        doc.setTextColor(255, 255, 255);
        doc.setFont('Roboto', 'bold');
        doc.text('МЕДИЦИНСКОЕ ЗАКЛЮЧЕНИЕ', 105, 20, null, null, 'center'); // Исправленный метод выравнивания

        // Основная информация
        doc.setFontSize(12);
        doc.setTextColor(...textColor);

        // Разделительные линии
        doc.setDrawColor(...mainColor);
        doc.setLineWidth(0.5);
        doc.line(10, 40, 200, 40);
        doc.line(10, 270, 200, 270);

        // Информация о пациенте и враче
        const fields = [
            { label: 'Пациент:', value: medicalReportData.value.patientName },
            {
                label: 'Пол:',
                value: props.auth.user.gender === 'male' ? 'мужской' : 'женский'
            },
            {
                label: 'Дата рождения:',
                value: props.auth.user.birth_date
            },
            { label: 'Дата осмотра:', value: medicalReportData.value.date },
            { label: 'Лечащий врач:', value: medicalReportData.value.doctorName },
            { label: 'Специализация:', value: medicalReportData.value.doctorSpeciality }
        ];

        let y = 50;
        fields.forEach(item => {
            doc.setFontSize(12);
            doc.setFont('Roboto', 'bold');
            doc.text(item.label, 20, y);
            doc.setFont('Roboto', 'normal');
            doc.text(item.value, 60, y);
            y += 8;
        });

        // Диагноз с рамкой
        y += 5;
        doc.setFont('Roboto', 'bold');
        doc.text('Диагноз:', 20, y);
        doc.setFont('Roboto', 'normal');
        doc.setDrawColor(...mainColor);
        doc.rect(20, y + 5, 170, 30);
        const diagnosisLines = doc.splitTextToSize(medicalReportData.value.diagnosis, 160);
        doc.text(diagnosisLines, 25, y + 15);

        // Рекомендации
        y += 48;
        doc.setFont('Roboto', 'bold');
        doc.text('Рекомендации:', 20, y);
        doc.setFont('Roboto', 'normal');
        const splitRec = doc.splitTextToSize(medicalReportData.value.recommendations, 170);
        const lineHeight = 6;
        const recHeight = splitRec.length * lineHeight;
        doc.text(splitRec, 20, y + 8);

        // Рецепт
        y += 8 + recHeight + 8;
        if (y > 220) {
            doc.addPage();
            y = 30;
        }
        doc.setFont('Roboto', 'bold');
        doc.text('Рецепт:', 20, y);
        doc.setFont('Roboto', 'normal');
        const splitPres = doc.splitTextToSize(medicalReportData.value.prescription, 170);
        doc.text(splitPres, 20, y + 8);

        // Подписи и печати
        y = Math.max(y + (splitPres.length * 5) + 20, 180);
        if (y > 250) {
            doc.addPage();
            y = 30;
        }
        doc.setFontSize(10);
        const signatureY = y;
        const doctorName = medicalReportData.value.doctorName;

        // Подпись врача
        doc.text('Врач: ___________________', 30, signatureY);

        // Личная печать врача с двойной рамкой
        const stampWidth = 40;
        const stampHeight = 16;
        const stampX = 45;
        const stampY = signatureY - 8;

        // Двойная рамка
        doc.setDrawColor(...mainColor);
        doc.setLineWidth(0.8);
        doc.roundedRect(stampX, stampY, stampWidth, stampHeight, 2, 2, 'D');
        doc.setLineWidth(0.3);
        doc.roundedRect(stampX + 2, stampY + 2, stampWidth - 4, stampHeight - 4, 1, 1, 'D');

        // Текст печати
        doc.setTextColor(...mainColor);
        doc.setFontSize(6);

        // Разбиваем ФИО с защитой от ошибок
        const nameParts = doctorName.trim().split(/\s+/).filter(Boolean);
        let line1 = nameParts.slice(0, 2).join(' ');
        let line2 = nameParts.length > 2 ? nameParts[2] : '';

        // Центральные координаты
        const centerX = stampX + stampWidth / 2;
        const centerY = stampY + stampHeight / 2;

        // Первая строка (имя и отчество)
        doc.setFont('Roboto', 'bold');
        doc.text(line1, centerX, centerY - 1.5, null, null, 'center');

        // Вторая строка (фамилия или "Личная печать")
        if (line2) {
            doc.text(line2, centerX, centerY + 2.5, null, null, 'center');
        } else {
            doc.setFontSize(5);
            doc.text('Личная печать', centerX, centerY + 2.5, null, null, 'center');
        }

        // Подпись пациента
        doc.setTextColor(0, 0, 0);
        doc.setFontSize(10);

        // Формируем ФИО пациента ("Иванов И.И.")
        doc.setFont('Roboto', 'normal');
        const patientName = medicalReportData.value.patientName || '';
        const userParts = patientName.split(' ');
        let initials = '';
        if (userParts.length > 1) {
            initials = userParts[1].charAt(0) + '.';
            if (userParts.length > 2) {
                initials += userParts[2].charAt(0) + '.';
            }
        }

        // Если есть ФИО, добавляем его над линией
        if (userParts.length > 0) {
            doc.text('___________________', 120, signatureY);
            doc.text(`${userParts[0]} ${initials}`, 125, signatureY - 0.5);
            doc.text('Пациент:', 100, signatureY);
        } else {
            doc.text('Пациент: ___________________', 120, signatureY);
        }

        const sealX = 160 - 20;
        const sealY = signatureY + 20 - 20;
        const sealWidth = 40;
        const sealHeight = 40;

        // Добавление изображения печати
        doc.addImage('/MedPortal_License.png', 'PNG', sealX, sealY, sealWidth, sealHeight);

        // Сохранение PDF
        const fileName = `Мед_заключение_${medicalReportData.value.patientName}_${medicalReportData.value.date}.pdf`;
        doc.save(fileName);
    } catch (error) {
        console.error("Ошибка при создании PDF:", error);
    }
};

const toggleAppointmentDetails = (appointment) => {
    if (selectedAppointment.value && selectedAppointment.value.id === appointment.id) {
        selectedAppointment.value = null;
    } else {
        selectedAppointment.value = appointment;
    }
};

const emit = defineEmits(['cancel-appointment', 'update-reason']);

const editingAppointmentId = ref(null);
const newReason = ref('');
const showCancelModal = ref(false);
const appointmentToCancel = ref(null);
const showHistory = ref(false);
const selectedAppointment = ref(null);
const swiperModules = [Pagination, Navigation];

const sortedAppointments = computed(() => {
    return [...props.appointments].sort((a, b) => {
        if (a.status === 'cancelled' && b.status !== 'cancelled') return -1;
        if (a.status !== 'cancelled' && b.status === 'cancelled') return 1;
        if (a.status === 'confirmed' && b.status !== 'confirmed') return -1;
        if (a.status !== 'confirmed' && b.status === 'confirmed') return 1;
        return new Date(a.start_time) - new Date(b.start_time);
    });
});

const filteredAppointments = computed(() => {
    const twoWeeksAgo = new Date();
    twoWeeksAgo.setDate(twoWeeksAgo.getDate() - 14);
    return sortedAppointments.value.filter(appointment => {
        if (appointment.status !== 'cancelled') return true;
        return new Date(appointment.updated_at) > twoWeeksAgo;
    });
});

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
};

const formatTime = (timeString) => {
    return new Date(timeString).toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatShortDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long'
    });
};

const daysUntilRemoval = (updatedAt) => {
    const updatedDate = new Date(updatedAt);
    const removalDate = new Date(updatedDate);
    removalDate.setDate(removalDate.getDate() + 14);
    const diffTime = removalDate - new Date();
    return Math.max(0, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
};

const openCancelModal = (appointmentId) => {
    appointmentToCancel.value = appointmentId;
    showCancelModal.value = true;
};

const closeCancelModal = () => {
    showCancelModal.value = false;
    appointmentToCancel.value = null;
};

const cancelAppointment = async () => {
    try {
        await emit('cancel-appointment', appointmentToCancel.value);
        closeCancelModal();
    } catch {
        // Ошибка уже обработана в родительском компоненте
    }
};

const startEditing = (appointment) => {
    editingAppointmentId.value = appointment.id;
    newReason.value = appointment.reason || '';
};

const updateReason = (appointmentId) => {
    emit('update-reason', { id: appointmentId, reason: newReason.value });
    editingAppointmentId.value = null;
};

const toggleHistory = () => {
    showHistory.value = !showHistory.value;
};

const groupedCompletedAppointments = computed(() => {
    const groups = {};
    props.completedAppointments.forEach(appointment => {
        const year = new Date(appointment.start_time).getFullYear();
        if (!groups[year]) groups[year] = [];
        groups[year].push(appointment);
    });
    const sortedYears = Object.keys(groups).sort((a, b) => b - a);
    sortedYears.forEach(year => {
        groups[year].sort((a, b) => new Date(b.start_time) - new Date(a.start_time));
    });
    return { groups, sortedYears };
});
</script>

<style scoped>
.swiper {
    z-index: 0 !important;
}
</style>

<template>
    <!-- Переключение между текущими и завершенными приемами -->
    <div class="flex justify-between items-center my-4">
        <h3 class="text-lg font-medium text-gray-900">
            {{ showHistory ? 'История записей' : 'Мои записи' }}
        </h3>
        <button @click="toggleHistory" class="px-4 py-2 text-sm font-medium rounded-md" :class="{
            'bg-indigo-600 text-white hover:bg-indigo-700': !showHistory,
            'bg-gray-200 text-gray-700 hover:bg-gray-300': showHistory
        }">
            {{ showHistory ? 'Вернуться к текущим' : 'Просмотреть историю' }}
        </button>
    </div>

    <!-- Предстоящие записи - десктопная версия (карточки) -->
    <div v-if="!showHistory" class="hidden md:block">
        <div v-if="filteredAppointments.length > 0"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 items-start">
            <div v-for="appointment in filteredAppointments" :key="appointment.id"
                class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 transition-shadow flex flex-col h-auto">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center">
                                <span class="font-medium" :class="{
                                    'text-gray-900': appointment.status !== 'cancelled',
                                    'text-gray-500': appointment.status === 'cancelled'
                                }">
                                    {{ appointment.doctor.specialty.name }}
                                </span>
                                <span class="ml-2 px-2 py-1 text-xs rounded-full" :class="{
                                    'bg-green-100 text-green-800': appointment.status === 'confirmed',
                                    'bg-yellow-100 text-yellow-800': appointment.status === 'pending',
                                    'bg-red-100 text-red-800': appointment.status === 'cancelled'
                                }">
                                    {{
                                        appointment.status === 'confirmed' ? 'Подтверждено' :
                                            appointment.status === 'pending' ? 'Ожидание' :
                                                'Отменено'
                                    }}
                                </span>
                            </div>
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ appointment.doctor.user.full_name }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    {{ appointment.doctor.room || 'Кабинет не указан' }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ formatDate(appointment.start_time) }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ formatTime(appointment.start_time) }} - {{ formatTime(appointment.end_time) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Блок с причиной обращения или отмены -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ appointment.status === 'cancelled' ? 'Причина отмены' : 'Причина обращения' }}:
                        </label>
                        <div v-if="editingAppointmentId !== appointment.id" class="mt-1">
                            <p class="text-sm text-gray-600">
                                {{ appointment.status === 'cancelled' ? appointment.cancel_reason || 'Не указана' :
                                    appointment.reason || 'Не указана' }}
                            </p>

                            <!-- Уведомление об удалении отмененной записи -->
                            <div v-if="appointment.status === 'cancelled'"
                                class="mt-2 p-2 bg-blue-50 text-blue-700 text-sm rounded">
                                Эта запись автоматически скроется через {{ daysUntilRemoval(appointment.updated_at) }}
                                {{
                                    daysUntilRemoval(appointment.updated_at) === 1 ? 'день' :
                                        [2, 3, 4].includes(daysUntilRemoval(appointment.updated_at)) ? 'дня' : 'дней'
                                }}
                            </div>

                            <div v-if="appointment.status !== 'cancelled'"
                                class="mt-3 flex justify-between items-center">
                                <button @click="startEditing(appointment)"
                                    class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Редактировать
                                </button>
                                <button v-if="appointment.status !== 'cancelled'"
                                    @click="openCancelModal(appointment.id)"
                                    class="px-3 py-1 text-sm text-red-600 border border-red-300 rounded-md hover:bg-red-50 transition flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Отменить
                                </button>
                            </div>
                        </div>
                        <div v-else-if="appointment.status !== 'cancelled'" class="mt-1">
                            <textarea v-model="newReason" rows="3"
                                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                            <div class="mt-2 flex space-x-2 justify-end">
                                <button @click="editingAppointmentId = null"
                                    class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                    Отмена
                                </button>
                                <button @click="updateReason(appointment.id)"
                                    class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="px-6 py-20 text-center bg-white rounded-lg shadow">
            <p class="text-gray-500">У вас нет предстоящих записей</p>
            <a href="/appointments/create"
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Записаться на прием
            </a>
        </div>
    </div>

    <!-- Предстоящие записи - мобильная версия (свайпер) -->
    <div v-if="!showHistory" class="md:hidden">
        <div v-if="filteredAppointments.length > 0">
            <swiper :modules="swiperModules" :slides-per-view="1" :space-between="20" :pagination="{ clickable: true }"
                :loop="true" class="pb-8">
                <swiper-slide v-for="appointment in filteredAppointments" :key="appointment.id">
                    <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200 mx-2" :class="{
                        'opacity-70 bg-gray-50': appointment.status === 'cancelled'
                    }">
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">
                                            {{ appointment.doctor.specialty.name }}
                                        </span>
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full" :class="{
                                            'bg-green-100 text-green-800': appointment.status === 'confirmed',
                                            'bg-yellow-100 text-yellow-800': appointment.status === 'pending',
                                            'bg-red-100 text-red-800': appointment.status === 'cancelled'
                                        }">
                                            {{
                                                appointment.status === 'confirmed' ? 'Подтверждено' :
                                                    appointment.status === 'pending' ? 'Ожидание' :
                                                        'Отменено'
                                            }}
                                        </span>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ appointment.doctor.user.full_name }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                            </svg>
                                            {{ appointment.doctor.room || 'Кабинет не указан' }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ formatDate(appointment.start_time) }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ formatTime(appointment.start_time) }} - {{
                                                formatTime(appointment.end_time) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Блок с причиной обращения или отмены -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ appointment.status === 'cancelled' ? 'Причина отмены' : 'Причина обращения' }}:
                                </label>
                                <div v-if="editingAppointmentId !== appointment.id" class="mt-1">
                                    <p class="text-sm text-gray-600">
                                        {{ appointment.status === 'cancelled' ? appointment.cancel_reason || 'Неуказана'
                                            : appointment.reason || 'Не указана' }}
                                    </p>

                                    <!-- Уведомление об удалении отмененной записи -->
                                    <div v-if="appointment.status === 'cancelled'"
                                        class="mt-2 p-2 bg-blue-50 text-blue-700 text-sm rounded">
                                        Эта запись автоматически скроется через {{
                                            daysUntilRemoval(appointment.updated_at) }} {{
                                            daysUntilRemoval(appointment.updated_at) === 1 ? 'день' :
                                                [2, 3, 4].includes(daysUntilRemoval(appointment.updated_at)) ? 'дня' : 'дней'
                                        }}
                                    </div>

                                    <div v-if="appointment.status !== 'cancelled'"
                                        class="mt-3 flex justify-between items-center">
                                        <button @click="startEditing(appointment)"
                                            class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Редактировать
                                        </button>
                                        <button v-if="appointment.status !== 'cancelled'"
                                            @click="openCancelModal(appointment.id)"
                                            class="px-3 py-1 text-sm text-red-600 border border-red-300 rounded-md hover:bg-red-50 transition flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Отменить
                                        </button>
                                    </div>
                                </div>
                                <div v-else-if="appointment.status !== 'cancelled'" class="mt-1">
                                    <textarea v-model="newReason" rows="3"
                                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    <div class="mt-2 flex space-x-2 justify-end">
                                        <button @click="editingAppointmentId = null"
                                            class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                                            Отмена
                                        </button>
                                        <button @click="updateReason(appointment.id)"
                                            class="px-3 py-1 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                            Сохранить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </swiper-slide>
            </swiper>
        </div>

        <div v-else class="px-6 py-20 text-center bg-white rounded-lg shadow">
            <p class="text-gray-500">У вас нет предстоящих записей</p>
            <a href="/appointments/create"
                class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Записаться на прием
            </a>
        </div>
    </div>

    <!-- История завершенных записей -->
    <div v-if="showHistory" class="bg-white overflow-hidden shadow rounded-lg">
        <div v-if="completedAppointments.length > 0" class="divide-y divide-gray-200">
            <div v-for="year in groupedCompletedAppointments.sortedYears" :key="year" class="px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900 mb-3">{{ year }}</h3>
                <div class="space-y-3">
                    <div v-for="appointment in groupedCompletedAppointments.groups[year]" :key="appointment.id"
                        class="border-l-4 border-indigo-200 pl-4 py-2">
                        <button @click="toggleAppointmentDetails(appointment)"
                            class="text-left w-full hover:bg-gray-50 p-2 rounded">
                            <div class="font-medium text-gray-700">
                                {{ formatShortDate(appointment.start_time) }}
                                <span class="text-gray-500">({{ appointment.doctor.specialty.name }})</span>
                            </div>
                        </button>

                        <!-- Детали приема (показываются при клике) -->
                        <div v-if="selectedAppointment && selectedAppointment.id === appointment.id"
                            class="mt-2 ml-2 p-3 bg-gray-50 rounded-lg">
                            <div class="text-sm text-gray-600">
                                Врач: {{ appointment.doctor.user.full_name }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Причина обращения: {{ appointment.reason || 'Не указана' }}
                            </div>
                            <div class="text-sm text-gray-600">
                                Заключение:
                                <button @click="openMedicalReport(appointment)"
                                    class="text-blue-600 underline hover:text-blue-800">
                                    Посмотреть детали
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="px-6 py-20 text-center">
            <p class="text-gray-500">У вас нет завершенных записей</p>
        </div>
    </div>

    <!-- Модальное окно с медицинским заключением -->
    <div v-if="showMedicalReportModal"
        class="fixed inset-0 bg-gray-900/70 flex items-center justify-center p-4 z-50 transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Шапка модального окна -->
            <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-white">Медицинское заключение</h3>
                    <p class="text-blue-100 text-sm mt-1">
                        {{ medicalReportData.date }} | {{ medicalReportData.doctorSpeciality || 'Специальность не указана' }}
                    </p>
                </div>
                <button @click="showMedicalReportModal = false"
                    class="text-blue-200 hover:text-white transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Основное содержимое -->
            <div class="p-6 overflow-y-auto flex-1">
                <!-- Медицинская информация -->
                <div class="space-y-6">
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-md font-medium text-gray-700">Пациент:</h4>
                            <p class="text-gray-600">{{ medicalReportData.patientName }}</p>
                        </div>

                        <div>
                            <h4 class="text-md font-medium text-gray-700">Врач:</h4>
                            <p class="text-gray-600">{{ medicalReportData.doctorName }}</p>
                        </div>

                        <div>
                            <h4 class="text-md font-medium text-gray-700">Дата приема:</h4>
                            <p class="text-gray-600">{{ medicalReportData.date }}</p>
                        </div>

                        <div>
                            <h4 class="text-md font-medium text-gray-700">Диагноз:</h4>
                            <p class="text-gray-600">{{ medicalReportData.diagnosis }}</p>
                        </div>

                        <div>
                            <h4 class="text-md font-medium text-gray-700">Рекомендации:</h4>
                            <p class="text-gray-600">{{ medicalReportData.recommendations }}</p>
                        </div>

                        <div>
                            <h4 class="text-md font-medium text-gray-700">Назначения:</h4>
                            <p class="text-gray-600">{{ medicalReportData.prescription }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Футер модального окна -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Документ сформирован: {{ new Date().toLocaleDateString() }}
                </div>
                <div>
                    <button @click="showMedicalReportModal = false"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Закрыть
                    </button>
                    <button @click="downloadPdf"
                        class="ml-4 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        <svg class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Скачать PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальное окно подтверждения отмены -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">Подтверждение отмены записи</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-600">Вы уверены, что хотите отменить запись? Это действие нельзя
                        отменить.</p>
                </div>
            </div>
            <div class="bg-gray-50 rounded-b-lg px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button @click="cancelAppointment" type="button"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Отменить запись
                </button>
                <button @click="closeCancelModal" type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Вернуться
                </button>
            </div>
        </div>
    </div>
</template>