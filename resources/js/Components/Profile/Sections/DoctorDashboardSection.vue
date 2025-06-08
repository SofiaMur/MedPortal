<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Calendar } from 'v-calendar';
import 'v-calendar/dist/style.css';
import { jsPDF } from "jspdf";
import DoctorNotes from '@/Components/Profile/Articles/Notes.vue';

const props = defineProps({
    auth: Object,
    appointments: Array,
    completedAppointments: Array,
    completedAppointmentsHistory: Array
});

const groupByMonth = (appointments) => {
    return appointments.reduce((acc, appointment) => {
        const month = new Date(appointment.start_time).getMonth();
        if (!acc[month]) acc[month] = [];
        acc[month].push(appointment);
        return acc;
    }, {});
};

const formatMonth = (monthIndex) => {
    const months = [
        'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    ];
    return months[monthIndex];
};

const formatDayAndTime = (dateString) => {
    const date = new Date(dateString);
    return `${date.getDate()} ${formatMonth(date.getMonth()).toLowerCase()}, ${date.getHours()}:${date.getMinutes().toString().padStart(2, '0')}`;
};

const today = new Date();
today.setHours(0, 0, 0, 0);

const selectedAppointment = ref(null);
const selectedDate = ref(new Date(today));
const selectedAppointments = ref([]);
const showCancelModal = ref(false);
const appointmentToCancel = ref(null);
const showPatientDetails = ref(false);
const selectedPatient = ref(null);
const cancelError = ref(null);
const completeError = ref(null);
const showAllHistory = ref(false);

const loadAllHistory = () => {
    showAllHistory.value = true;
    showCompletedAppointments.value = false;
};

const medicalReport = useForm({
    diagnosis: '',
    recommendations: '',
    prescription: '',
    recentlySuccessful: false
});

const currentPage = ref(1);
const appointmentsPerPage = 6;

const mockData = reactive({
    auth: props.auth,
    appointments: props.appointments.map(app => ({
        ...app,
        start_time: new Date(app.start_time),
        end_time: new Date(app.end_time)
    })),
    completedAppointments: props.completedAppointments.map(app => ({
        ...app,
        start_time: new Date(app.start_time),
        end_time: new Date(app.end_time)
    }))
});

const formatDate = (date) => new Date(date).toLocaleDateString('ru-RU');
const formatTime = (date) => new Date(date).toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
const formatShortDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long'
    });
};

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
        patientName: appointment.patient.full_name,
        patientBirthDate: formatDate(appointment.patient.birth_date),
        patientGender: appointment.patient.gender,
        doctorName: props.auth.user.full_name,
        doctorSpeciality: props.auth.user.specialty,
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
        // Загрузка шрифтов 
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
                value: medicalReportData.value.patientGender === 'male' ? 'мужской' : 'женский'
            },
            {
                label: 'Дата рождения:',
                value: medicalReportData.value.patientBirthDate
            },
            { label: 'Дата осмотра:', value: medicalReportData.value.date },
            { label: 'Лечащий врач:', value: medicalReportData.value.doctorName },
            { label: 'Специализация:', value: medicalReportData.value.doctorSpeciality }
        ];

        let y = 50; // Начальная позиция
        fields.forEach(item => {
            doc.setFontSize(12);
            doc.setFont('Roboto', 'bold');
            doc.text(item.label, 20, y);
            doc.setFont('Roboto', 'normal');
            doc.text(item.value, 60, y);
            y += 8; // Уменьшен интервал между строками
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

const groupedCompletedAppointments = computed(() => {
    const groups = {};
    props.completedAppointmentsHistory.forEach(appointment => {
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

const paginatedAppointments = computed(() => {
    const start = (currentPage.value - 1) * appointmentsPerPage;
    const end = start + appointmentsPerPage;
    return selectedAppointments.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(selectedAppointments.value.length / appointmentsPerPage));
const changePage = (page) => { currentPage.value = page; };

const canCancelAppointment = (appointment) => {
    const now = new Date();
    const appointmentTime = new Date(appointment.start_time);
    const hoursDiff = (appointmentTime - now) / (1000 * 60 * 60);
    return hoursDiff >= 24;
};

const canCompleteAppointment = computed(() => {
    if (!selectedAppointment.value) return false;

    const now = new Date();
    const appointmentTime = new Date(selectedAppointment.value.start_time);
    const hoursDiff = (appointmentTime - now) / (1000 * 60 * 60);

    return hoursDiff <= 12;
});

const openPatientDetails = (appointment) => {
    medicalReport.reset();
    medicalReport.recentlySuccessful = false;

    selectedPatient.value = appointment.patient;
    selectedAppointment.value = appointment;

    if (appointment.medical_report) {
        medicalReport.diagnosis = appointment.medical_report.diagnosis || '';
        medicalReport.recommendations = appointment.medical_report.recommendations || '';
        medicalReport.prescription = appointment.medical_report.prescription || '';
    } else {
        medicalReport.diagnosis = '';
        medicalReport.recommendations = '';
        medicalReport.prescription = '';
    }

    if (appointment.status === 'pending') {
        completeError.value = 'Нельзя завершить приём в статусе "Ожидание"';
        setTimeout(() => completeError.value = null, 3000);
    } else if (!canCompleteAppointmentNow(appointment)) {
        completeError.value = 'Нельзя завершить приём более чем за 12 часов до начала';
        setTimeout(() => completeError.value = null, 3000);
    }

    showPatientDetails.value = true;
};

const openCancelModal = (appointment) => {
    if (!canCancelAppointment(appointment)) {
        cancelError.value = 'Нельзя отменить прием менее чем за 24 часа до начала';
        setTimeout(() => cancelError.value = null, 3000);
        return;
    }

    appointmentToCancel.value = appointment;
    showCancelModal.value = true;
};

const updateSelectedAppointments = () => {
    if (!selectedDate.value) return;

    const selDate = new Date(selectedDate.value);
    selDate.setHours(0, 0, 0, 0);

    selectedAppointments.value = mockData.appointments.filter(app => {
        const appDate = new Date(app.start_time);
        appDate.setHours(0, 0, 0, 0);
        return appDate.getTime() === selDate.getTime();
    });

    currentPage.value = 1;
};

const handleDateClick = (date) => {
    const dateObj = date instanceof Date ? date : new Date(date.year, date.month - 1, date.day);
    selectedDate.value = dateObj;
    updateSelectedAppointments();
};

const calendarAttributes = computed(() => {
    const appointmentsByDate = {};

    mockData.appointments.forEach(appointment => {
        const date = new Date(appointment.start_time);
        date.setHours(0, 0, 0, 0);
        const dateKey = date.getTime();

        if (!appointmentsByDate[dateKey]) {
            appointmentsByDate[dateKey] = {
                count: 0,
                statuses: new Set(),
                appointments: []
            };
        }

        appointmentsByDate[dateKey].count++;
        appointmentsByDate[dateKey].statuses.add(appointment.status);
        appointmentsByDate[dateKey].appointments.push(appointment);
    });

    return Object.entries(appointmentsByDate).map(([dateKey, data]) => {
        let dotColor = 'gray';
        if (data.statuses.has('confirmed')) dotColor = 'blue';
        else if (data.statuses.has('pending')) dotColor = 'gray';

        const popoverContent = data.appointments
            .map(app => `${formatTime(app.start_time)} - ${app.patient.full_name} (${app.status === 'confirmed' ? 'Подтвержден' : app.status === 'pending' ? 'Ожидание' : 'Отменен'})`)
            .join('<br>');

        return {
            key: `appointment-${dateKey}`,
            dates: new Date(parseInt(dateKey)),
            dot: { color: dotColor, class: 'opacity-75' },
            popover: {
                label: `Приемов: ${data.count}`,
                description: popoverContent,
                hideIndicator: true
            }
        };
    }).concat([{
        key: 'today',
        dates: today,
        highlight: { color: 'blue', fillMode: 'light', class: 'today-highlight' }
    }]);
});

const showCompletedAppointments = ref(false);
const completedAppointments = ref([]);
const searchQuery = ref('');

const loadCompletedAppointments = () => {
    const now = new Date();
    const seventyTwoHoursAgo = new Date(now.getTime() - (72 * 60 * 60 * 1000));

    completedAppointments.value = mockData.completedAppointments
        .filter(app => new Date(app.end_time) > seventyTwoHoursAgo);

    showCompletedAppointments.value = true;
    showAllHistory.value = false;
};

const filteredCompletedAppointments = computed(() => {
    if (!searchQuery.value) return completedAppointments.value;

    const query = searchQuery.value.toLowerCase();
    return completedAppointments.value.filter(app => {
        return (
            app.patient.full_name.toLowerCase().includes(query) ||
            (app.medical_report?.diagnosis?.toLowerCase().includes(query) ?? false) ||
            app.reason.toLowerCase().includes(query)
        );
    });
});

const formattedDateTitle = computed(() => {
    const selected = new Date(selectedDate.value);
    selected.setHours(0, 0, 0, 0);

    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    if (selected.getTime() === today.getTime()) {
        return { text: 'Сегодня', class: 'text-blue-700 font-bold' };
    } else if (selected.getTime() === tomorrow.getTime()) {
        return { text: 'Завтра', class: '' };
    } else {
        return { text: formatDate(selected), class: '' };
    }
});

const canCompleteAppointmentNow = (appointment) => {
    if (appointment.status === 'pending') return false;

    const now = new Date();
    const appointmentTime = new Date(appointment.start_time);
    const hoursDiff = (appointmentTime - now) / (1000 * 60 * 60);

    return hoursDiff <= 12;
};

const completeAppointment = () => {
    if (!canCompleteAppointmentNow(selectedAppointment.value)) {
        completeError.value = 'Нельзя завершить этот приём';
        setTimeout(() => completeError.value = null, 3000);
        return;
    }

    if (!medicalReport.diagnosis.trim()) {
        completeError.value = 'Диагноз обязателен для заполнения';
        setTimeout(() => completeError.value = null, 3000);
        return;
    }

    if (!medicalReport.recommendations.trim()) {
        completeError.value = 'Рекомендации обязательны для заполнения';
        setTimeout(() => completeError.value = null, 3000);
        return;
    }

    medicalReport.post(`/doctor/dashboard/appointments/${selectedAppointment.value.id}/complete`, {
        onSuccess: () => {
            const index = mockData.appointments.findIndex(app => app.id === selectedAppointment.value.id);
            if (index !== -1) {
                mockData.appointments.splice(index, 1);
            }

            updateSelectedAppointments();

            updateSelectedAppointments();
            medicalReport.recentlySuccessful = true;

            setTimeout(() => {
                showPatientDetails.value = false;
                medicalReport.reset();
                medicalReport.recentlySuccessful = false;
            }, 2000);
        },
        onError: (errors) => {
            completeError.value = 'Ошибка при сохранении: ' +
                (errors.message || 'Проверьте заполнение полей');
            setTimeout(() => completeError.value = null, 3000);
        }
    });
};

const saveMedicalReport = () => {
    if (!medicalReport.diagnosis || !medicalReport.recommendations) {
        return;
    }

    medicalReport.post(`/doctor/dashboard/appointments/${selectedAppointment.value.id}/complete`, {
        onSuccess: () => {
            medicalReport.recentlySuccessful = true;
            setTimeout(() => {
                medicalReport.recentlySuccessful = false;
            }, 2000);
        }
    });
};

const cancelAppointment = () => {
    if (!appointmentToCancel.value.cancel_reason) return;

    router.post(`/doctor/dashboard/appointments/${appointmentToCancel.value.id}/cancel`, {
        cancel_reason: appointmentToCancel.value.cancel_reason
    }, {
        onSuccess: () => {
            const index = mockData.appointments.findIndex(app => app.id === appointmentToCancel.value.id);
            if (index !== -1) {
                mockData.appointments.splice(index, 1);
            }

            updateSelectedAppointments();

            showCancelModal.value = false;
            updateSelectedAppointments();
        },
        onError: () => {
            cancelError.value = 'Ошибка при отмене приёма';
            setTimeout(() => cancelError.value = null, 3000);
        }
    });
};

watch(selectedDate, updateSelectedAppointments, { immediate: true });

onMounted(() => {
    selectedDate.value = new Date(today);
    updateSelectedAppointments();
});
</script>

<style scoped>
.custom-calendar {
    --vc-font-family: inherit;
    --vc-day-content-hover-bg: #e0e7ff;
    --vc-accent-50: #e0e7ff;
    --vc-accent-100: #c7d2fe;
    --vc-accent-200: #a5b4fc;
    --vc-accent-600: #4f46e5;
}

.custom-calendar .is-selected {
    background-color: var(--vc-accent-600);
    color: white;
    border-radius: 50%;
}

.custom-calendar .is-today {
    background-color: transparent;
    color: inherit;
    border: 2px solid var(--vc-accent-600);
    border-radius: 50%;
    box-shadow: none;
}

.custom-calendar .vc-day-box .vc-dots {
    display: flex;
    justify-content: center;
    gap: 2px;
}

.custom-calendar .vc-day-box .vc-dots .vc-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
}

.custom-calendar .vc-popover-content {
    max-width: 300px;
    padding: 0.5rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.custom-calendar .vc-popover-content-wrapper {
    z-index: 10;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}


.app-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
</style>

<template>
    <div class="flex flex-col md:flex-row gap-6 h-full">
        <!-- Основная панель -->
        <div class="w-full md:w-3/4">
            <div class="bg-white rounded-lg">
                <div class="flex justify-between items-center mb-4 border-b">
                    <div class="flex">
                        <button @click="showCompletedAppointments = false; showAllHistory = false" class="px-4 py-2"
                            :class="{
                                'bg-white text-blue-600 border-l': !showCompletedAppointments && !showAllHistory,
                                'bg-gray-100 text-gray-600': showCompletedAppointments || showAllHistory
                            }">
                            Активные приемы
                        </button>
                        <button @click="loadCompletedAppointments" class="px-4 py-2" :class="{
                            'bg-white text-blue-600': showCompletedAppointments,
                            'bg-gray-100 text-gray-600': !showCompletedAppointments
                        }">
                            Завершенные приемы (72ч)
                        </button>
                        <button @click="loadAllHistory" class="px-4 py-2" :class="{
                            'bg-white text-blue-600 border-r': showAllHistory,
                            'bg-gray-100 text-gray-600': !showAllHistory
                        }">
                            История приемов
                        </button>
                    </div>
                </div>

                <!-- Список приемов (показывается когда не открыты детали) -->
                <div v-if="!showPatientDetails && !showCompletedAppointments && !showAllHistory">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold py-6">
                            Приемы на <span :class="formattedDateTitle.class">{{ formattedDateTitle.text }}</span>
                        </h2>
                        <div v-if="selectedAppointments.length > 0" class="text-sm text-gray-500">
                            Всего: {{ selectedAppointments.length }}
                        </div>
                    </div>

                    <!-- Список приемов -->
                    <div v-if="selectedAppointments.length > 0">
                        <div class="space-y-4 mb-4">
                            <div v-for="appointment in paginatedAppointments" :key="appointment.id"
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-md text-lg flex items-center gap-2">
                                            <span>{{ appointment.patient.full_name }}</span>
                                            <span class="text-sm px-2 py-1 rounded-full" :class="{
                                                '': appointment.status === 'confirmed',
                                                'bg-blue-100 text-blue-800': appointment.status === 'pending',
                                            }">
                                                {{ appointment.status === 'confirmed' ? '' : 'Ожидание' }}
                                            </span>
                                        </h3>

                                        <div class="mt-2 space-y-1 text-sm text-gray-600">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ formatTime(appointment.start_time) }} - {{
                                                    formatTime(appointment.end_time) }}
                                            </div>
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                </svg>
                                                Кабинет: {{ mockData.auth.user.room }}
                                            </div>
                                            <div class="flex items-center">
                                                Причина: {{ appointment.reason || 'Не указана' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="cancelError"
                                        class="fixed bottom-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                                        <p>{{ cancelError }}</p>
                                    </div>

                                    <div v-if="completeError"
                                        class="fixed bottom-4 right-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                                        <p>{{ completeError }}</p>
                                    </div>

                                    <div class="flex gap-2">
                                        <button @click="openPatientDetails(appointment)"
                                            class="px-3 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 transition">
                                            Подробнее
                                        </button>
                                        <button @click="openCancelModal(appointment)"
                                            class="px-3 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200 transition"
                                            v-if="appointment.status !== 'cancelled'">
                                            Отменить
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Пагинация -->
                        <div v-if="totalPages > 1" class="flex justify-center items-center gap-2 mt-4">
                            <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1"
                                class="px-3 py-1 border rounded disabled:opacity-50">
                                Назад
                            </button>

                            <span v-for="page in totalPages" :key="page" @click="changePage(page)"
                                class="px-3 py-1 cursor-pointer rounded" :class="{
                                    'bg-blue-100 text-blue-800': page === currentPage,
                                    'hover:bg-gray-100': page !== currentPage
                                }">
                                {{ page }}
                            </span>

                            <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages"
                                class="px-3 py-1 border rounded disabled:opacity-50">
                                Вперед
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-16 lg:py-28 text-gray-500">
                        На выбранную дату приемов не запланировано
                    </div>
                </div>

                <!-- Список завершенных приемов -->
                <div v-if="showCompletedAppointments && !showPatientDetails && !showAllHistory">
                    <div class="p-4">
                        <div class="relative">
                            <input v-model="searchQuery" type="text" placeholder="Поиск по ФИО, диагнозу или причине..."
                                class="w-full p-2 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <div v-if="filteredCompletedAppointments.length > 0" class="space-y-4 mt-4 mb-4">
                        <div v-for="appointment in filteredCompletedAppointments" :key="'completed-' + appointment.id"
                            class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="font-md text-lg flex items-center gap-2">
                                        <span>{{ appointment.patient.full_name }}</span>
                                        <span class="text-sm px-2 py-1 rounded-full bg-green-100 text-green-800">
                                            Завершен
                                        </span>
                                    </h3>

                                    <div class="mt-2 space-y-1 text-sm text-gray-600">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ formatDate(appointment.end_time) }}, {{
                                                formatTime(appointment.start_time) }} - {{ formatTime(appointment.end_time)
                                            }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-md text-gray-500 mb-1">Диагноз</p>
                                            <p>{{ appointment.medical_report?.diagnosis || 'Не указан' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <button @click="openPatientDetails(appointment)"
                                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200 transition">
                                    Редактировать
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="completedAppointments.length === 0" class="text-center py-32 text-gray-500">
                        Нет завершенных приемов за последние 72 часа
                    </div>
                    <div v-else class="text-center py-24 text-gray-500">
                        Ничего не найдено по вашему запросу
                    </div>
                </div>

                <!-- История завершенных приемов врача -->
                <div v-if="showAllHistory && !showPatientDetails && !showCompletedAppointments"
                    class="bg-white overflow-hidden border rounded-lg">
                    <div v-if="completedAppointmentsHistory.length > 0" class="divide-y divide-gray-200">
                        <!-- Группировка по годам -->
                        <div v-for="year in groupedCompletedAppointments.sortedYears" :key="year" class="px-6 py-4">
                            <h3 class="text-lg font-md text-gray-900 mb-3 flex items-center justify-between">
                                <span>{{ year }}</span>
                                <span class="text-sm font-normal text-gray-500">
                                    {{ groupedCompletedAppointments.groups[year].length }} приёмов
                                </span>
                            </h3>

                            <!-- Группировка по месяцам -->
                            <div v-for="(monthAppointments, month) in groupByMonth(groupedCompletedAppointments.groups[year])"
                                :key="month" class="mb-4">
                                <h4 class="text-md font-md text-gray-700 mb-2">{{ formatMonth(month) }}</h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <!-- Карточка приема -->
                                    <div v-for="appointment in monthAppointments" :key="appointment.id"
                                        class="border rounded-lg hover:shadow transition-shadow">
                                        <button class="w-full text-left p-3">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <div class="font-md text-blue-800">
                                                        {{ formatDayAndTime(appointment.start_time) }}
                                                    </div>
                                                    <div class="text-sm text-gray-600 mt-1">
                                                        {{ appointment.patient.full_name }}
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                        <div class="px-3 pb-3 -mt-1">
                                            <div class="border-t pt-3 text-sm space-y-2">
                                                <div class="flex items-center">
                                                    <span class="font-md text-gray-700">Заключение:</span>
                                                    <button @click="openMedicalReport(appointment)"
                                                        class="ml-1 text-blue-600 hover:text-blue-800 underline flex items-center">
                                                        Подробнее
                                                    </button>
                                                </div>
                                            </div>
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
                    <div
                        class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col">
                        <!-- Шапка модального окна -->
                        <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-bold text-white">Медицинское заключение</h3>
                                <p class="text-blue-100 text-sm mt-1">
                                    {{ medicalReportData.date }} | {{ medicalReportData.doctorSpeciality ||
                                    'Специальность не указана' }}
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
                                        <h4 class="text-md font-md text-gray-700">Пациент:</h4>
                                        <p class="text-gray-600">{{ medicalReportData.patientName }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-md font-md text-gray-700">Врач:</h4>
                                        <p class="text-gray-600">{{ medicalReportData.doctorName }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-md font-md text-gray-700">Дата приема:</h4>
                                        <p class="text-gray-600">{{ medicalReportData.date }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-md font-md text-gray-700">Диагноз:</h4>
                                        <p class="text-gray-600">{{ medicalReportData.diagnosis }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-md font-md text-gray-700">Рекомендации:</h4>
                                        <p class="text-gray-600">{{ medicalReportData.recommendations }}</p>
                                    </div>

                                    <div>
                                        <h4 class="text-md font-md text-gray-700">Назначения:</h4>
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
                                    <svg class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Скачать PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Секция с деталями пациента -->
                <div v-if="showPatientDetails && selectedAppointment" class="p-2">
                    <div class="flex justify-between items-center mb-6 mt-4">
                        <h2 class="text-xl font-bold">Детали приема</h2>
                        <button @click="showPatientDetails = false"
                            class="px-3 py-2 bg-gray-100 rounded hover:bg-gray-200">
                            Назад к списку
                        </button>
                    </div>

                    <!-- Детали приема - верхняя часть -->
                    <div class="bg-white rounded-lg shadow-sm border mb-6">
                        <div class="p-6 border-b">
                            <h2 class="text-2xl font-bold text-blue-700 mb-1">{{ selectedPatient.full_name }}</h2>
                        </div>

                        <!-- Информация о приеме -->
                        <div class="p-4">
                            <p class="text-sm text-gray-500 mb-1">Причина обращения</p>
                            <p class="font-md">{{ selectedAppointment.reason || 'Не указана' }}</p>
                        </div>
                    </div>

                    <!-- Данные пациента -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Основная информация -->
                        <div class="bg-white rounded-lg border p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Основная информация</h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-md text-gray-500 mb-1">Дата рождения</p>
                                    <p class="text-gray-800">{{ formatDate(new Date(selectedPatient.birth_date)) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-md text-gray-500 mb-1">Пол</p>
                                    <p class="text-gray-800">{{ selectedPatient.gender === 'male' ? 'Мужской' :
                                        'Женский' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-md text-gray-500 mb-1">Номер телефона</p>
                                    <p class="text-gray-800">{{ selectedPatient.phone || 'Не указан' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Медицинская информация -->
                        <div class="bg-white rounded-lg border p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4 pb-2 border-b">Медицинская информация
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <p class="text-sm font-md text-gray-500 mb-1">Аллергии</p>
                                    <p class="text-gray-800">{{ selectedPatient.allergies || 'Не указаны' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-md text-gray-500 mb-1">Хронические заболевания</p>
                                    <p class="text-gray-800">{{ selectedPatient.chronic_diseases || 'Не указаны' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Форма заключения -->
                    <div class="border-t pt-6">
                        <h3 class="font-bold text-lg mb-4">Медицинское заключение</h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-md text-gray-700 mb-1">Диагноз*</label>
                                <input v-model="medicalReport.diagnosis" type="text" class="w-full border rounded p-2"
                                    required>
                            </div>

                            <div>
                                <label class="block text-sm font-md text-gray-700 mb-1">Рекомендации*</label>
                                <textarea v-model="medicalReport.recommendations" class="w-full border rounded p-2 h-32"
                                    required></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-md text-gray-700 mb-1">Рецепт*</label>
                                <textarea v-model="medicalReport.prescription"
                                    class="w-full border rounded p-2 h-24"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button @click="showPatientDetails = false" class="px-4 py-2 border rounded">
                                Назад
                            </button>
                            <button v-if="selectedAppointment.status === 'completed'" @click="saveMedicalReport"
                                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                                :disabled="!medicalReport.diagnosis || !medicalReport.recommendations">
                                Сохранить изменения
                            </button>
                            <button v-else @click="completeAppointment"
                                :disabled="!canCompleteAppointmentNow(selectedAppointment) || medicalReport.processing"
                                :class="{
                                    'bg-green-600 hover:bg-green-700': canCompleteAppointmentNow(selectedAppointment),
                                    'bg-gray-400 cursor-not-allowed': !canCompleteAppointmentNow(selectedAppointment),
                                    'opacity-50': medicalReport.processing
                                }" class="text-white px-4 py-2 rounded transition-colors">
                                <span v-if="medicalReport.processing">Сохранение...</span>
                                <span v-else>Завершить приём</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-3/5 md:w-1/2 lg:w-1/4 md:mt-8 sticky top-32">
            <!-- Календарь -->
            <div v-if="!showPatientDetails && !showCompletedAppointments" class="bg-white rounded-lg border md:pl-6">
                <Calendar v-model="selectedDate" @dayclick="handleDateClick" :attributes="calendarAttributes"
                    :min-date="new Date()" locale="ru" is-expanded trim-weeks class="custom-calendar" transparent
                    borderless />
            </div>
            <div class="mt-6">
                <DoctorNotes />
            </div>


        </div>
    </div>

    <!-- Всплывающее окно об успешном завершении приема -->
    <Transition name="fade">
        <div v-if="medicalReport.recentlySuccessful" class="fixed inset-0 flex items-center justify-center z-50">
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            <div class="bg-white rounded-lg p-6 shadow-xl z-10 max-w-sm w-full mx-4">
                <div class="flex items-center justify-center">
                    <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="mt-3 text-lg font-md text-center text-gray-900">Заключение успешно оформлено</h3>
            </div>
        </div>
    </Transition>

    <!-- Модальное окно отмены приема -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-md mb-4">Отмена приема</h3>
            <p class="mb-4">Вы уверены, что хотите отменить прием у {{ appointmentToCancel.patient.full_name }}?</p>

            <div class="mb-4">
                <label class="block text-sm font-md text-gray-700 mb-1">Причина отмены</label>
                <textarea v-model="appointmentToCancel.cancel_reason" class="w-full border rounded p-2"
                    placeholder="Укажите причину отмены..." required></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button @click="showCancelModal = false" class="px-4 py-2 border rounded">
                    Отмена
                </button>
                <button @click="cancelAppointment" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    :disabled="!appointmentToCancel.cancel_reason">
                    Подтвердить отмену
                </button>
            </div>
        </div>
    </div>
</template>