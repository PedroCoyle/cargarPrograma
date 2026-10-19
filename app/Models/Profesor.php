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
    const ROL_EMTP = 4;
    const ROL_DIRECTIVO = 5;
    const ROL_JEFE = 6;

    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'nombre',
        'email',
        'creado_por_admin',
        'creado_por_perfil_id',
    ];

    protected $casts = [
        'creado_por_admin' => 'boolean',
    ];

    // Roles que un jefe de área puede asignar
    public static function rolesQueAsignaElJefe(): array
    {
        return [self::ROL_PROFESOR, self::ROL_PRECEPTOR, self::ROL_EMTP];
    }

    public function user()
    {
        // Si el perfil todavía no tiene cuenta, devuelve un User vacío con el email y nombre del perfil,
        // así las vistas que hacen $profesor->user->email no fallan.
        return $this->belongsTo(User::class)->withDefault(function ($user, $profesor) {
            $user->email = $profesor->email;
            $user->nombre = $profesor->nombre;
        });
    }

    public function tieneCuenta(): bool
    {
        return ! is_null($this->user_id);
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'profesor_materias');
    }

    public function carrerasEmtp()
    {
        return $this->belongsToMany(Carrera::class, 'emtp_carreras');
    }

    public function carrerasJefe()
    {
        return $this->belongsToMany(Carrera::class, 'jefe_carreras');
    }

    public function cursosPreceptor()
    {
        return $this->hasMany(PreceptorCurso::class);
    }

    public function programas()
    {
        return $this->hasMany(Programa::class);
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

    // Perfil sin roles, asignaciones ni programas (el que se crea solo al registrarse)
    public function estaVacio(): bool
    {
        return ! $this->rolesPivot()->exists()
            && ! $this->materias()->exists()
            && ! $this->carrerasEmtp()->exists()
            && ! $this->cursosPreceptor()->exists()
            && ! $this->programas()->exists();
    }

    // Perfiles del área de un jefe: los que creó o los que tienen algo asignado en sus carreras
    public function scopeDelAreaDe($query, Profesor $jefe)
    {
        $carreraIds = $jefe->carrerasJefe()->pluck('carreras.id')->all();

        return $query->where(function ($q) use ($jefe, $carreraIds) {
            $q->where('profesores.creado_por_perfil_id', $jefe->id)
              ->orWhereHas('materias', fn ($m) => $m->whereIn('materias.carrera_id', $carreraIds))
              ->orWhereHas('carrerasEmtp', fn ($c) => $c->whereIn('carreras.id', $carreraIds))
              ->orWhereHas('cursosPreceptor', fn ($c) => $c->whereIn('preceptor_cursos.carrera_id', $carreraIds));
        });
    }

    // Lo que el jefe puede manejar: de su área y sin roles Admin, Directivo ni Jefe
    public function scopeGestionablePor($query, Profesor $jefe)
    {
        return $query->delAreaDe($jefe)
            ->whereDoesntHave('rolesPivot', function ($q) {
                $q->whereIn('rol_id', [self::ROL_ADMIN, self::ROL_DIRECTIVO, self::ROL_JEFE]);
            });
    }

    public function esGestionablePor(Profesor $jefe): bool
    {
        return static::query()->whereKey($this->getKey())->gestionablePor($jefe)->exists();
    }
}