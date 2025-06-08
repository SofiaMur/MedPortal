<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // Создаем роли
        $roles = [
            ['name' => 'admin'],
            ['name' => 'registrar'],
            ['name' => 'doctor'],
        ];
        
        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }

        // 1. Администратор
        $admins = [
            [
                'login' => 'superadmin',
                'email' => 'admin@clinic.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Иванов',
                'first_name' => 'Александр',
                'middle_name' => 'Сергеевич',
                'gender' => 'male',
                'birth_date' => '1980-05-15',
                'phone' => '+79111234567',
                'is_profile_completed' => true,
            ]
        ];

        foreach ($admins as $admin) {
            $user = User::create($admin);
            $user->roles()->attach(Role::where('name', 'admin')->first());
        }

        // 2. Регистраторы (3 пользователей)
        $registrars = [
            [
                'login' => 'registrar1',
                'email' => 'registrar1@clinic.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Кузнецова',
                'first_name' => 'Анна',
                'middle_name' => 'Игоревна',
                'gender' => 'female',
                'birth_date' => '1990-07-12',
                'phone' => '+79122345678',
                'is_profile_completed' => true,
            ],
            [
                'login' => 'registrar2',
                'email' => 'registrar2@clinic.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Федоров',
                'first_name' => 'Михаил',
                'middle_name' => 'Петрович',
                'gender' => 'male',
                'birth_date' => '1989-11-25',
                'phone' => '+79123456789',
                'is_profile_completed' => true,
            ],
        ];

        foreach ($registrars as $registrar) {
            $user = User::create($registrar);
            $user->roles()->attach(Role::where('name', 'registrar')->first());
        }

        // 3. Пациенты (10 пользователей)
        $patients = [
            [
                'login' => 'patient1',
                'email' => 'patient1@example.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Волков',
                'first_name' => 'Александр',
                'middle_name' => 'Дмитриевич',
                'gender' => 'male',
                'birth_date' => '1992-07-14',
                'phone' => '+79172345678',
                'snils' => '678-901-234 56',
                'insurance_number' => '678-901-234-56789',
                'allergies' => 'Пенициллин',
                'chronic_diseases' => 'Гипертония',
                'is_profile_completed' => true,
            ],
            [
                'login' => 'patient2',
                'email' => 'patient2@example.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Ковалева',
                'first_name' => 'Мария',
                'middle_name' => 'Андреевна',
                'gender' => 'female',
                'birth_date' => '1988-11-03',
                'phone' => '+79183456789',
                'snils' => '789-012-345 67',
                'insurance_number' => '789-012-345-67890',
                'allergies' => 'Аспирин, мед',
                'chronic_diseases' => 'Сахарный диабет 2 типа',
                'is_profile_completed' => true,
            ],
            [
                'login' => 'patient3',
                'email' => 'patient3@example.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Соколов',
                'first_name' => 'Иван',
                'middle_name' => 'Петрович',
                'gender' => 'male',
                'birth_date' => '1976-05-28',
                'phone' => '+79194567890',
                'snils' => '890-123-456 78',
                'insurance_number' => '890-123-456-78901',
                'allergies' => 'Пыльца березы',
                'chronic_diseases' => 'Бронхиальная астма',
                'is_profile_completed' => true,
            ],
            [
                'login' => 'patient4',
                'email' => 'patient4@example.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Лебедева',
                'first_name' => 'Анна',
                'middle_name' => 'Владимировна',
                'gender' => 'female',
                'birth_date' => '1995-02-17',
                'phone' => '+79205678901',
                'snils' => '901-234-567 89',
                'insurance_number' => '901-234-567-89012',
                'allergies' => 'Кошки, пыль',
                'chronic_diseases' => 'Атопический дерматит',
                'is_profile_completed' => true,
            ],
            [
                'login' => 'patient5',
                'email' => 'patient5@example.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Новиков',
                'first_name' => 'Денис',
                'middle_name' => 'Сергеевич',
                'gender' => 'male',
                'birth_date' => '1983-09-10',
                'phone' => '+79216789012',
                'snils' => '012-345-678 90',
                'insurance_number' => '012-345-678-90123',
                'allergies' => 'Нет',
                'chronic_diseases' => 'Язвенная болезнь желудка',
                'is_profile_completed' => true,
            ],
            [
                'login' => 'patient6',
                'email' => 'patient6@example.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Медведева',
                'first_name' => 'Елена',
                'middle_name' => 'Александровна',
                'gender' => 'female',
                'birth_date' => '1990-12-24',
                'phone' => '+79227890123',
                'snils' => '123-456-789 01',
                'insurance_number' => '123-456-789-01234',
                'allergies' => 'Антибиотики',
                'chronic_diseases' => 'Хронический гастрит',
                'is_profile_completed' => true,
            ],
            [
                'login' => 'patient7',
                'email' => 'patient7@example.ru',
                'password' => Hash::make('password'),
                'last_name' => 'Павлов',
                'first_name' => 'Артем',
                'middle_name' => 'Игоревич',
                'gender' => 'male',
                'birth_date' => '1987-04-05',
                'phone' => '+79238901234',
                'snils' => '234-567-890 12',
                'insurance_number' => '234-567-890-12345',
                'allergies' => 'Шоколад, орехи',
                'chronic_diseases' => 'Псориаз',
                'is_profile_completed' => true,
            ]
        ];

        foreach ($patients as $patient) {
            $user = User::create($patient);
            $user->roles()->attach(Role::where('name', 'patient')->first());
        }
    }
}