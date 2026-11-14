# Miembro 3: planificación y nutrición

Implementa los requisitos de objetivos, recordatorios, catálogo de alimentos, planes de comida, alertas y reportes. El manual del panel se encuentra en Manual_Usuario_Panel.md y Manual_Usuario_Panel.pdf, con imágenes en capturas/.

## Instalación e integración

Requiere la base existente de Laravel 12, PHP 8.2 y sus dependencias. Habilitar gd en PHP para Excel, como indica el README original.

~~~bash
composer install
npm ci
npm run build
php artisan migrate
php artisan db:seed --class=PlanificacionSeeder
php artisan test
~~~

En una instalación nueva, primero copiar .env.example a .env, configurar MySQL y ejecutar php artisan key:generate; después php artisan migrate --seed. No cambiar APP_KEY de una instalación con perfiles cifrados. El seeder del módulo es repetible; solo agrega ejemplos personales en local/testing y cuando existe usuario@salud.test.

La migración 2026_11_14_000001_create_planificacion_tables.php agrega tablas nuevas sin modificar las del miembro 1. El miembro 2 debe integrar estas tablas evitando recrearlas con otros nombres. Las rutas están en planificacion_web.php y planificacion_api.php. La navegación respeta el layout base del miembro 4.

## Acceso

| Acción | Administrador | Moderador | Usuario móvil |
|---|---|---|---|
| Consultar planificación y alimentos en el panel | Sí | Sí | No |
| Crear, editar y eliminar alimentos | Sí | No | No |
| Exportar reportes del panel | Sí | No | No |
| Gestionar planificación propia mediante API | Sí | Sí | Sí |

La API siempre aplica auth:sanctum y activo. user_id se asigna desde el token y nunca desde el cuerpo. Un ID ajeno devuelve 404. El catálogo móvil es de solo lectura. Los nombres de rutas API llevan api. para evitar colisiones con el panel.

## Endpoints

Prefijo /api. Enviar Accept: application/json y Authorization: Bearer TOKEN. Éxito: {success, message, data}; los listados usan paginación de 20 elementos dentro de data (registros en data.data, total en data.total). Los errores conservan el manejo del módulo base: validación 422 con errors, autenticación 401, cuenta inactiva 403 e inexistente/ajeno 404.

| Recurso | Métodos y rutas | Datos / filtros |
|---|---|---|
| Objetivos | GET/POST /objetivos; GET/PUT/PATCH/DELETE /objetivos/{objetivo} | titulo, descripcion?, unidad, valor_meta, valor_actual?, fecha_inicio, fecha_fin; filtro estado |
| Recordatorios | GET/POST /recordatorios; GET/PUT/PATCH/DELETE /recordatorios/{recordatorio} | nombre, tipo, dosis, hora, frecuencia, fecha_inicio, fecha_fin?, activo?; filtros tipo, activo |
| Tomas | POST/DELETE /recordatorios/{recordatorio}/cumplimientos | fecha: YYYY-MM-DD; POST es idempotente |
| Alimentos | GET /alimentos y /alimentos/{alimento} | filtros buscar y categoria |
| Comidas | GET/POST /planes-comida; GET/PUT/PATCH/DELETE /planes-comida/{plan} | fecha, tipo, notas?, alimentos: [{alimento_id, porciones}]; filtros fecha, tipo |

Objetivos y recordatorios aceptan actualizaciones parciales. Los planes necesitan fecha, tipo y la lista completa de alimentos en cada actualización. Importar la colección Postman y ajustar base_url y fecha; el login y las altas guardan token/ID automáticamente. No repetir un plan del mismo usuario, fecha y tipo; el servidor valida esa combinación única.

## Reglas y significado de los datos

- Objetivos: metas acumulativas crecientes, por ejemplo kilómetros, sesiones o días. valor_meta > 0; valor_actual >= 0. cumplido si alcanza la meta; vencido si no la alcanza y fecha_fin es anterior a hoy; en_curso en otro caso. El porcentaje se limita a 100. No representa directamente una meta de bajar peso.
- Recordatorios: tipo medicamento o vitamina; hora HH:mm; frecuencia diaria, semanal o mensual. Cada recordatorio representa una toma por día programado. Para varias horas, crear varios recordatorios. Semanal usa el día de la semana del inicio; mensual usa el día del mes del inicio, ajustado al último día si no existe. fecha_fin es inclusiva y opcional.
- Las tomas se registran por fecha, sin duplicados, nunca para el futuro ni fuera del calendario. Pausar impide registrar nuevas tomas. No se puede cambiar inicio, fin ni frecuencia si ya existen tomas; crear otro recordatorio conserva el historial. El módulo almacena la planificación, sin enviar notificaciones push ni recomendar dosis.
- Alimentos: calorías por porción y peso de esa porción en gramos. Categorías: frutas, verduras, cereales, proteinas, lacteos, grasas, bebidas, otros. No se elimina un alimento utilizado en planes. La llave foránea también protege frente a borrados concurrentes.
- Planes: desayuno, almuerzo, cena o merienda. calorias_totales suma porciones × calorias_porcion. Las calorías se copian del catálogo al incorporar el alimento; editar el catálogo no altera planes ya registrados. Conservar un alimento durante la edición mantiene sus calorías originales.
- Fechas de calendario YYYY-MM-DD según la zona de la aplicación (config/app.php, UTC en la base existente). La hora es una hora local declarada por el usuario; no se convierte ni se ejecuta un planificador.

## Reportes

/admin/planificacion/reportes permite elegir usuario y fechas. /admin/planificacion/reportes/{objetivos|recordatorios}/{pdf|excel} descarga ambos formatos y admite los mismos filtros de consulta. Máximo 366 días; por defecto, del inicio del mes a hoy.

Objetivos: la selección usa fecha_fin; el estado y avance son actuales, sin reconstrucción histórica. Recordatorios: compara tomas registradas con las programadas que ya debían ocurrir, incluidas las de hoy; excluye fechas futuras. El estado activo/pausado es el actual: no hay historial de pausas, por lo que no se descuentan pausas en el denominador. PDF y Excel incluyen periodo, totales y porcentaje. Excel escribe texto explícito para que nombres que empiecen con = no se ejecuten como fórmulas.

## Alertas comunes

SweetAlert2 se compila desde npm. resources/js/alertas.js procesa formularios con data-confirm y todos los DELETE del panel. resources/views/partials/alertas.blade.php muestra success, error, status y validaciones, conservando texto visible si no carga JavaScript. El módulo de usuarios ya usa el mismo mecanismo.

## Validación realizada

82 pruebas automáticas y sus comprobaciones pasaron. Migraciones y seeders ejecutados en una base SQLite aislada; frontend compilado con Vite. Pruebas de panel y API cubren permisos, propiedad, fechas, calendarios, idempotencia, calorías, unicidad, filtros y exportaciones reales. Se revisaron pantallas en navegador y las alertas de éxito, error y confirmación. MySQL debe verificarse con la configuración del equipo antes de integrar; no se modificó ninguna base MySQL existente.

## Documentación oficial consultada

- [Laravel 12: migraciones](https://laravel.com/docs/12.x/migrations).
- [SweetAlert2: instalación y uso](https://sweetalert2.github.io/).
