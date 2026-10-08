<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preceptor_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('profesores')->onDelete('cascade');
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
            $table->foreignId('anio_id')->constrained('anios')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['profesor_id', 'carrera_id', 'anio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preceptor_cursos');
    }
};