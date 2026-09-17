<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $fillable = ['profesor_id', 'materia_id', 'archivo', 'fecha_subida', 'ultimo'];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'ultimo' => 'boolean',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }
}