<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        $patients = User::whereHas('roles', function($q) {
            $q->where('name', 'patient');
        })->limit(7)->get();

        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->limit(12)->get();

        $registrars = User::whereHas('roles', function($q) {
            $q->where('name', 'registrar');
        })->get();

        $reasons = [
            'Плановый осмотр',
            'Головная боль',
            'Боли в спине',
            'Повышенное давление',
            'Простуда',
            'Консультация',
            'Анализы',
            'Боль в суставах',
            'Аллергическая реакция',
            'Дискомфорт в животе',
            'Повторный прием',
            'Вакцинация',
            'Справка',
            'Профилактический осмотр'
        ];

        // Статусы приемов
        $statuses = ['completed', 'pending', 'canceled', 'confirmed'];

        // Создаем 25 приемов
        for ($i = 0; $i < 25; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            $registrar = $registrars->random();

            // Генерируем случайную дату и время в ближайшие 30 дней
            $days = rand(0, 30);
            $hours = rand(9, 18); // Рабочие часы с 9 до 18
            $minutes = rand(0, 1) ? 0 : 30; // Время с шагом 30 минут

            $startTime = Carbon::now()
                ->addDays($days)
                ->setHour($hours)
                ->setMinute($minutes)
                ->setSecond(0);

            $endTime = (clone $startTime)->addMinutes(30); // Прием длится 30 минут

            $status = $statuses[array_rand($statuses)];
            $cancelReason = $status === 'canceled' ? 'Пациент отменил запись' : null;

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'registrar_id' => $registrar->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'reason' => $reasons[array_rand($reasons)],
                'status' => $status,
                'cancel_reason' => $cancelReason,
                'created_at' => now()->subDays(rand(0, 10)), // Дата создания записи
            ]);
        }
    }
}