<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegistroRequest;
use App\Http\Resources\UsuarioResource;
use App\Models\Perfil;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Autenticación de la API con Laravel Sanctum (tokens personales) para la app React Native.
 *
 * Formato de respuesta: { "success": bool, "message": string, "data": {...} }
 * La app guarda "data.token" y lo envía en cada petición:  Authorization: Bearer <token>
 */
class AuthController extends Controller
{
    /** Hash bcrypt válido de una clave aleatoria; solo sirve para igualar tiempos de respuesta. */
    private const HASH_FALSO = '$2y$12$76fE7L2UQ5jWdufg2pZfk.K5keR01CCC2U7RCM3GxSgwYR/72eIBS';

    /** POST /api/auth/registro */
    public function registro(RegistroRequest $request): JsonResponse
    {
        $usuario = DB::transaction(function () use ($request) {
            // "rol" y "activo" no son fillable: todo registro público queda como "usuario" activo.
            $usuario = User::create($request->safe()->only(['name', 'email', 'password']));

            $datosPerfil = array_filter(
                $request->safe()->only(['edad', 'sexo', 'altura', 'peso']),
                fn ($valor) => $valor !== null
            );
            if ($datosPerfil) {
                $usuario->perfil()->create($datosPerfil);
            }

            // recarga desde la BD para traer los valores por defecto (rol = usuario, activo = true)
            return $usuario->refresh();
        });

        return $this->respuestaConToken($usuario, $request->input('dispositivo'), 'Registro exitoso.', 201);
    }

    /** POST /api/auth/login */
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = User::where('email', $request->email)->first();

        // Se compara siempre contra un hash (aunque el correo no exista) para no revelar por tiempo
        // de respuesta si el correo está registrado, y se da un mensaje único para ambos casos.
        $hash = $usuario?->password ?? self::HASH_FALSO;
        $correcto = Hash::check($request->password, $hash) && $usuario !== null;

        if (! $correcto) {
            return $this->error('Credenciales incorrectas.', 401);
        }

        if (! $usuario->activo) {
            return $this->error('Tu cuenta está desactivada. Contacta al administrador.', 403);
        }

        return $this->respuestaConToken($usuario, $request->input('dispositivo'), 'Inicio de sesión correcto.');
    }

    /** POST /api/auth/logout  (auth:sanctum) – revoca el token actual, o todos con {"todos": true}. */
    public function logout(Request $request): JsonResponse
    {
        $usuario = $request->user();

        if ($request->boolean('todos')) {
            $usuario->tokens()->delete();
        } else {
            $usuario->currentAccessToken()->delete();
        }

        return response()->json(['success' => true, 'message' => 'Sesión cerrada correctamente.', 'data' => null]);
    }

    /** GET /api/auth/me  (auth:sanctum) */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado.',
            'data'    => new UsuarioResource($request->user()->load('perfil')),
        ]);
    }

    // ----------------------------------------------------------------- Auxiliares

    private function respuestaConToken(User $usuario, ?string $dispositivo, string $mensaje, int $codigo = 200): JsonResponse
    {
        $token = $usuario->createToken($dispositivo ?: 'app-movil')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'usuario'    => new UsuarioResource($usuario->load('perfil')),
            ],
        ], $codigo);
    }

    private function error(string $mensaje, int $codigo): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $mensaje, 'data' => null], $codigo);
    }
}
