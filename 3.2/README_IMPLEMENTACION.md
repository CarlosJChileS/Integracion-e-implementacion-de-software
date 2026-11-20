# Ejecución de la implementación

El código vive en la aplicación Laravel raíz del repositorio. Requiere el mismo usuario autenticado que las rutas de inscripciones.

```powershell
php artisan migrate --seed
php artisan serve
```

Obtenga un token Sanctum de desarrollo desde la base local y guárdelo como variable privada `token` en Postman. Importe `postman/Practica-3.2-Enrutamiento-ILIA.postman_collection.json` y configure `base_url` como `http://127.0.0.1:8000`. Ejecute los seis casos; ninguno usa un token distribuido en los archivos.

El flujo de cada mensaje es: autenticación y validación → filtro de prioridad → router por tipo → consumidor. Los eventos de creación y actualización requieren un `course_slug` que exista; los de actualización y cancelación requieren `enrollment_id`. El filtro descarta prioridad `baja` antes del router; la validación no genera decisiones de enrutamiento.

El router (`EnrollmentEventRouter`) selecciona las clases `EnrollmentCreatedConsumer`, `EnrollmentUpdatedConsumer` o `EnrollmentCancelledConsumer`. Estas llaman al `EnrollmentService` real y crean, actualizan o cancelan la inscripción del usuario autenticado. El efecto persistido aparece en la respuesta y la decisión queda en `storage/logs/laravel.log`. El evento desconocido se registra como `no_enrutables` y no altera SQLite.

La ruta `POST /api/enrollment-events` se encuentra en `routes/api.php` y está protegida con `auth:sanctum`. La colección se ejecutó con Postman CLI; las seis capturas, respuestas JSON y trazas correlacionadas están en `evidencias/postman/`.

```powershell
php artisan test tests/Feature/EnrollmentEventRoutingTest.php
```

Los consumidores son servicios Laravel sincrónicos y ejecutan operaciones reales de inscripción. No se usa broker ni cola; se documenta así porque el laboratorio no cuenta con uno.
