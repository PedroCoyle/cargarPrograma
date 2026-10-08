<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesor_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesor_id')->constrained('profesores')->onDelete('cascade');
            $table->unsignedTinyInteger('rol_id'); // 1=profesor, 2=preceptor, 3=admin
            $table->timestamps();

            $table->unique(['profesor_id', 'rol_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profesor_rol');
    }
};