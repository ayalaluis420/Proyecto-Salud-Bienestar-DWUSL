<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROL_ADMIN = 'admin';
    public const ROL_MODERADOR = 'moderador';
    public const ROL_USUARIO = 'usuario';

    public const ROLES = [
        self::ROL_ADMIN     => 'Administrador',
        self::ROL_MODERADOR => 'Moderador',
        self::ROL_USUARIO   => 'Usuario',
    ];

    /**
     * Campos asignables en masa. "rol" y "activo" NO están aquí a propósito:
     * solo se asignan explícitamente desde código de administración, así nadie
     * puede escalar privilegios enviando "rol=admin" en un formulario o en la API.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',   // las contraseñas siempre se guardan con hash (bcrypt)
            'activo'            => 'boolean',
        ];
    }

    // ---------------------------------------------------------------- Relaciones

    public function perfil(): HasOne
    {
        return $this->hasOne(Perfil::class);
    }

    // --------------------------------------------------------------------- Roles

    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esModerador(): bool
    {
        return $this->rol === self::ROL_MODERADOR;
    }

    /** Personal del panel: administrador o moderador. */
    public function esPersonal(): bool
    {
        return $this->esAdmin() || $this->esModerador();
    }

    public function getNombreRolAttribute(): string
    {
        return self::ROLES[$this->rol] ?? ucfirst((string) $this->rol);
    }

    // ------------------------------------------------------------------- Scopes

    /**
     * Filtros del listado y de los reportes. Todo va con bindings (consultas
     * parametrizadas de Eloquent), por lo que no hay inyección SQL.
     *
     * @param  array{rol?: ?string, estado?: ?string, buscar?: ?string}  $filtros
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        $rol = $filtros['rol'] ?? null;
        $estado = $filtros['estado'] ?? null;
        $buscar = trim((string) ($filtros['buscar'] ?? ''));

        return $query
            ->when($rol && array_key_exists($rol, self::ROLES), fn (Builder $q) => $q->where('rol', $rol))
            ->when($estado === 'activo', fn (Builder $q) => $q->where('activo', true))
            ->when($estado === 'inactivo', fn (Builder $q) => $q->where('activo', false))
            ->when($buscar !== '', function (Builder $q) use ($buscar) {
                // se escapan los comodines de LIKE para que "%" y "_" se busquen literalmente
                $like = '%'.addcslashes($buscar, '\\%_').'%';
                $q->where(fn (Builder $w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            });
    }

    public function actividadesFisicas(): HasMany
    {
        return $this->hasMany(ActividadFisica::class);
    }

    public function entrenamientos(): HasMany
    {
        return $this->hasMany(Entrenamiento::class);
    }
}
