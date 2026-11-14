# Salud y Bienestar – Parte web (Laravel)

Desarrollo Web usando Software Libre – ITCA-FEPADE · Proyecto 2

Este repositorio contiene el **Módulo 1 – Usuarios, autenticación y seguridad** y el **Módulo 3 – Objetivos, recordatorios y comidas**.
Los demás miembros agregan sus módulos sobre esta misma base.

## Módulo 3: planificación y nutrición

Consulta de objetivos, recordatorios y planes de comida; CRUD de alimentos; API privada de planificación; SweetAlert2 en todo el panel; reportes PDF/Excel. [Instalación, contrato y reglas](docs/Miembro3_Integracion.md) · [Manual del panel](docs/Manual_Usuario_Panel.md) · [Manual PDF](docs/Manual_Usuario_Panel.pdf) · [Colección Postman](docs/postman/Salud_Bienestar_Miembro3_Planificacion.postman_collection.json).

## Requisitos

PHP 8.2+, Composer, Node 18+, MySQL (XAMPP sirve). Extensiones PHP: `mbstring`, `openssl`, `pdo_mysql`, `zip`, `fileinfo` y **`gd`**
(en XAMPP: quitar el `;` de `extension=gd` en `php.ini`; PhpSpreadsheet/Excel la pide al instalar).

## Puesta en marcha

```bash
composer install
npm install && npm run build
cp .env.example .env          # en Windows: copy .env.example .env
php artisan key:generate
```

Crear la base de datos `salud_bienestar_dwusl` (utf8mb4) en MySQL, revisar `DB_*` en `.env` y luego:

```bash
php artisan migrate --seed
php artisan serve
```

Panel: <http://127.0.0.1:8000> · API: <http://127.0.0.1:8000/api>

### Usuarios de prueba (solo desarrollo – ver `database/seeders/UsuariosSeeder.php`)

| Rol | Correo | Entra al panel |
|---|---|---|
| Administrador | `admin@salud.test` | Sí, con todos los permisos |
| Moderador | `moderador@salud.test` | Sí, solo consulta de usuarios |
| Usuario | `usuario@salud.test` | No (solo app móvil / API) |

Las contraseñas están en el seeder. **Cámbialas antes de cualquier despliegue real.**

### Recuperación de contraseña en desarrollo

`MAIL_MAILER=log`: el correo con el enlace queda en `storage/logs/laravel.log`.

## Qué incluye este módulo

| Requisito | Dónde está |
|---|---|
| Login, logout y recuperación de contraseña (Laravel UI) | `app/Http/Controllers/Auth/*`, `resources/views/auth/*`, `routes/web.php` |
| API con Sanctum: registro, login, logout | `app/Http/Controllers/Api/AuthController.php`, `routes/api.php` |
| CRUD de usuarios + perfil, activar/desactivar | `app/Http/Controllers/Admin/UsuarioController.php`, `resources/views/admin/usuarios/*` |
| Middleware `EsAdmin`, `EsModerador`, `UsuarioActivo` | `app/Http/Middleware/*`, alias en `bootstrap/app.php` |
| Form Requests y errores en Bootstrap | `app/Http/Requests/*`, `resources/views/admin/usuarios/_form.blade.php` |
| Hash, CSRF, SQL seguro, cifrado | `User` (cast `hashed`), `@csrf`, scope `User::filtrar()`, `App\Casts\DecimalCifrado` |
| Reporte PDF / Excel filtrable por rol y estado | `Admin/ReporteUsuarioController.php`, `app/Exports/UsuariosExport.php` |
| Pruebas | `tests/Feature/*` → `php artisan test` (82 pruebas) |
| Colección Postman | `docs/postman/` |
| Introducción y Objetivos del documento | `docs/Introduccion_y_Objetivos.md` |

### Roles y rutas protegidas

| Rol | Panel | Usuarios (ver) | Usuarios (crear/editar/eliminar/reportes) |
|---|---|---|---|
| `admin` | ✔ | ✔ | ✔ |
| `moderador` | ✔ | ✔ | ✘ (403) |
| `usuario` | ✘ (403) | ✘ | ✘ |

Para proteger rutas de otros módulos (en `routes/web.php`):

```php
// Cualquier miembro del panel ya está dentro de ['auth', 'activo', 'es_moderador'].
// Secciones solo de administradores:
Route::middleware('es_admin')->group(function () { /* ... */ });
```

### Seguridad aplicada

- **Contraseñas**: bcrypt (cast `hashed`); política: 8+ caracteres con mayúscula, minúscula, número y símbolo (`Password::defaults()` en `AppServiceProvider`, en producción también `uncompromised`).
- **CSRF**: middleware de Laravel en el grupo `web` y `@csrf` en todos los formularios. La API es sin estado (token Bearer).
- **SQL**: solo Eloquent/Query Builder con bindings; la búsqueda escapa los comodines de `LIKE`.
- **Cifrado**: `peso` y `altura` se guardan cifrados (AES-256 con `APP_KEY`) con el cast `DecimalCifrado`. ⚠️ Si cambia `APP_KEY` esos datos dejan de poder leerse.
- **Asignación masiva**: `rol` y `activo` no son `fillable`; nadie puede hacerse admin enviando `rol=admin`.
- **Sesiones y tokens**: se bloquea el login tras 5 intentos; los tokens de la API caducan a los 7 días (`SANCTUM_EXPIRATION`); al desactivar a un usuario, cambiarle la contraseña o eliminarlo se revocan sus tokens.
- **Otros**: cabeceras de seguridad, `throttle` en login/registro de la API, no se puede eliminar/desactivar al último administrador ni a uno mismo.

## Contrato de la API (acordado con el equipo React Native)

Todas las respuestas: `{ "success": bool, "message": string, "data": ... }`. Enviar siempre `Accept: application/json` y,
en rutas protegidas, `Authorization: Bearer <token>`. Errores de validación → `422` con `errors` por campo.

| Método | Ruta | Auth | Descripción |
|---|---|---|---|
| POST | `/api/auth/registro` | — | `name, email, password, password_confirmation` + opcionales `edad, sexo, altura, peso, dispositivo` → `201` con `data.token` |
| POST | `/api/auth/login` | — | `email, password, dispositivo?` → `data.token` · `401` credenciales · `403` cuenta desactivada |
| POST | `/api/auth/logout` | Bearer | Revoca el token actual (`{"todos": true}` revoca todos) |
| GET | `/api/auth/me` | Bearer | Usuario y perfil |
| GET / PUT | `/api/perfil` | Bearer | Ver / actualizar edad, sexo, altura (cm), peso (kg) |

Rangos: edad 10–100 · altura 100–250 cm · peso 20–300 kg · sexo `masculino|femenino|otro`.

## Notas de integración para el equipo

- **Miembro 2 (BD)**: las migraciones de este módulo son `*_add_rol_y_activo_to_users_table`, `*_create_perfiles_table` y la de `personal_access_tokens` (Sanctum). La FK de otras tablas debe apuntar a `users.id`.
- **Miembro 4 (layout)**: `resources/views/layouts/app.blade.php` es una base mínima; conservar `@yield('content')`, `@include('partials.alertas')` y el enlace *Usuarios*. `home.blade.php` espera su dashboard.
- **Miembro 3 (alertas)**: SweetAlert2 ya está integrado en las confirmaciones de borrado y en los mensajes de todo el panel (`resources/js/alertas.js`, `partials/alertas`).
- Cada miembro agrega su línea en `DatabaseSeeder` y sus rutas API dentro del grupo `auth:sanctum` + `activo` de `routes/api.php`.
