# 2.1 Integración en la capa de datos

Esta carpeta reúne la presentación de integración en la capa de datos, la actividad de arquitectura física/conectividad y la evidencia Laravel–PostgreSQL.

En el código Laravel de la raíz, las [migraciones](../database/migrations/) y [modelos](../app/Models/) definen la persistencia; el cliente usa la API y no accede directamente a la base. La versión que acompaña esta estructura ejecuta con SQLite. La conexión a PostgreSQL en Ubuntu queda señalada como parte de esta etapa y no se considera terminada hasta verificarla desde Laravel.

La capa de datos respalda los DTO y contratos de [2.2](../2.2/README.md).
