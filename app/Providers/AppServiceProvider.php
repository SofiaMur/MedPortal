<?php

namespace App\Providers;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Policies\DoctorPolicy;
use App\Policies\AppointmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    // Gate::policy(Doctor::class, DoctorPolicy::class);
    // Gate::policy(Appointment::class, AppointmentPolicy::class);

    Inertia::share([
        'auth' => function () {
            $user = Auth::user();
            return [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'is_doctor' => $user->is_doctor 
                ] : null
            ];
        }
    ]);

    Vite::prefetch(concurrency: 3);
}
}