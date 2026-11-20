# Ejecución de la implementación

El código vive en la aplicación Laravel raíz del repositorio. Requiere el mismo usuario autenticado que las rutas de inscripciones.

```powershell
php artisan migrate --seed
php artisan serve
```

Obtenga un token Sanctum de desarrollo desde la base local y guárdelo como variable privada `token` en Postman. Importe `postman/Practica-3.2-Enrutamiento-ILIA.postman_collection.json` y configure `base_url` como `http://127.0.0.1:8000`. Ejecute los seis casos; ninguno usa un token distribuido en los archivos.

La implementación consta de `RouteEnrollmentEventRequest`, `EnrollmentEventController` y los servicios `EnrollmentEventRouter`, `EnrollmentEventFilter` y `EnrollmentEventConsumer`. La ruta se encuentra en `routes/api.php` y está protegida con `auth:sanctum`. La prueba de regresión está en `tests/Feature/EnrollmentEventRoutingTest.php`.

```powershell
php artisan test tests/Feature/EnrollmentEventRoutingTest.php
```

Los consumidores son clases Laravel sincrónicas, simuladas en memoria. No se usa broker ni se alteran las inscripciones guardadas.
