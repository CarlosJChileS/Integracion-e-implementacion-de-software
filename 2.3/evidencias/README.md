# Evidencias de pruebas – Práctica 2.3

La suite de Laravel verifica el traductor por HTTP con SQLite de prueba en memoria. Además, se enviaron los tres casos por HTTP a la instancia de Laravel conectada a PostgreSQL 18.6 en la VM, usando el catálogo de ILIA. El token temporal empleado para P1–P3 se revocó al terminar; no se conserva en el repositorio.

## Casos mínimos de la guía

| Caso | Entrada | Resultado esperado y verificado |
|---|---|---|
| P1 – Origen A válido | `source: legacy`, `codigo_curso: " PROMPTING-PARA-DOCENTES "`, `codigo_paralelo: " ilia-demo "` | VM PostgreSQL: HTTP 200; `data` contiene `course_slug: "prompting-para-docentes"` y `cohort_code: "ILIA-DEMO"`. |
| P2 – Origen B equivalente | `source: portal`, `course.slug: "prompting-para-docentes"`, `cohort.code: "ILIA-DEMO"` | VM PostgreSQL: HTTP 200; mismo objeto `data` y mismos tipos que P1. |
| P3 – Campo obligatorio ausente | `source: legacy`, falta `codigo_curso` | VM PostgreSQL: HTTP 422; JSON `errors` identifica `codigo_curso`; no se crea inscripción. |

## Solicitudes y respuestas

### P1 – Payload heredado

```json
{
  "source": "legacy",
  "payload": {
    "codigo_curso": " PROMPTING-PARA-DOCENTES ",
    "codigo_paralelo": " ilia-demo "
  }
}
```

Salida:

```json
{
  "data": {
    "course_slug": "prompting-para-docentes",
    "cohort_code": "ILIA-DEMO"
  }
}
```

### P2 – Payload anidado del portal

```json
{
  "source": "portal",
  "payload": {
    "course": {"slug": "prompting-para-docentes"},
    "cohort": {"code": "ILIA-DEMO"}
  }
}
```

La respuesta `data` coincide exactamente con P1. Las pruebas comprueban que la transformación no persiste una inscripción.

### P3 – Error controlado

Entrada:

```json
{
  "source": "legacy",
  "payload": {
    "codigo_paralelo": "ILIA-DEMO"
  }
}
```

Respuesta observada en la VM:

```json
{
  "message": "El código del curso es obligatorio.",
  "errors": {
    "codigo_curso": [
      "El código del curso es obligatorio."
    ]
  }
}
```

La respuesta real se obtuvo con HTTP 422. La prueba automatizada confirma además el error asociado a `codigo_curso` y que no se crea una inscripción.

## Resultados automatizados

- `php artisan test --filter=EnrollmentPayloadTranslatorTest`: **5 pruebas aprobadas, 24 aserciones**.
- `php artisan test`: **21 pruebas aprobadas, 89 aserciones**.
- Pint `--test` sobre traductor, request, controlador, ruta y pruebas: **aprobado**.
- Solicitudes reales contra la VM PostgreSQL: P1 HTTP 200, P2 HTTP 200, P3 HTTP 422; tras revocar el token temporal, el mismo endpoint respondió HTTP 401.

La cobertura incluye equivalencia P1/P2, normalización de espacios y mayúsculas, cohorte omitida convertida a `null`, rechazo de campos faltantes o no textuales, referencias inexistentes, claves ambiguas y requisito de autenticación. La suite anterior de la API se conserva.

## Reflexión individual

Resolví la incompatibilidad entre un sistema académico legado y un portal asociado que nombran y organizan distinto los datos de inscripción. Elegí un traductor independiente para concentrar el mapeo, la normalización y la validación, y devolver el DTO ya usado por el backend. Verifiqué que los dos orígenes generan el mismo JSON, que los datos inválidos producen errores 422 claros y que normalizar no crea registros.
