# Práctica 3.2 Patrones de enrutamiento y distribución lógica

**Estudiante:** Carlos Chile · **Curso:** 8.º nivel / A · **Proyecto:** ILIA

## Entrega

- [Informe PDF](entrega/Informe_Practica_3_2.pdf).
- [Código fuente Laravel](../app/Http/Controllers/Api/EnrollmentEventController.php) y [prueba funcional](../tests/Feature/EnrollmentEventRoutingTest.php) del proyecto ILIA versionado en este repositorio.
- [Colección Postman](postman/Practica-3.2-Enrutamiento-ILIA.postman_collection.json).
- [Diagrama Mermaid](docs/flujo-mensajes.mmd).
- Seis solicitudes y respuestas JSON verificadas en `evidencias/`.
- [Seis capturas Postman CLI con respuesta, consumidor y traza](evidencias/postman/).
- La carpeta `entrega` contiene el PDF final.

## Resumen

La ruta protegida `POST /api/enrollment-events` valida el contrato, filtra prioridad baja antes del router y dirige tres tipos a consumidores Laravel que operan sobre las inscripciones reales del usuario autenticado. Se conservan trazas con `X-Correlation-ID`. No hay broker; los casos de desconocido, filtro y validación no modifican la base. Consulte [la guía de ejecución, reglas y componentes](README_IMPLEMENTACION.md).
