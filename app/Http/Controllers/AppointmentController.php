<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request)
    {
        try {
            $user = auth()->user();

            // Проверка заполненности профиля
            if (!$user->is_profile_completed) {
                return redirect()->back()->with('error', 'Пожалуйста, заполните профиль перед записью');
            }

            // Проверка лимита активных записей
            $activeAppointmentsCount = Appointment::where('patient_id', $user->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            if ($activeAppointmentsCount >= 6) {
                return redirect()->back()->with('error', 'Вы можете иметь не более 6 активных записей одновременно');
            }

            $validated = $request->validated();
            $doctor = Doctor::findOrFail($validated['doctor_id']);
            $start_time = Carbon::parse($validated['start_time']);
            $end_time = $start_time->copy()->addMinutes($doctor->appointment_duration);

            // Проверка существующей записи к этому врачу
            $existingAppointment = Appointment::where('patient_id', $user->id)
                ->where('doctor_id', $doctor->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->first();

            if ($existingAppointment) {
                return redirect()->back()->with('error', 'У вас уже есть активная запись к этому врачу');
            }

            // Проверка конфликта времени
            if ($this->hasAppointmentConflict($doctor, $start_time, $end_time)) {
                return redirect()->back()->with('error', 'Это время уже занято');
            }

            // Создание записи
            Appointment::create([
                'patient_id' => $user->id,
                'doctor_id' => $doctor->id,
                'registrar_id' => $user->is_admin ? $user->id : null,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);

            return redirect()->route('public.doctors')->with('success', 'Запись успешно создана!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Ошибка сервера: ' . $e->getMessage());
        }
    }
    public function countActive()
    {
        $count = Appointment::where('patient_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return response()->json(['count' => $count]);
    }

    public function checkDoctor($doctorId)
    {
        $exists = Appointment::where('patient_id', auth()->id())
            ->where('doctor_id', $doctorId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function getOccupiedTimes(Doctor $doctor, Request $request)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        return response()->json(
            $this->getOccupiedTimesForDate($doctor, $request->input('date'))
        );
    }

    private function hasAppointmentConflict(Doctor $doctor, Carbon $start_time, Carbon $end_time): bool
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time])
                    ->orWhere(function ($q) use ($start_time, $end_time) {
                        $q->where('start_time', '<', $start_time)
                            ->where('end_time', '>', $end_time);
                    });
            })
            ->exists();
    }

    private function getOccupiedTimesForDate(Doctor $doctor, string $date): array
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->whereDate('start_time', $date)
            ->get()
            ->map(function ($appointment) {
                return [
                    'start' => Carbon::parse($appointment->start_time)->format('H:i'),
                    'end' => Carbon::parse($appointment->end_time)->format('H:i')
                ];
            })
            ->toArray();
    }
}
