# Integración e implementación de software

Repositorio organizado siguiendo la secuencia de contenidos de la asignatura. Los materiales de clase y las evidencias están en carpetas numeradas; el proyecto Laravel que integra estos temas está en la raíz.

## Secuencia de contenidos

| Carpeta | Tema | Continuidad en el proyecto |
|---|---|---|
| [`1.1`](1.1/README.md) | Fundamentos de integración y sistemas heterogéneos | Contexto del proceso de inscripción de ILIA y de sus clientes. |
| [`1.2`](1.2/README.md) | Acoplamiento, cohesión e interoperabilidad | Separación entre HTTP, validación, DTO, servicio y persistencia. |
| [`1.3`](1.3/README.md) | Comunicación síncrona, APIs REST y contratos | Endpoints REST y contrato JSON de la API. |
| [`1.4`](1.4/README.md) | Arquitectura distribuida y modelos desacoplados | Límites entre cliente, API, servicios y base de datos. |
| [`2.1`](2.1/README.md) | Integración en la capa de datos | Migraciones, persistencia y material de arquitectura/conectividad. |
| [`2.2`](2.2/README.md) | DTO y contratos de intercambio JSON | Guía, informe, contrato, colección Postman y evidencias de la práctica. |

Cada carpeta explica cómo su tema prepara el siguiente. La implementación común se mantiene en este directorio raíz para que las prácticas puedan referirse al mismo sistema.

## Proyecto Laravel (raíz)

API de inscripción a cursos del proyecto ILIA. Usa Laravel 13, PHP 8.3 o superior y SQLite en esta versión local. La carpeta `2.2` documenta la práctica correspondiente; el código no está duplicado dentro de ella.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

La API queda en `http://127.0.0.1:8000`. Ejecuta las pruebas desde la raíz con `php artisan test`. Para probar peticiones, importa `2.2/postman/ILIA-Inscripciones.postman_collection.json`.

La conexión PostgreSQL de la actividad 2.1 se documenta con sus materiales y evidencias en `2.1`. Esta copia del proyecto conserva SQLite como motor ejecutable mientras se completa y verifica la conexión de la VM Ubuntu.
