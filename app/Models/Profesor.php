<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;

const ROL_PROFESOR = 1;
const ROL_PRECEPTOR = 2;
const ROL_ADMIN = 3;

    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'nombre',
        'rol_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materias()
{
    return $this->belongsToMany(Materia::class, 'profesor_materias');
}
}