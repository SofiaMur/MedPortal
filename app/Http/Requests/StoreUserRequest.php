<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['sometimes', 'string', 'in:admin,doctor,user'],
        ];

        // Правила для пароля (только при создании или при явном изменении)
        if ($this->isMethod('post')) { 
            $rules['password'] = [
                'required', 
                'confirmed', 
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ];
            $rules['password_confirmation'] = ['required'];
        } elseif ($this->filled('password')) {
            $rules['password'] = [
                'confirmed', 
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Поле имени обязательно для заполнения',
            'email.required' => 'Поле email обязательно для заполнения',
            'email.email' => 'Введите корректный email адрес',
            'email.unique' => 'Пользователь с таким email уже существует',
            'role.in' => 'Недопустимая роль пользователя',
            
            'password.required' => 'Придумайте пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль слишком короткий (минимум 8 символов)',
            'password.mixed' => 'Пароль должен содержать заглавные и строчные буквы',
            'password.numbers' => 'Добавьте в пароль хотя бы одну цифру',
            'password.symbols' => 'Добавьте в пароль специальный символ (!@#$%^&*)',
            
            'password_confirmation.required' => 'Подтверждение пароля обязательно',
        ];
    }
}