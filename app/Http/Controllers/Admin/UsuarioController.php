<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Usuarios\ActualizarUsuarioRequest;
use App\Http\Requests\Usuarios\GuardarUsuarioRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CRUD de usuarios del panel.
 *  - index / show: administrador y moderador (solo lectura)
 *  - create / store / edit / update / destroy / toggle: solo administrador
 * (ver routes/web.php)
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = $request->only(['buscar', 'rol', 'estado']);

        $usuarios = User::with('perfil')
            ->filtrar($filtros)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'filtros'  => $filtros,
            'roles'    => User::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.create', ['roles' => User::ROLES]);
    }

    public function store(GuardarUsuarioRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        DB::transaction(function () use ($datos, $request) {
            $usuario = new User([
                'name'     => $datos['name'],
                'email'    => $datos['email'],
                'password' => $datos['password'],       // el cast "hashed" la encripta
            ]);
            $usuario->rol = $datos['rol'];              // asignación explícita (no fillable)
            $usuario->activo = $request->boolean('activo');
            $usuario->save();

            $this->guardarPerfil($usuario, $datos['perfil'] ?? []);
        });

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $usuario): View
    {
        return view('admin.usuarios.show', ['usuario' => $usuario->load('perfil')]);
    }

    public function edit(User $usuario): View
    {
        return view('admin.usuarios.edit', [
            'usuario' => $usuario->load('perfil'),
            'roles'   => User::ROLES,
        ]);
    }

    public function update(ActualizarUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $datos = $request->validated();
        $esElMismo = $usuario->is($request->user());
        $nuevoRol = $datos['rol'];
        $nuevoEstado = $request->boolean('activo');

        // Un administrador no puede quitarse a sí mismo el rol ni desactivarse (se quedaría sin acceso)
        if ($esElMismo && ($nuevoRol !== User::ROL_ADMIN || ! $nuevoEstado)) {
            return back()->withInput()->with('error', 'No puedes quitarte el rol de administrador ni desactivar tu propia cuenta.');
        }

        if ($this->quedariaSinAdmins($usuario, $nuevoRol, $nuevoEstado)) {
            return back()->withInput()->with('error', 'Debe existir al menos un administrador activo.');
        }

        DB::transaction(function () use ($usuario, $datos, $nuevoRol, $nuevoEstado) {
            $usuario->name = $datos['name'];
            $usuario->email = $datos['email'];
            if (! empty($datos['password'])) {
                $usuario->password = $datos['password'];
            }
            $usuario->rol = $nuevoRol;
            $usuario->activo = $nuevoEstado;
            $usuario->save();

            $this->guardarPerfil($usuario, $datos['perfil'] ?? []);

            // Si lo desactivan o le cambian la contraseña, se cierran sus sesiones de la app móvil
            if (! $nuevoEstado || ! empty($datos['password'])) {
                $usuario->tokens()->delete();
            }
        });

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /** Activar / desactivar sin entrar al formulario de edición. */
    public function toggle(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        if ($this->quedariaSinAdmins($usuario, $usuario->rol, ! $usuario->activo)) {
            return back()->with('error', 'Debe existir al menos un administrador activo.');
        }

        $usuario->activo = ! $usuario->activo;
        $usuario->save();

        if (! $usuario->activo) {
            $usuario->tokens()->delete();   // revoca los tokens de la app móvil
        }

        return back()->with('success', $usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        if ($this->quedariaSinAdmins($usuario, null, false)) {
            return back()->with('error', 'Debe existir al menos un administrador activo.');
        }

        $usuario->tokens()->delete();
        $usuario->delete();   // el perfil se elimina en cascada (llave foránea)

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }

    // ----------------------------------------------------------------- Auxiliares

    /** Crea o actualiza el perfil; si no se llenó ningún campo no crea un perfil vacío. */
    private function guardarPerfil(User $usuario, array $perfil): void
    {
        $datos = array_intersect_key($perfil, array_flip(['edad', 'sexo', 'altura', 'peso']));

        if ($usuario->perfil) {
            $usuario->perfil->update($datos);
        } elseif (array_filter($datos, fn ($v) => $v !== null && $v !== '')) {
            $usuario->perfil()->create($datos);
        }
    }

    /** ¿Tras el cambio propuesto no quedaría ningún administrador activo? */
    private function quedariaSinAdmins(User $usuario, ?string $nuevoRol, bool $nuevoActivo): bool
    {
        if (! $usuario->esAdmin() || ! $usuario->activo) {
            return false;   // el usuario no cuenta hoy como admin activo
        }

        if ($nuevoRol === User::ROL_ADMIN && $nuevoActivo) {
            return false;   // seguirá siendo admin activo
        }

        $otros = User::where('rol', User::ROL_ADMIN)->where('activo', true)->whereKeyNot($usuario->id)->count();

        return $otros === 0;
    }
}
