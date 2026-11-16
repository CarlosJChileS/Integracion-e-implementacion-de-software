# API de integración ILIA — versión local SQLite

Implementación individual en Laravel basada en el proceso de inscripción a un curso del proyecto ILIA. El backend original de ILIA usa FastAPI; esta carpeta contiene una versión Laravel independiente para practicar contratos de intercambio, arquitectura por capas y persistencia centralizada sin cambiar aquel backend.

**Estudiante:** Carlos Chile.  
**Curso / paralelo:** 8.º nivel / A · **Fecha:** 15 de noviembre de 2026.  
**Proceso:** inscripción de un docente a un curso, con cohorte opcional.  
**Proceso:** CRUD de las inscripciones del docente autenticado. Todos los endpoints requieren token Bearer de Sanctum.

## Requisitos

- PHP 8.3 o superior
- Composer
- Laravel 13

La versión actual utiliza SQLite. La integración con PostgreSQL en una VM Ubuntu se implementará en una segunda fase; no se requiere VM para ejecutar esta versión.

## Instalación y ejecución

Desde esta carpeta:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

La API queda disponible en `http://127.0.0.1:8000`. Para crear un token ficticio de prueba, abre `php artisan tinker` y ejecuta:

```powershell
php artisan tinker --execute='$user = App\Models\User::query()->where("email", "docente.demo@ilia.test")->firstOrFail(); echo $user->createToken("postman-demo")->plainTextToken;'
```

Usa el token mostrado solo en el cliente local. No lo incluyas en capturas ni lo subas al repositorio.

## Endpoints REST

| Método | Ruta | Resultado |
|---|---|---|
| `GET` | `/api/enrollments` | Lista paginada de inscripciones del usuario autenticado. |
| `POST` | `/api/enrollments` | Crea una inscripción; repetirla devuelve el registro existente. |
| `GET` | `/api/enrollments/{id}` | Consulta una inscripción propia. |
| `PATCH` | `/api/enrollments/{id}` | Cambia el curso y/o cohorte de una inscripción propia. |
| `DELETE` | `/api/enrollments/{id}` | Elimina una inscripción propia y responde `204`. |

El cuerpo JSON y los códigos de respuesta están descritos en [docs/CONTRATO_JSON.md](docs/CONTRATO_JSON.md). Las peticiones son síncronas: cada cliente espera la respuesta HTTP antes de continuar.

## Organización de responsabilidades

```text
Cliente web o móvil
        │ HTTP + JSON + Bearer token
        ▼
Route (rutas/api.php) → Sanctum → Form Request → Controller
                                               │ DTO
                                               ▼
                                      EnrollmentService
                                               │ Eloquent
                                               ▼
                                       SQLite local
                                               │
                                               ▼
                                 EnrollmentResource → JSON
```

Las rutas declaran los recursos y delegan; el controlador coordina HTTP; los Form Requests validan entrada; los DTO transportan campos permitidos; el servicio aplica reglas y persistencia; el Resource serializa la salida. El cliente no conoce el esquema de la base y no se conecta directamente a SQLite.

La conexión se configura con variables de entorno (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). La siguiente fase cambiará SQLite por PostgreSQL alojado en Ubuntu, conservando el contrato HTTP si el esquema se mantiene compatible.

## Pruebas

```powershell
php artisan test
```

La colección importable de Postman está en [`postman/ILIA-Inscripciones.postman_collection.json`](postman/ILIA-Inscripciones.postman_collection.json). Define `base_url` como `http://127.0.0.1:8000` y `token` como el token de prueba recién creado.

## Documentación de entrega

- [Contratos, arquitectura, proceso y pruebas](docs/CONTRATO_JSON.md)
- [Evidencias reproducibles](evidencias/README.md)
- [Reflexión individual](docs/CONTRATO_JSON.md#reflexión-individual-borrador-para-revisar)

Los cursos y la cohorte son datos ficticios de demostración, basados en los slugs del catálogo de ILIA. Las migraciones SQLite son propias de esta práctica y no se aplican a la base del proyecto original.
