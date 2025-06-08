<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()
                ->min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ], [
            'token.required' => 'Токен сброса пароля обязателен',
            
            'email.required' => 'Укажите Вашу почту',
            'email.email' => 'Укажите корректный адрес почты',
            
            'password.required' => 'Придумайте пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль слишком короткий (минимум 8 символов)',
            'password.mixed' => 'Пароль должен содержать заглавные и строчные буквы',
            'password.numbers' => 'Добавьте в пароль хотя бы одну цифру',
            'password.symbols' => 'Добавьте в пароль специальный символ (!@#$%^&*)',
            
            'password_confirmation.required' => 'Подтверждение пароля обязательно',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}