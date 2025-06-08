<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Role;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\StoreUserRequest;
use Carbon\Carbon;

use Illuminate\Support\Str;

class RegistrarDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Получаем список приемов для регистратора
        $appointments = Appointment::query()
            ->with(['patient', 'doctor.user', 'doctor.specialty', 'registrar:id'])
            ->where('registrar_id', $user->id)
            ->whereIn('status', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CANCELLED
            ])
            ->orderBy('start_time')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'patient_name' => $appointment->patient->full_name,
                    'doctor_name' => $appointment->doctor->user->full_name,
                    'specialty' => $appointment->doctor->specialty->name,
                    'room' => $appointment->doctor->room,
                    'start_time' => $appointment->start_time->format('d.m.Y H:i'),
                    'end_time' => $appointment->end_time->format('d.m.Y H:i'),
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'cancel_reason' => $appointment->cancel_reason,
                ];
            });

        // Получаем все специальности
        $specialties = Specialty::all();

        // Инициализируем переменные для поиска пациента и доступного времени
        $patient = null;
        $availableTimes = [];
        $doctors = [];

        // Поиск пациента по СНИЛС (если передан параметр snils)
        if ($request->has('snils')) {
            $cleanSnils = preg_replace('/[^0-9]/', '', $request->snils);

            $patient = User::get()
                ->first(function ($user) use ($cleanSnils) {
                    // Дешифруем СНИЛС для каждого пользователя и сравниваем
                    $userSnils = preg_replace('/[^0-9]/', '', $user->snils);
                    return $userSnils === $cleanSnils;
                });
        }

        // Получаем врачей по специальности (если передан specialty_id)
        if ($request->has('specialty_id')) {
            $doctors = Doctor::with(['user', 'specialty'])
                ->where('specialty_id', $request->specialty_id)
                ->whereHas('user', function ($q) {
                    $q->where('is_profile_completed', true);
                })
                ->get()
                ->map(function ($doctor) {
                    return [
                        'id' => $doctor->id,
                        'full_name' => $doctor->user->full_name,
                        'specialty_id' => $doctor->specialty_id,
                        'specialty' => $doctor->specialty->name,
                        'available' => $doctor->isAvailable()
                    ];
                });
        }

        // Получаем доступное время для врача (если передан doctor_id и date)
        $availableTimes = [];
        if ($request->has('doctor_id') && $request->has('date')) {
            $doctor = Doctor::find($request->doctor_id);
            if ($doctor) {
                $availableTimes = $doctor->getAvailableTimes($request->date);
            }
        }

        return Inertia::render('Admin/RegistrarDashboard', [
            'appointments' => $appointments,
            'specialties' => $specialties,
            'patient' => $patient ? [
                'id' => $patient->id,
                'full_name' => $patient->full_name,
                'birth_date' => $patient->birth_date->format('d.m.Y'),
                'insurance_number' => $patient->insurance_number
            ] : null,
            'doctors' => $doctors,
            'availableTimes' => $availableTimes,
            'filters' => $request->only(['snils', 'specialty_id', 'doctor_id', 'date'])
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
            'cancel_reason' => 'required_if:status,cancelled|nullable|string|max:500'
        ]);

        $appointment->update([
            'status' => $validated['status'],
            'cancel_reason' => $validated['cancel_reason'] ?? null
        ]);

        return redirect()->back()->with('success', 'Статус записи обновлен');
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        // Если email не указан, устанавливаем null
        $validated['email'] = !empty($validated['email']) ? $validated['email'] : null;

        $user = User::create($validated + [
            'password' => Hash::make(Str::random(12))
        ]);

        $patientRole = Role::where('name', 'patient')->first();
        if ($patientRole) {
            $user->roles()->attach($patientRole);
        }

        return redirect()->back()->with('Пациент успешно зарегистрирован');
    }

    public function storeAppointment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:doctors,id',
            'start_time' => 'required|date',
            'reason' => 'required|string|max:500'
        ]);

        $validated['registrar_id'] = Auth::user();
        $validated['end_time'] = Carbon::parse($validated['start_time'])
            ->addMinutes(Doctor::find($validated['doctor_id'])->appointment_duration ?? 30);
        $validated['status'] = Appointment::STATUS_CONFIRMED;

        Appointment::create($validated);

        return redirect()->back()->with('success', 'Запись на прием успешно создана!');
    }
}
