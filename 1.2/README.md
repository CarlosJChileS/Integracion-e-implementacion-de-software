# 1.2 Acoplamiento, cohesión e interoperabilidad

Aquí se conserva el material sobre separación de responsabilidades e interoperabilidad.

En el Laravel de la raíz, las rutas y el [controlador](../app/Http/Controllers/Api/EnrollmentController.php) coordinan HTTP; los [Form Requests](../app/Http/Requests/) validan; los [DTO](../app/Data/Enrollment/) transportan datos; el [servicio](../app/Services/EnrollmentService.php) aplica reglas y Eloquent persiste. Esta separación lleva a los contratos REST de [1.3](../1.3/README.md).
