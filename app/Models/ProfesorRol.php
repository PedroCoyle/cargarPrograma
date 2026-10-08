<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfesorRol extends Model
{
    protected $table = 'profesor_rol';

    protected $fillable = ['profesor_id', 'rol_id'];
}