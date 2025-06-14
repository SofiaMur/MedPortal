<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => [
                    'required', 
                    'confirmed',
                    Password::defaults()
                        ->min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                ],
            ], [
                'current_password.required' => 'Текущий пароль обязателен',
                'current_password.current_password' => 'Неверный текущий пароль',
                
                'password.required' => 'Придумайте пароль',
                'password.confirmed' => 'Пароли не совпадают',
                'password.min' => 'Пароль слишком короткий (минимум 8 символов)',
                'password.mixed' => 'Пароль должен содержать заглавные и строчные буквы',
                'password.numbers' => 'Добавьте в пароль хотя бы одну цифру',
                'password.symbols' => 'Добавьте в пароль специальный символ (!@#$%^&*)',
                
                'password_confirmation.required' => 'Подтверждение пароля обязательно',
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            return back()->with('status', 'Пароль успешно изменен!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}