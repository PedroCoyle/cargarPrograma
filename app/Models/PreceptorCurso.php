<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreceptorCurso extends Model
{
    protected $table = 'preceptor_cursos';

    protected $fillable = ['profesor_id', 'carrera_id', 'anio_id'];

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function anio()
    {
        return $this->belongsTo(Anio::class);
    }
}