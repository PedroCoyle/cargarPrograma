<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profesores', function (Blueprint $table) {
            $table->boolean('creado_por_admin')->default(false)->after('email');
        });

        // Los perfiles que ya tienen roles los armó el admin: se marcan para que no desaparezcan de Asignaciones
        DB::statement('UPDATE profesores SET creado_por_admin = 1 WHERE EXISTS (SELECT 1 FROM profesor_rol WHERE profesor_rol.profesor_id = profesores.id)');
    }

    public function down(): void
    {
        Schema::table('profesores', function (Blueprint $table) {
            $table->dropColumn('creado_por_admin');
        });
    }
};
