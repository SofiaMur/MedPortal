<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Specialty;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        return Inertia::render('Doctors', [
            'doctors' => Doctor::with([
                'user',
                'specialty',
                'schedules',
                'experience',
                'educations',
                'achievements'
            ])->get(),
            'specialties' => Specialty::all()
        ]);
    }

    public function getSchedule(Doctor $doctor)
    {
        return response()->json($doctor->schedules);
    }
}
