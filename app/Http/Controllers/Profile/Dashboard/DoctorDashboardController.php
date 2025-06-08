<?php

namespace App\Http\Controllers\Profile\Dashboard;

use App\Http\Controllers\Profile\DashboardController;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DoctorDashboardController extends DashboardController
{
    public function index()
    {
        $user = Auth::user();

        $appointments = Appointment::with(['patient', 'medicalReport', 'doctor.user'])
            ->where('doctor_id', $user->doctor->id)
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'start_time' => $appointment->start_time->toISOString(),
                    'end_time' => $appointment->end_time->toISOString(),
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'patient' => [
                        'id' => $appointment->patient->id,
                        'full_name' => $appointment->patient->full_name,
                        'gender' => $appointment->patient->gender,
                        'birth_date' => $appointment->patient->birth_date?->format('Y-m-d'),
                        'allergies' => $appointment->patient->allergies,
                        'chronic_diseases' => $appointment->patient->chronic_diseases,
                        'phone' => $appointment->patient->phone
                    ],
                    'medical_report' => $appointment->medicalReport ? [
                        'diagnosis' => $appointment->medicalReport->diagnosis,
                        'recommendations' => $appointment->medicalReport->recommendations,
                        'prescription' => $appointment->medicalReport->prescription
                    ] : null
                ];
            });

        $completedAppointments = Appointment::with(['patient', 'medicalReport'])
            ->where('doctor_id', $user->doctor->id)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->where('end_time', '>', now()->subHours(72))
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'start_time' => $appointment->start_time->toISOString(),
                    'end_time' => $appointment->end_time->toISOString(),
                    'status' => $appointment->status,
                    'reason' => $appointment->reason,
                    'patient' => [
                        'id' => $appointment->patient->id,
                        'full_name' => $appointment->patient->full_name
                    ],
                    'medical_report' => $appointment->medicalReport ? [
                        'diagnosis' => $appointment->medicalReport->diagnosis,
                        'recommendations' => $appointment->medicalReport->recommendations, // Изменено
                        'prescription' => $appointment->medicalReport->prescription // Добавлено
                    ] : null
                ];
            });

        $completedAppointmentsHistory = $user->doctor->appointments()
            ->with([
                'patient',
                'medicalReport',
            ])
            ->where('status', Appointment::STATUS_COMPLETED)
            ->orderByDesc('start_time')
            ->get();

        return Inertia::render('DoctorDashboard', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'room' => $user->doctor->room,
                    'is_doctor' => $user->is_doctor,
                    'specialty' => $user->doctor->specialty->name,
                ]
            ],
            'appointments' => $appointments,
            'completedAppointments' => $completedAppointments,
            'completedAppointmentsHistory' => $completedAppointmentsHistory,
        ]);
    }

    public function complete(Request $request, $appointmentId)
    {
        $request->validate([
            'diagnosis' => 'required|string|max:255',
            'recommendations' => 'required|string',
            'prescription' => 'nullable|string'
        ]);

        $appointment = Appointment::findOrFail($appointmentId);
        $user = Auth::user();

        if ($appointment->doctor_id !== $user->doctor->id) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $appointment->update(['status' => Appointment::STATUS_COMPLETED]);

        $appointment->medicalReport()->updateOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'diagnosis' => $request->diagnosis,
                'recommendations' => $request->recommendations,
                'prescription' => $request->prescription
            ]
        );

        return back()->with('success', 'Прием успешно завершен');
    }

    public function cancel(Request $request, $appointmentId)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500'
        ]);

        $appointment = Appointment::findOrFail($appointmentId);
        $user = Auth::user();

        if ($appointment->doctor_id !== $user->doctor->id) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $appointment->update([
            'status' => Appointment::STATUS_CANCELLED,
            'cancel_reason' => $request->cancel_reason
        ]);

        return back()->with('success', 'Прием успешно отменен');
    }
}
