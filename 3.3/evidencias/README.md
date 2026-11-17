# Evidencias de la Práctica 3.3

Las respuestas de `respuesta-exito.json` y `respuesta-fallo.json` se obtuvieron consultando la API Laravel local mediante HTTP. `registro-fallo.log` es la línea correspondiente de `storage/logs/laravel.log`; su `correlation_id` coincide con la respuesta 502. Las imágenes PNG muestran esos valores reales en formato legible.

## Flujo exitoso

- Endpoint: `GET /api/resumen-solicitud/101`.
- Correlation ID: `practica33-postman-01`.
- Resultado observado: HTTP 200 y objeto `usuario` + `estado` unificado.
- Captura: `captura-exito-http.png`.

## Servicio de estados detenido

- Se detuvo el proceso PHP que escucha en `127.0.0.1:8092` y se envió la misma petición al orquestador.
- Correlation ID: `practica33-postman-fail-01`.
- Resultado observado: HTTP 502, mensaje controlado, sin traza; el log identifica `service=estados` y `reason=connection_error`.
- Captura: `captura-fallo-http.png`.

## Recuperación

Se reinició el servicio de estados y se comprobó que el endpoint volvió a responder HTTP 200 con el estado `pendiente`.

La colección Postman lista en `../postman/` permite reproducir los dos casos en Postman. Sus assertions verifican código HTTP, JSON y cabecera de correlación.
