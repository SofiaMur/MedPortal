<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Education;
use App\Models\Achievement;
use App\Models\Experience;

class DoctorExtrasSeeder extends Seeder
{
    public function run(): void
    {
        $fallbackEducation = [
            'Медицинский университет, 2005',
            'Ординатура, 2007'
        ];

        $fallbackAchievements = [
            'Участие в международной конференции',
            'Сертификат повышения квалификации'
        ];

        Doctor::with('user')->get()->each(function ($doctor, $index) use ($fallbackEducation, $fallbackAchievements) {
            // Опыт: от 5 до 25 лет
            $years = rand(5, 25);

            Experience::create([
                'doctor_id' => $doctor->id,
                'years' => $years,
                'description' => "Опыт работы в медицинской практике — $years лет"
            ]);

            // Образование (2 записи)
            foreach ($fallbackEducation as $edu) {
                Education::create([
                    'doctor_id' => $doctor->id,
                    'institution' => $edu
                ]);
            }

            // Достижения (2 записи)
            foreach ($fallbackAchievements as $achievement) {
                Achievement::create([
                    'doctor_id' => $doctor->id,
                    'title' => $achievement
                ]);
            }
        });
    }
}
