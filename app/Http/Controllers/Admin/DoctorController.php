<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Admin/Doctors/Index', [
            'doctors' => Doctor::with('user')
                ->when($request->search, function ($query, $search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                        ->orWhere('specialization', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(StoreDoctorRequest $request)
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password'))
        ]);

        $user->roles()->attach(Role::where('name', 'doctor')->first());

        $user->doctor()->create([
            'specialization' => $request->validated('specialization')
        ]);

        return redirect()->route('admin.doctors.index')->with('success', 'Врач успешно зарегистрирован!');
    }

    public function update(StoreDoctorRequest $request, Doctor $doctor)
    {
        // Проверяем уникальность email (исключая текущего пользователя)
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email,' . $doctor->user_id],
        ]);

        DB::transaction(function () use ($request, $doctor) {
            // Обновляем данные пользователя
            $userData = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
            ];

            // Добавляем пароль, если он был изменен
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->validated('password'));
            }

            $doctor->user->update($userData);

            $doctor->touch();

            // Обновляем данные врача
            $doctor->update([
                'specialization' => $request->validated('specialization')
            ]);
        });

        return back()->with('success', 'Данные врача успешно обновлены!');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->user()->delete();

        return back()->with('success', 'Врач успешно удален!');
    }
}
