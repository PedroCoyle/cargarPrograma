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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'profesor_materias');
    }

    public function rolesPivot()
    {
        return $this->hasMany(ProfesorRol::class);
    }

    public function getRolesIdsAttribute(): array
    {
        return $this->rolesPivot()->pluck('rol_id')->toArray();
    }

    public function tieneRol(int $rolId): bool
    {
        return in_array($rolId, $this->roles_ids);
    }

    public function asignarRoles(array $rolesIds): void
    {
        $this->rolesPivot()->delete();

        foreach ($rolesIds as $rolId) {
            $this->rolesPivot()->create(['rol_id' => $rolId]);
        }
    }
}