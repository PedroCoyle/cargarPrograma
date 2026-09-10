<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarrerasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('carreras')->insert([
            ['id' => 1, 'nombre' => 'Técnico en Informática Personal y Profesional'],
            ['id' => 2, 'nombre' => 'Técnico Electromecánico'],
            ['id' => 5, 'nombre' => 'Ciclo Básico'],
            ['id' => 7, 'nombre' => 'Técnico Químico'],
            ['id' => 10, 'nombre' => 'Técnico Maestro Mayor de Obras'],
        ]);
    }
}