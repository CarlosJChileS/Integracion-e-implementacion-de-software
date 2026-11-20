# Entrega individual de la Práctica 3.2

**Autor:** Carlos Chile

**Asignatura:** Integración e Implementación de Software

**Nivel/paralelo:** Octavo / A

**Proyecto de aula:** ILIA

Este paquete contiene la guía recibida, el informe PDF con carátula institucional, la tabla de reglas, el diagrama del flujo real, el código fuente propio y las evidencias de los seis casos obligatorios. La contribución individual se identifica en el código Laravel y en la prueba `EnrollmentEventRoutingTest.php`; el mismo código está en el [repositorio del proyecto](https://github.com/CarlosJChileS/Integracion-e-implementacion-de-software/tree/main/3.2).

## Implementación

El productor envía eventos JSON a `POST /api/enrollment-events`, protegido con Sanctum. La petición valida `id`, `type`, `priority` y `data`. El `Message Filter` retiene prioridad `baja` y registra el motivo antes de invocar al router. Si el mensaje pasa el filtro, el `Content-Based Router` envía `enrollment.created`, `enrollment.updated` y `enrollment.cancelled` a tres clases consumidoras distintas. Un tipo desconocido se registra como `no_enrutables` con HTTP 202. Los datos incompletos se rechazan con HTTP 422.

Las clases consumidoras llaman al servicio de inscripciones del proyecto y crean, actualizan o cancelan datos del usuario autenticado. **No se configuró un broker ni una cola real:** se simula la entrega a cada destino mediante clases Laravel invocadas sincrónicamente, según permite la guía cuando no hay broker. Las operaciones de negocio sí se comprueban sobre SQLite; la limitación corresponde al transporte por cola.

## Reglas y pruebas

La tabla completa con condición, destino y justificación está en `documentacion/tabla_reglas_enrutamiento.md` y también en el informe. El diagrama editable está en `documentacion/flujo_mensajes.mmd`.

| Caso | Resultado observado |
|---|---|
| 01 · Creación | `inscripciones`; HTTP 200 y alta persistida |
| 02 · Actualización | `actualizaciones`; HTTP 200 y cambio persistido |
| 03 · Cancelación | `cancelaciones`; HTTP 200 y baja persistida |
| 04 · Tipo desconocido | `no_enrutables`; HTTP 202, con registro y sin consumidor |
| 05 · Prioridad baja | `filtrados`; HTTP 200, sin router ni consumidor |
| 06 · Falta `course_slug` | HTTP 422 antes del filtro y del router |

La carpeta `evidencias/postman/` contiene una captura PNG y una respuesta JSON saneada por caso, además del resumen de Postman CLI y las trazas correlacionadas del servidor. No se incluyen tokens ni secretos. La carpeta `evidencias/phpunit/` contiene los resultados verificables de la prueba funcional Laravel.

## Verificación

- Postman CLI 1.90.0 contra Laravel local y una base SQLite aislada: **6 de 6 solicitudes y 6 de 6 assertions aprobadas**.
- `EnrollmentEventRoutingTest`: **6 pruebas y 37 aserciones aprobadas**.
- `EnrollmentContractTest`: **14 pruebas y 63 aserciones aprobadas**.

Para repetir las pruebas automatizadas desde la raíz del repositorio Laravel:

```powershell
php artisan test tests/Feature/EnrollmentEventRoutingTest.php
php artisan test tests/Feature/EnrollmentContractTest.php
```

Para ejecutar manualmente la colección Postman, importa `postman/Practica-3.2-Enrutamiento-ILIA.postman_collection.json`, configura `base_url` y agrega un token Sanctum propio a la variable privada `token`. No hay credenciales dentro de esta entrega.

## Archivos de código incluidos

- `EnrollmentEventController.php` y `RouteEnrollmentEventRequest.php`: endpoint y validación previa.
- `EnrollmentEventRouter.php`: reglas por tipo y destino de revisión.
- `EnrollmentEventFilter.php`: regla de prioridad y registro del rechazo.
- `EnrollmentEventConsumer.php` y los consumidores `Created`, `Updated` y `Cancelled`: despacho y operaciones de dominio.
- `EnrollmentService.php` y DTO de inscripción: operaciones invocadas por cada consumidor.
- `routes/api.php` y `EnrollmentEventRoutingTest.php`: ruta y prueba de integración.

El proyecto completo y su configuración se conservan en GitHub; el ZIP lleva el código propio de la práctica junto con la documentación y las evidencias para la revisión en Moodle.
