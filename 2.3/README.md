# Práctica 2.3 – Patrones de traducción y normalización

**Estudiante:** Carlos Chile  
**Curso / paralelo:** 8.º nivel / A  
**Fecha:** 16 de noviembre de 2026

## Escenario

La administración académica heredada de ILIA y un portal asociado pueden describir la misma inscripción con nombres y estructuras distintas. El backend conserva el contrato de 2.2 (`course_slug`, `cohort_code`); el adaptador de esta práctica convierte ambas entradas a ese modelo común, normaliza los formatos y comprueba que las referencias existan en el catálogo.

La solución individual está en `App\Services\Practica23\CarlosChile\EnrollmentPayloadTranslator`. El endpoint autenticado `POST /api/integrations/enrollments/normalize` devuelve el objeto normalizado sin crear una inscripción. El cliente puede enviar el objeto `data` como cuerpo de `POST /api/enrollments`; ese contrato existente no cambia.

## Entregables

- [Mapa de estructuras, reglas y diagrama](docs/MAPA_Y_CONTRATO.md)
- [Casos P1, P2 y P3 con resultados](evidencias/README.md)
- [Código del traductor](../app/Services/Practica23/CarlosChile/EnrollmentPayloadTranslator.php)
- [Pruebas automatizadas](../tests/Feature/Practica23/CarlosChile/EnrollmentPayloadTranslatorTest.php)

## Ejecución y comprobación

Desde la raíz del repositorio:

```bash
php artisan test --filter=EnrollmentPayloadTranslatorTest
php artisan test
```

La API requiere un Bearer token de Sanctum. En la VM PostgreSQL, se puede consumir desde `http://127.0.0.1:8080`; los ejemplos usan datos ficticios del catálogo sembrado. No se incluye ningún token ni archivo `.env` en esta carpeta.
