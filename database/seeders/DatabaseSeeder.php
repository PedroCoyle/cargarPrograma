<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AniosSeeder::class,
            CarrerasSeeder::class,
            MateriasSeeder::class,
        ]);
    }
}