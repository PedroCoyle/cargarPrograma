<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $fillable = ['carrera_id', 'anio_id', 'nombre'];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function anio()
    {
        return $this->belongsTo(Anio::class);
    }

    public function profesores()
    {
        return $this->belongsToMany(Profesor::class, 'profesor_materias');
    }

    public function programas()
    {
        return $this->hasMany(Programa::class);
    }
}