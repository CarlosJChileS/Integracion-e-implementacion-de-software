<?php

namespace Database\Seeders;

use App\Models\Cohort;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'docente.demo@ilia.test'],
            ['name' => 'Docente de práctica', 'password' => fake()->password()],
        );

        foreach ([
            'prompting-para-docentes' => 'Prompting para docentes',
            'notebooklm-en-el-aula' => 'NotebookLM en el aula',
            'evaluacion-asistida-por-ia' => 'Evaluación asistida por IA',
        ] as $slug => $name) {
            Course::firstOrCreate(['slug' => $slug], ['name' => $name]);
        }

        Cohort::firstOrCreate(
            ['code' => 'ILIA-DEMO'],
            ['name' => 'Cohorte de demostración'],
        );
    }
}
