from __future__ import annotations

from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile

BASE = Path(__file__).resolve().parent
PROJECT = BASE.parent
OUT = BASE / 'entrega' / 'Practica_3_2_Carlos_Chile.zip'
ROOT = 'Practica_3_2_Carlos_Chile'

FILES: dict[str, Path] = {
    'README_ENTREGA.md': BASE / 'README_ENTREGA.md',
    'guia/Guia_Practica_Individual_3_2_IS803.docx': BASE / 'referencia' / 'Guia_Practica_Individual_3_2_IS803.docx',
    'informe/Informe_Practica_3_2.pdf': BASE / 'entrega' / 'Informe_Practica_3_2.pdf',
    'documentacion/README_IMPLEMENTACION.md': BASE / 'README_IMPLEMENTACION.md',
    'documentacion/tabla_reglas_enrutamiento.md': BASE / 'docs' / 'tabla-reglas-enrutamiento.md',
    'documentacion/flujo_mensajes.mmd': BASE / 'docs' / 'flujo-mensajes.mmd',
    'postman/Practica-3.2-Enrutamiento-ILIA.postman_collection.json': BASE / 'postman' / 'Practica-3.2-Enrutamiento-ILIA.postman_collection.json',
    'codigo/app/Http/Controllers/Api/EnrollmentEventController.php': PROJECT / 'app/Http/Controllers/Api/EnrollmentEventController.php',
    'codigo/app/Http/Requests/RouteEnrollmentEventRequest.php': PROJECT / 'app/Http/Requests/RouteEnrollmentEventRequest.php',
    'codigo/app/Services/EnrollmentEvents/EnrollmentEventRouter.php': PROJECT / 'app/Services/EnrollmentEvents/EnrollmentEventRouter.php',
    'codigo/app/Services/EnrollmentEvents/EnrollmentEventFilter.php': PROJECT / 'app/Services/EnrollmentEvents/EnrollmentEventFilter.php',
    'codigo/app/Services/EnrollmentEvents/EnrollmentEventConsumer.php': PROJECT / 'app/Services/EnrollmentEvents/EnrollmentEventConsumer.php',
    'codigo/app/Services/EnrollmentEvents/EnrollmentCreatedConsumer.php': PROJECT / 'app/Services/EnrollmentEvents/EnrollmentCreatedConsumer.php',
    'codigo/app/Services/EnrollmentEvents/EnrollmentUpdatedConsumer.php': PROJECT / 'app/Services/EnrollmentEvents/EnrollmentUpdatedConsumer.php',
    'codigo/app/Services/EnrollmentEvents/EnrollmentCancelledConsumer.php': PROJECT / 'app/Services/EnrollmentEvents/EnrollmentCancelledConsumer.php',
    'codigo/app/Services/EnrollmentService.php': PROJECT / 'app/Services/EnrollmentService.php',
    'codigo/app/Data/Enrollment/EnrollCourseData.php': PROJECT / 'app/Data/Enrollment/EnrollCourseData.php',
    'codigo/app/Data/Enrollment/UpdateEnrollmentData.php': PROJECT / 'app/Data/Enrollment/UpdateEnrollmentData.php',
    'codigo/routes/api.php': PROJECT / 'routes/api.php',
    'codigo/tests/Feature/EnrollmentEventRoutingTest.php': PROJECT / 'tests/Feature/EnrollmentEventRoutingTest.php',
}

for name in ('01-creada', '02-actualizada', '03-cancelada', '04-desconocido', '05-filtrado', '06-validacion'):
    FILES[f'evidencias/phpunit/{name}.json'] = BASE / 'evidencias' / f'{name}.json'

for path in sorted((BASE / 'evidencias' / 'postman').iterdir()):
    if path.is_file():
        FILES[f'evidencias/postman/{path.name}'] = path

missing = [f'{archive_path} <- {source}' for archive_path, source in FILES.items() if not source.is_file()]
if missing:
    raise SystemExit('Missing required delivery files:\n' + '\n'.join(missing))

OUT.parent.mkdir(parents=True, exist_ok=True)
with ZipFile(OUT, 'w', compression=ZIP_DEFLATED, compresslevel=9) as archive:
    for archive_path, source in sorted(FILES.items()):
        archive.write(source, f'{ROOT}/{archive_path}')

print(f'Created {OUT} with {len(FILES)} files')
