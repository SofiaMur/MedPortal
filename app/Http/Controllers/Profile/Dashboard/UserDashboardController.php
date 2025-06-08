<?php

namespace App\Http\Controllers\Profile\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $appointments = $user->patientAppointments()
            ->with([
                'doctor.user',
                'doctor.specialty',
                'medicalReport'
            ])
            ->where(function ($query) {
                $query->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                    ->orWhere(function ($q) {
                        $q->where('status', Appointment::STATUS_CANCELLED)
                            ->where('updated_at', '>', Carbon::now()->subDays(14));
                    });
            })
            ->where('start_time', '>', now())
            ->orderByRaw("CASE 
            WHEN status = 'cancelled' THEN 0 
            WHEN status = 'confirmed' THEN 1 
            ELSE 2 
        END")
            ->orderBy('start_time')
            ->get();

        $completedAppointments = $user->patientAppointments()
            ->with([
                'doctor.user',
                'doctor.specialty',
                'medicalReport'
            ])
            ->where('status', Appointment::STATUS_COMPLETED)
            ->orderByDesc('start_time')
            ->get();

        return inertia('Dashboard', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'login' => $user->login,
                    'gender' => $user->gender,
                    'birth_date' => $user->birth_date ? $user->birth_date->format('d.m.Y') : null,
                    'is_profile_completed' => $user->is_profile_completed,
                    'is_doctor' => $user->is_doctor,
                ]
            ],
            'appointments' => $appointments,
            'completedAppointments' => $completedAppointments
        ]);
    }
    public function updateReason(Request $request, Appointment $appointment)
    {
        if (Auth::id() !== $appointment->patient_id) {
            abort(403, 'Недостаточно прав для обновления записи');
        }

        $request->validate(['reason' => 'required|string|max:1000']);

        $appointment->update(['reason' => $request->reason]);

        return back();
    }

    public function cancel(Appointment $appointment)
    {
        if (Auth::id() !== $appointment->patient_id) {
            return redirect()->back()->withErrors([
                'message' => 'У вас нет прав для отмены этой записи'
            ]);
        }

        if ($appointment->start_time < now()->addDay()) {
            return redirect()->back()->withErrors([
                'message' => 'Отмена возможна не позднее чем за 24 часа до приема'
            ]);
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancel_reason' => 'Отменено пациентом'
        ]);

        return redirect()->route('dashboard')->with('success', 'Запись успешно отменена');
    }
}
