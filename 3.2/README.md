# Práctica 3.2 Patrones de enrutamiento y distribución lógica

**Estudiante:** Carlos Chile · **Curso:** 8.º nivel / A · **Proyecto:** ILIA

## Entrega

- [Informe PDF](entrega/Informe_Practica_3_2.pdf).
- [Código fuente Laravel](../app/Http/Controllers/Api/EnrollmentEventController.php) y [prueba funcional](../tests/Feature/EnrollmentEventRoutingTest.php) del proyecto ILIA versionado en este repositorio.
- [Colección Postman](postman/Practica-3.2-Enrutamiento-ILIA.postman_collection.json).
- [Diagrama Mermaid](docs/flujo-mensajes.mmd).
- Seis solicitudes y respuestas JSON verificadas en `evidencias/`.
- La carpeta `entrega` contiene el PDF final.

## Resumen

La ruta protegida `POST /api/enrollment-events` valida el contrato del mensaje, preserva o genera `X-Correlation-ID`, filtra eventos de creación de prioridad baja y enruta tres tipos de eventos a consumidores Laravel simulados. No hay broker ni persistencia de los eventos de demostración. Consulte [la guía de ejecución, reglas y componentes](README_IMPLEMENTACION.md).
