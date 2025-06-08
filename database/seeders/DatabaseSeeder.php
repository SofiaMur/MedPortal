<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SpecialtiesSeeder::class,
            DoctorSeeder::class,
            DoctorExtrasSeeder::class,
            SchedulesSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
