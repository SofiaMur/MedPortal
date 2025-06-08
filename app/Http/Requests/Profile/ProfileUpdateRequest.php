<?php

namespace App\Http\Requests\Profile;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'last_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{Cyrillic}Ёё]+$/u'
            ],
            'first_name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{Cyrillic}Ёё]+$/u'
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[\p{Cyrillic}Ёё]+$/u'
            ],
            'gender' => [
                'required',
                'in:male,female'
            ],
            'birth_date' => [
                'required',
                'date',
                'before:-18 years',
                'after:-100 years'
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^7\d{10}$/'
            ],
            'insurance_number' => [
                'required',
                'string',
                'regex:/^\d{14}$/',
                'unique:users'
            ],
            'snils' => [
                'required',
                'string',
                'regex:/^\d{11}$/',
                function ($attribute, $value, $fail) {
                    if (!$this->validateSnils($value)) {
                        $fail('Некорректный формат');
                    }
                }
            ],
            'allergies' => [
                'nullable',
                'string',
                'regex:/^[\p{Cyrillic}Ёё]+$/u',
                'max:1000'
            ],
            'chronic_diseases' => [
                'nullable',
                'string',
                'regex:/^[\p{Cyrillic}Ёё]+$/u',
                'max:1000'
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                Rule::unique(User::class)->ignore($this->user()->id),
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
        ];
    }

    protected function validateSnils($number)
    {
        if (strlen($number) != 11) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$number[$i] * (9 - $i);
        }

        $checkDigit = 0;
        if ($sum < 100) {
            $checkDigit = $sum;
        } elseif ($sum > 101) {
            $checkDigit = $sum % 101;
            if ($checkDigit == 100) {
                $checkDigit = 0;
            }
        }

        return $checkDigit == substr($number, -2);
    }

    public function messages()
    {
        return [
            'last_name.required' => 'Обязательно для заполнения',
            'last_name.regex' => 'Фамилия должна содержать только кириллицу',
            'first_name.required' => 'Обязательно для заполнения',
            'first_name.regex' => 'Имя должно содержать только кириллицу',
            'middle_name.regex' => 'Отчество должно содержать только кириллицу',
            'gender.required' => 'Укажите пол',
            'gender.in' => 'Выберите корректный пол (мужской/женский)',
            'birth_date.required' => 'Укажите дату рождения',
            'birth_date.date' => 'Укажите корректную дату',
            'birth_date.before' => 'Пациент должен быть старше 18 лет',
            'birth_date.after' => 'Укажите реальную дату рождения',
            'phone.required' => 'Обязательно для заполнения',
            'phone.regex' => 'Некорректный формат',
            'insurance_number.required' => 'Обязательно для заполнения',
            'insurance_number.regex' => 'Некорректный формат',
            'snils.required' => 'Обязательно для заполнения',
            'snils.regex' => 'Некорректный формат',
            'email.required' => 'Обязательно для заполнения',
            'email.email' => 'Укажите корректный email',
            'email.unique' => 'Этот email уже используется',
            'allergies.regex' => 'Допустима только кириллица',
            'allergies.max' => 'Максимальная длина - 1000 символов',
            'chronic_diseases.regex' => 'Допустима только кириллица',
            'chronic_diseases.max' => 'Максимальная длина - 1000 символов',
        ];
    }

    public function attributes()
    {
        return [
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'gender' => 'Пол',
            'birth_date' => 'Дата рождения',
            'phone' => 'Телефон',
            'insurance_number' => 'Полис ОМС',
            'snils' => 'СНИЛС',
            'allergies' => 'Аллергии',
            'chronic_diseases' => 'Хронические заболевания',
            'email' => 'Email',
        ];
    }
}
