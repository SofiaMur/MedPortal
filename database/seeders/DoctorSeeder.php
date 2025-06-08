<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    public function run()
    {
        $specialties = Specialty::all();
        $doctorRole = Role::where('name', 'doctor')->first();

        $doctors = [
            [
                'login' => 'doctor1',
                'last_name' => 'Иванова',
                'first_name' => 'Елена',
                'middle_name' => 'Сергеевна',
                'email' => 'therapist1@medportal.ru',
                'room' => '101',
                'specialty' => 'Терапевт',
                'duration' => 30,
                'gender' => 'female',
                'birth_date' => '1980-05-15'
            ],
            [
                'login' => 'doctor2',
                'last_name' => 'Петров',
                'first_name' => 'Сергей',
                'middle_name' => 'Владимирович',
                'email' => 'surgeon1@medportal.ru',
                'room' => '205',
                'specialty' => 'Хирург',
                'duration' => 45,
                'gender' => 'male',
                'birth_date' => '1975-08-22'
            ],
            [
                'login' => 'doctor3',
                'last_name' => 'Сидорова',
                'first_name' => 'Анна',
                'middle_name' => 'Михайловна',
                'email' => 'pediatrician1@medportal.ru',
                'room' => '156',
                'specialty' => 'Педиатр',
                'duration' => 30,
                'gender' => 'female',
                'birth_date' => '1982-11-30'
            ],
            [
                'login' => 'doctor4',
                'last_name' => 'Кузнецов',
                'first_name' => 'Алексей',
                'middle_name' => 'Петрович',
                'email' => 'surgeon2@medportal.ru',
                'room' => '301',
                'specialty' => 'Хирург',
                'duration' => 60,
                'gender' => 'male',
                'birth_date' => '1973-07-12'
            ],
            [
                'login' => 'doctor5',
                'last_name' => 'Смирнова',
                'first_name' => 'Ольга',
                'middle_name' => 'Викторовна',
                'email' => 'ophthalmologist1@medportal.ru',
                'room' => '210',
                'specialty' => 'Офтальмолог',
                'duration' => 30,
                'gender' => 'female',
                'birth_date' => '1985-02-28'
            ],
            [
                'login' => 'doctor6',
                'last_name' => 'Васильев',
                'first_name' => 'Михаил',
                'middle_name' => 'Иванович',
                'email' => 'therapist2@medportal.ru',
                'room' => '102',
                'specialty' => 'Терапевт',
                'duration' => 30,
                'gender' => 'male',
                'birth_date' => '1970-09-05'
            ],
            [
                'login' => 'doctor7',
                'last_name' => 'Николаева',
                'first_name' => 'Татьяна',
                'middle_name' => 'Олеговна',
                'email' => 'cardiologist1@medportal.ru',
                'room' => '103',
                'specialty' => 'Кардиолог',
                'duration' => 45,
                'gender' => 'female',
                'birth_date' => '1977-12-14'
            ],
            [
                'login' => 'doctor8',
                'last_name' => 'Федоров',
                'first_name' => 'Дмитрий',
                'middle_name' => 'Сергеевич',
                'email' => 'neurologist1@medportal.ru',
                'room' => '206',
                'specialty' => 'Невролог',
                'duration' => 30,
                'gender' => 'male',
                'birth_date' => '1981-06-20'
            ],
            [
                'login' => 'doctor9',
                'last_name' => 'Морозова',
                'first_name' => 'Ирина',
                'middle_name' => 'Александровна',
                'email' => 'pediatrician2@medportal.ru',
                'room' => '157',
                'specialty' => 'Педиатр',
                'duration' => 30,
                'gender' => 'female',
                'birth_date' => '1983-03-25'
            ],
            [
                'login' => 'doctor10',
                'last_name' => 'Григорьев',
                'first_name' => 'Андрей',
                'middle_name' => 'Викторович',
                'email' => 'cardiologist2@medportal.ru',
                'room' => '302',
                'specialty' => 'Кардиолог',
                'duration' => 45,
                'gender' => 'male',
                'birth_date' => '1976-10-08'
            ],
            [
                'login' => 'doctor11',
                'last_name' => 'Белова',
                'first_name' => 'Наталья',
                'middle_name' => 'Дмитриевна',
                'email' => 'ophthalmologist2@medportal.ru',
                'room' => '211',
                'specialty' => 'Офтальмолог',
                'duration' => 30,
                'gender' => 'female',
                'birth_date' => '1979-01-17'
            ],
            [
                'login' => 'doctor12',
                'last_name' => 'Козлов',
                'first_name' => 'Артем',
                'middle_name' => 'Игоревич',
                'email' => 'neurologist2@medportal.ru',
                'room' => '104',
                'specialty' => 'Невролог',
                'duration' => 45,
                'gender' => 'male',
                'birth_date' => '1978-04-18'
            ]
        ];

        foreach ($doctors as $doctorData) {
            $user = User::create([
                'login' => $doctorData['login'],
                'email' => $doctorData['email'],
                'password' => Hash::make('password'),
                'last_name' => $doctorData['last_name'],
                'first_name' => $doctorData['first_name'],
                'middle_name' => $doctorData['middle_name'],
                'gender' => $doctorData['gender'],
                'birth_date' => $doctorData['birth_date'],
                'is_profile_completed' => false,
            ]);

            $user->roles()->attach($doctorRole);

            $specialty = $specialties->firstWhere('name', $doctorData['specialty']);

            Doctor::create([
                'user_id' => $user->id,
                'specialty_id' => $specialty->id,
                'room' => $doctorData['room'],
                'appointment_duration' => $doctorData['duration'],
            ]);
        }
    }
}