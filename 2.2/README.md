# 2.2 DTO y contratos de intercambio JSON

Esta carpeta reúne la [guía individual](Guia_Individual_Tema_2_2_IS803.docx), el [informe de Carlos Chile](Practica_2.2_Carlos_Chile.docx), la [especificación del contrato JSON](docs/CONTRATO_JSON.md), la [colección Postman](postman/ILIA-Inscripciones.postman_collection.json) y las [evidencias de las pruebas](evidencias/README.md).

El proyecto Laravel asociado está en la **raíz del repositorio** para que una sola implementación dé continuidad a los temas anteriores y siguientes.

| Responsabilidad | Código Laravel en la raíz |
|---|---|
| DTO de creación y actualización | [app/Data/Enrollment](../app/Data/Enrollment/) |
| Validación de solicitudes | [app/Http/Requests](../app/Http/Requests/) |
| Controlador REST | [EnrollmentController.php](../app/Http/Controllers/Api/EnrollmentController.php) |
| Reglas y persistencia | [EnrollmentService.php](../app/Services/EnrollmentService.php) |
| Serialización de respuesta | [EnrollmentResource.php](../app/Http/Resources/EnrollmentResource.php) |
| Rutas API | [routes/api.php](../routes/api.php) |
| Migraciones | [database/migrations](../database/migrations/) |
| Pruebas del contrato | [EnrollmentContractTest.php](../tests/Feature/EnrollmentContractTest.php) |

Para instalar y ejecutar el sistema, sigue el [README principal](../README.md) desde la raíz. Las pruebas se ejecutan allí con `php artisan test`.
