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
| [`2.3`](2.3/README.md) | Patrones de traducción y normalización | Adaptador individual de Carlos Chile para convertir dos formatos de inscripción al DTO común. |
| [`3.3`](3.3/README.md) | Orquestación y composición de capacidades | Servicios HTTP locales de usuarios/estados, resumen Laravel, correlación y manejo de fallos. |

Cada carpeta explica cómo su tema prepara el siguiente. La implementación común se mantiene en este directorio raíz para que las prácticas puedan referirse al mismo sistema.

## Proyecto Laravel (raíz)

API de inscripción a cursos del proyecto ILIA. Usa Laravel 13 y PHP 8.3 o superior. El archivo `.env.example` conserva SQLite para una ejecución local sencilla; la instancia desplegada en la VM Ubuntu utiliza PostgreSQL. Las carpetas numeradas documentan cada práctica; el código Laravel compartido permanece en la raíz. La práctica 3.3 añade un orquestador HTTP independiente y dos servicios de prueba en su carpeta.

### Ejecución local con SQLite

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

La API local queda en `http://127.0.0.1:8000`. Ejecuta las pruebas desde la raíz con `php artisan test`. Para probar peticiones, importa `2.2/postman/ILIA-Inscripciones.postman_collection.json`.

### VM Ubuntu y PostgreSQL

La VM `Integracion Ubuntu PostgreSQL` ejecuta una copia de este mismo repositorio, Laravel y PostgreSQL 18.6. La aplicación se inicia automáticamente con el servicio `laravel-integracion`; PostgreSQL conserva la base `backend_api` y Laravel se conecta mediante `127.0.0.1:5432` dentro de la VM. El acceso desde el equipo anfitrión usa estos puertos reenviados por VirtualBox:

- API: `http://127.0.0.1:8080` (puerto 8000 del invitado).
- SSH: `ssh -p 2224 carlo@127.0.0.1`.

La cuenta de PostgreSQL es `laravel_app`. Las contraseñas se entregan por separado y no se guardan en este repositorio. Para consultar el estado de los servicios dentro de Ubuntu, usa `sudo systemctl status laravel-integracion postgresql`.

La conexión Laravel–PostgreSQL se preparó con `composer install`, `php artisan migrate --seed --force` y se comprobó con `php artisan db:show --counts`. La práctica 2.1 reúne la arquitectura y sus evidencias; la práctica 2.2 documenta el contrato JSON y sus validaciones; la 2.3 incorpora el traductor de estructuras de inscripción.


### Práctica 3.3

Inicia dos servicios PHP locales en los puertos 8091 y 8092 más el orquestador Laravel en el puerto 8000. La carpeta [3.3](3.3/README.md) incluye el procedimiento, las pruebas automatizadas, colección Postman, secuencia y evidencias.
