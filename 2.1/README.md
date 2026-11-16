# 2.1 Integración en la capa de datos

Esta carpeta reúne la presentación de integración en la capa de datos, la actividad de arquitectura física/conectividad y la evidencia Laravel–PostgreSQL.

En el código Laravel de la raíz, las [migraciones](../database/migrations/) y [modelos](../app/Models/) definen la persistencia; el cliente usa la API y no accede directamente a la base. La VM `Integracion Ubuntu PostgreSQL` ejecuta la misma aplicación Laravel con PostgreSQL 18.6. La base `backend_api` pertenece al rol `laravel_app`; Laravel se conecta a `127.0.0.1:5432` desde la VM, y la API queda accesible desde el anfitrión en `http://127.0.0.1:8080`.

Para acceder a Ubuntu: `ssh -p 2224 carlo@127.0.0.1`. Las credenciales no se incluyen en los archivos públicos del proyecto. El servicio `laravel-integracion` inicia Laravel automáticamente y depende de PostgreSQL.

La verificación realizada en la VM incluyó las siete migraciones, los datos semilla (tres cursos y una cohorte), `php artisan db:show --counts` mostrando conexión `pgsql`, y una petición autenticada de inscripción que respondió HTTP 201 y se eliminó luego con HTTP 204. Una petición sin token respondió HTTP 401 JSON. La suite Laravel aprobó 16 pruebas y 65 aserciones.

La capa de datos respalda los DTO y contratos de [2.2](../2.2/README.md).
