# Tabla de reglas de enrutamiento

| Condición | Destino / decisión | Justificación |
|---|---|---|
| `priority = baja` (cualquier tipo válido) | `filtrados`; HTTP 200, no se invoca el router ni un consumidor | La prioridad baja no cumple la admisión de negocio; se deja traza `message_filter.rejected`. |
| `type = enrollment.created`, con prioridad `normal` o `alta` | `inscripciones` → `EnrollmentCreatedConsumer` | Crea la inscripción del usuario autenticado al curso indicado. |
| `type = enrollment.updated`, con prioridad `normal` o `alta` | `actualizaciones` → `EnrollmentUpdatedConsumer` | Actualiza el curso de una inscripción propia del usuario autenticado. |
| `type = enrollment.cancelled`, con prioridad `normal` o `alta` | `cancelaciones` → `EnrollmentCancelledConsumer` | Cancela únicamente una inscripción propia del usuario autenticado. |
| Tipo desconocido y prioridad `normal` o `alta` | `no_enrutables`; HTTP 202, registrado sin ejecutar consumidor | Conserva constancia del evento y permite tratarlo como caso de revisión. |
| Falta un campo obligatorio o no cumple el contrato | Sin destino; HTTP 422 | La validación se ejecuta antes del filtro y del enrutador. |

Los mensajes incluyen `id`, `type`, `priority` y `data`. La API propaga `X-Correlation-ID` o genera un UUID. `created` requiere `data.course_slug`; `updated` requiere `data.enrollment_id` y `data.course_slug`; `cancelled` requiere `data.enrollment_id`.
