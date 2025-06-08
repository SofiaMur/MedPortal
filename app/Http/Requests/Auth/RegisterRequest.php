<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        return [
            'login' => 'required|string|max:50|unique:users|regex:/^[a-zA-Z0-9_\-]+$/u', // Только латиница, цифры, _ и -
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'unique:users',
                function ($attribute, $value, $fail) {
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail('Некорректный формат');
                    }
                    $domain = substr(strrchr($value, "@"), 1);
                    if (!checkdnsrr($domain, 'MX')) {
                        $fail('Домен почты не существует или не принимает письма');
                    }
                }
            ],
             'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->min(8)
            ],
        ];
    }

    public function messages()
    {
        return [
            // Логин
            'login.required' => 'Пожалуйста, придумайте логин',
            'login.max' => 'Слишком длинный логин',
            'login.regex' => 'Только латинские буквы, цифры, дефисы и подчёркивания',
            'login.unique' => 'Этот логин уже занят, попробуйте другой',
            
            // Email
            'email.required' => 'Укажите Вашу почту',
            'email.email' => 'Введите корректный email адрес',
            'email.unique' => 'Этот email уже зарегистрирован',
            
            // Пароль
            'password.required' => 'Придумайте пароль',
            'password.confirmed' => 'Пароли не совпадают',
            'password.min' => 'Пароль слишком короткий',
            'password.mixed' => 'Пароль должен содержать заглавные и строчные буквы',
            'password.numbers' => 'Добавьте в пароль хотя бы одну цифру',
            'password.symbols' => 'Добавьте в пароль специальный символ (!@#$%^&*)',
        ];
    }

    public function attributes()
    {
        return [
            'login' => 'Логин',
            'email' => 'Email',
            'password' => 'Пароль',
        ];
    }
}