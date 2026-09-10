<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AniosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('anios')->insert([
            ['id' => 1, 'nombre' => '1° Año'],
            ['id' => 2, 'nombre' => '2° Año'],
            ['id' => 3, 'nombre' => '3° Año'],
            ['id' => 4, 'nombre' => '4° Año'],
            ['id' => 5, 'nombre' => '5° Año'],
            ['id' => 6, 'nombre' => '6° Año'],
            ['id' => 7, 'nombre' => '7° Año'],
        ]);
    }
}
