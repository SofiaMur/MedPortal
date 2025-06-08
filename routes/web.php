<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Profile\ProfileController;

// use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UController;
use App\Http\Controllers\Admin\ScheduleDashboardController;
use App\Http\Controllers\Admin\RegistrarDashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Profile\Dashboard\UserDashboardController;
use App\Http\Controllers\Profile\Dashboard\DoctorDashboardController;
use App\Http\Controllers\DoctorController;

Route::get('/translations/{locale}', function ($locale) {
    $path = resource_path("lang/{$locale}/{$locale}.json");

    if (!file_exists($path)) {
        return response()->json(['error' => 'Язык не найден'], 404);
    }

    return response()->json(json_decode(file_get_contents($path), true));
});

// Главная страница
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Публичные страницы
Route::name('public.')->group(function () {
    Route::get('/services', fn() => Inertia::render('Services'))->name('services');
    Route::get('/doctors', [DoctorController::class, 'index'])->name('doctors');
    Route::post('/doctors/appointments', [AppointmentController::class, 'store'])
        ->middleware(['auth', 'verified'])
        ->name('doctors.appointments.store');
    Route::get('/doctors/{doctor}/occupied-times', [AppointmentController::class, 'getOccupiedTimes'])
        ->name('doctors.occupied-times');
    Route::get('/promotions', fn() => Inertia::render('Promotions'))->name('promotions');
    Route::get('/contacts', fn() => Inertia::render('Contacts'))->name('contacts');
});

// Личный кабинет 
Route::middleware(['auth', 'verified', \App\Http\Middleware\RedirectIfRegistrar::class])->group(function () {
    // Профиль пользователя с приемами
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::patch('/dashboard/appointments/{appointment}/reason', [UserDashboardController::class, 'updateReason'])
        ->name('dashboard.update-reason');

    // Настройки профиля
    Route::prefix('profile')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'edit')->name('profile.edit');
        Route::get('/security', 'security')->name('profile.security');
        Route::patch('/security', 'update')->name('profile.update');
        Route::delete('/security', 'destroy')->name('profile.destroy');
    });

    // Управление записями
    Route::prefix('appointments')->group(function () {
        Route::delete('/{appointment}/cancel', [UserDashboardController::class, 'cancel'])
            ->name('appointments.cancel');
    });
});

// Панель врача
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureIsDoctor::class])->group(function () {
    Route::prefix('doctor/dashboard')->group(function () {
        Route::get('/', [DoctorDashboardController::class, 'index'])
            ->name('doctor.dashboard');
        Route::post('/appointments/{appointment}/complete', [DoctorDashboardController::class, 'complete'])
            ->name('appointments.complete');
        Route::post('appointments/{appointment}/cancel', [DoctorDashboardController::class, 'cancel'])
            ->name('appointments.cancel');
    });
});

// Регистратура
Route::middleware(['auth', 'verified', \App\Http\Middleware\СheckRole::class])->group(function () {
    Route::prefix('registrar/dashboard')->group(function () {
        Route::get('/', [RegistrarDashboardController::class, 'index'])
            ->name('registrar.dashboard');
        Route::post('/patients', [RegistrarDashboardController::class, 'store'])
            ->name('registrar.patients');
        Route::put('/appointments/{appointment}/status', [RegistrarDashboardController::class, 'updateStatus'])
            ->name('registrar.appointments.update-status');
        Route::post('/appointments', [RegistrarDashboardController::class, 'storeAppointment'])
            ->name('registrar.appointments');
    });
});

// Администрация
// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::prefix('admin')->group(function () {
//         Route::get('/u', [UController::class, 'index'])
//             ->name('u.dashboard');
//     });
// });

require __DIR__ . '/auth.php';
