<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecialtiesSeeder extends Seeder
{
    public function run(): void
    {
          $specialties = [
            ['name' => 'Терапевт', 'description' => 'Врач общей практики'],
            ['name' => 'Хирург', 'description' => 'Оперативное лечение заболеваний'],
            ['name' => 'Кардиолог', 'description' => 'Лечение сердечно-сосудистых заболеваний'],
            ['name' => 'Невролог', 'description' => 'Лечение заболеваний нервной системы'],
            ['name' => 'Педиатр', 'description' => 'Детский врач'],
            ['name' => 'Офтальмолог', 'description' => 'Лечение заболеваний глаз'],
            ['name' => 'Отоларинголог', 'description' => 'ЛОР-врач'],
            ['name' => 'Гинеколог', 'description' => 'Женское здоровье'],
        ];

        foreach ($specialties as $specialty) {
            Specialty::create($specialty);
        }
    }
}
