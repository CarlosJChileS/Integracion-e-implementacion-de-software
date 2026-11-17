# Evidencias de pruebas – Práctica 2.3

La suite de Laravel verifica el traductor por HTTP con la base SQLite de prueba en memoria. En la VM, el mismo endpoint se puede contrastar con PostgreSQL y el catálogo de ILIA. Las solicitudes de los ejemplos son ficticias; no contienen credenciales ni tokens reales.

## Casos mínimos de la guía

| Caso | Entrada | Resultado esperado y verificado |
|---|---|---|
| P1 – Origen A válido | `source: legacy`, `codigo_curso: " PROMPTING-PARA-DOCENTES "`, `codigo_paralelo: " ilia-demo "` | HTTP 200; `data` contiene `course_slug: "prompting-para-docentes"` y `cohort_code: "ILIA-DEMO"`. |
| P2 – Origen B equivalente | `source: portal`, `course.slug: "prompting-para-docentes"`, `cohort.code: "ILIA-DEMO"` | HTTP 200; mismo objeto `data` y mismos tipos que P1. |
| P3 – Campo obligatorio ausente | `source: legacy`, falta `codigo_curso` | HTTP 422; JSON `errors` identifica `codigo_curso`; no se crea inscripción. |

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

Respuesta esperada:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "codigo_curso": ["El código del curso es obligatorio."]
  }
}
```

Laravel puede incluir campos adicionales de contexto en el error; la aserción confirma el código HTTP 422 y el error asociado a `codigo_curso`.

## Resultados automatizados

- `php artisan test --filter=EnrollmentPayloadTranslatorTest`: **5 pruebas aprobadas, 24 aserciones**.
- `php artisan test`: **21 pruebas aprobadas, 89 aserciones**.
- Pint `--test` sobre traductor, request, controlador, ruta y pruebas: **aprobado**.

La cobertura incluye equivalencia P1/P2, normalización de espacios y mayúsculas, cohorte omitida convertida a `null`, rechazo de campos faltantes o no textuales, referencias inexistentes, claves ambiguas y requisito de autenticación. La suite anterior de la API se conserva.

## Reflexión individual

Resolví la incompatibilidad entre un sistema académico legado y un portal asociado que nombran y organizan distinto los datos de inscripción. Elegí un traductor independiente para concentrar el mapeo, la normalización y la validación, y devolver el DTO ya usado por el backend. Verifiqué que los dos orígenes generan el mismo JSON, que los datos inválidos producen errores 422 claros y que normalizar no crea registros.
