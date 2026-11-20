from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.platypus import Flowable, PageBreak, Paragraph, SimpleDocTemplate, Spacer, Table, TableStyle

BASE = Path(__file__).parent
OUT = BASE / 'entrega' / 'Informe_Practica_3_2.pdf'
OUT.parent.mkdir(parents=True, exist_ok=True)
P = getSampleStyleSheet()
P.add(ParagraphStyle(name='TitleX', parent=P['Title'], fontName='Helvetica-Bold', fontSize=25, leading=30, textColor=colors.HexColor('#173C3A'), alignment=TA_LEFT, spaceAfter=10))
P.add(ParagraphStyle(name='HeadX', parent=P['Heading2'], fontName='Helvetica-Bold', fontSize=15, leading=19, textColor=colors.HexColor('#173C3A'), spaceBefore=8, spaceAfter=6))
P.add(ParagraphStyle(name='BodyX', parent=P['BodyText'], fontName='Helvetica', fontSize=9.5, leading=13, textColor=colors.HexColor('#344C45'), spaceAfter=6))
P.add(ParagraphStyle(name='SmallX', parent=P['BodyText'], fontName='Helvetica', fontSize=7.4, leading=9.5, textColor=colors.HexColor('#53665F'), spaceAfter=3, wordWrap='CJK'))


def footer(canvas, doc):
    canvas.saveState()
    canvas.setStrokeColor(colors.HexColor('#DCE3DD'))
    canvas.line(18 * mm, 14 * mm, 192 * mm, 14 * mm)
    canvas.setFont('Helvetica', 8)
    canvas.setFillColor(colors.HexColor('#65776F'))
    canvas.drawString(18 * mm, 9 * mm, 'Práctica 3.2 · Enrutamiento y filtrado')
    canvas.drawRightString(192 * mm, 9 * mm, str(doc.page))
    canvas.restoreState()


story = [
    Spacer(1, 7 * mm),
    Paragraph('Patrones de enrutamiento y distribución lógica', P['TitleX']),
    Paragraph('Práctica individual 3.2 · Integración e Implementación de Software', P['BodyX']),
    Paragraph('<b>Estudiante:</b> Carlos Chile &nbsp;&nbsp; <b>Nivel / paralelo:</b> 8.º / A &nbsp;&nbsp; <b>Fecha:</b> 20 de noviembre de 2026', P['BodyX']),
    Paragraph('Objetivo y resultado', P['HeadX']),
    Paragraph('Implementé Content-Based Router y Message Filter en el proceso ILIA de inscripciones. El cliente produce un evento JSON; la API lo valida, filtra prioridad baja antes de enrutar, ejecuta una operación real de EnrollmentService y registra sus decisiones con ID de correlación. Los seis casos Postman prueban tres consumidores, tipo desconocido, prioridad filtrada y campo obligatorio ausente.', P['BodyX']),
    Paragraph('Alcance de la implementación', P['HeadX']),
    Paragraph('La solución amplía la API Laravel del proyecto de aula y opera sobre SQLite usando el usuario autenticado. Los consumidores son clases Laravel sincrónicas: crean, actualizan o cancelan inscripciones reales. No se configuró RabbitMQ ni otra cola; se indica esta limitación de transporte, como pide la guía.', P['BodyX']),
    Paragraph('Contrato JSON', P['HeadX']),
    Paragraph('<font name="Courier">{ "id": "11111111-1111-4111-8111-111111111111", "type": "enrollment.created",<br/>  "priority": "normal", "data": { "course_slug": "prompting-para-docentes" } }</font>', P['SmallX']),
    Paragraph('Se exige id, type, priority y data. Los identificadores usan UUID. created requiere course_slug; updated requiere enrollment_id y course_slug; cancelled requiere enrollment_id. priority acepta baja, normal o alta. La API conserva X-Correlation-ID o genera un UUID.', P['BodyX']),
    Paragraph('Tabla de reglas', P['HeadX']),
]
rows = [
    ['Condición', 'Destino / decisión'],
    ['enrollment.created · prioridad normal o alta', 'inscripciones · consume'],
    ['Cualquier tipo · prioridad baja', 'filtrado antes del router; sin destino ni consumidor'],
    ['enrollment.updated', 'actualizaciones · consume'],
    ['enrollment.cancelled', 'cancelaciones · consume'],
    ['Tipo fuera del catálogo', 'no_enrutables · se registra y responde 202'],
    ['Campo requerido ausente o inválido', 'ningún destino · respuesta 422'],
]
table = Table([[Paragraph(str(cell), P['SmallX']) for cell in row] for row in rows], colWidths=[79 * mm, 91 * mm], repeatRows=1)
table.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),colors.HexColor('#173C3A')),('TEXTCOLOR',(0,0),(-1,0),colors.white),('FONTNAME',(0,0),(-1,0),'Helvetica-Bold'),('GRID',(0,0),(-1,-1),0.4,colors.HexColor('#D6DED8')),('ROWBACKGROUNDS',(0,1),(-1,-1),[colors.white,colors.HexColor('#F3F5F1')]),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),6),('RIGHTPADDING',(0,0),(-1,-1),6),('TOPPADDING',(0,0),(-1,-1),5),('BOTTOMPADDING',(0,0),(-1,-1),5)]))
story += [table, PageBreak(), Paragraph('Flujo y pruebas', P['HeadX']), Paragraph('El productor envía el evento a la API protegida con Sanctum. Tras validar el contrato, Message Filter detiene la prioridad baja; si pasa, Content-Based Router elige al consumidor o marca el evento como no_enrutable. Las decisiones se correlacionan y se guardan en Laravel log.', P['BodyX'])]
cases = [
    ['Caso', 'Resultado esperado', 'HTTP'],
    ['01 Creada', 'inscripciones · consumido', '200'],
    ['02 Actualizada', 'actualizaciones · consumido', '200'],
    ['03 Cancelada', 'cancelaciones · consumido', '200'],
    ['04 Desconocido', 'no_enrutables · no_enrutable', '202'],
    ['05 Prioridad baja', 'filtrados · el router no se ejecuta', '200'],
    ['06 Falta course_slug', 'error data.course_slug', '422'],
]
table2 = Table([[Paragraph(str(cell), P['SmallX']) for cell in row] for row in cases], colWidths=[36 * mm, 98 * mm, 36 * mm], repeatRows=1)
table2.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),colors.HexColor('#173C3A')),('TEXTCOLOR',(0,0),(-1,0),colors.white),('GRID',(0,0),(-1,-1),0.4,colors.HexColor('#D6DED8')),('ROWBACKGROUNDS',(0,1),(-1,-1),[colors.white,colors.HexColor('#F3F5F1')]),('VALIGN',(0,0),(-1,-1),'TOP')]))
story += [table2, Spacer(1, 8), Paragraph('Evidencia de ejecución', P['HeadX']), Paragraph('Ejecuté la colección con Postman CLI 1.90.0 contra el servidor Laravel local y una SQLite aislada. Las seis solicitudes y sus seis assertions pasaron. Las capturas incluyen el JSON solicitado, respuesta HTTP, efecto persistido del consumidor y la traza local vinculada por correlation_id. No se incluyen tokens.', P['BodyX']), Paragraph('Postman CLI: 6 de 6 casos aprobados. PHPUnit: 6 de 6 casos funcionales (37 aserciones); además pasan las 14 pruebas del contrato de inscripciones.', P['BodyX']), Paragraph('Implementación', P['HeadX'])]


class MessageFlow(Flowable):
    def __init__(self):
        super().__init__()
        self.width = 170 * mm
        self.height = 82 * mm

    def draw(self):
        from reportlab.pdfbase.pdfmetrics import stringWidth

        canvas = self.canv
        canvas.setStrokeColor(colors.HexColor('#173C3A'))
        canvas.setFillColor(colors.HexColor('#F3F5F1'))

        def box(x, y, w, h, lines, fill='#F3F5F1'):
            canvas.setFillColor(colors.HexColor(fill))
            canvas.roundRect(x, y, w, h, 5, fill=1, stroke=1)
            canvas.setFillColor(colors.HexColor('#173C3A'))
            canvas.setFont('Helvetica-Bold', 8)
            for index, line in enumerate(lines):
                canvas.drawCentredString(x + w / 2, y + h / 2 + (len(lines) - 1) * 5 - index * 11, line)

        def arrow(x1, y1, x2, y2):
            canvas.setStrokeColor(colors.HexColor('#607C70'))
            canvas.setFillColor(colors.HexColor('#607C70'))
            canvas.setLineWidth(1.2)
            canvas.line(x1, y1, x2, y2)
            angle = __import__('math').atan2(y2 - y1, x2 - x1)
            for delta in (-0.45, 0.45):
                canvas.line(x2, y2, x2 - 6 * __import__('math').cos(angle + delta), y2 - 6 * __import__('math').sin(angle + delta))

        box(0, 102, 60, 32, ['Productor', 'cliente ILIA'])
        box(75, 102, 85, 32, ['API + Sanctum', 'validación'])
        box(180, 102, 85, 32, ['Message Filter', 'prioridad baja'], '#E5EFE9')
        box(295, 102, 90, 32, ['Content-Based', 'Router'])
        arrow(60, 118, 75, 118)
        arrow(160, 118, 180, 118)
        arrow(265, 118, 295, 118)
        box(0, 48, 90, 38, ['inscripciones', 'crea inscripción'])
        box(132, 48, 90, 38, ['actualizaciones', 'cambia curso'])
        box(264, 48, 90, 38, ['cancelaciones', 'elimina propia'])
        box(132, 0, 105, 32, ['No reconocido', 'no_enrutables · 202'], '#F7EEDC')
        box(0, 0, 95, 32, ['Baja prioridad', 'filtrado; sin destino'], '#F7EEDC')
        arrow(45, 102, 45, 86)
        canvas.setStrokeColor(colors.HexColor('#607C70'))
        canvas.setLineWidth(1.2)
        canvas.line(340, 102, 340, 94)
        canvas.line(45, 94, 309, 94)
        arrow(45, 94, 45, 86)
        arrow(177, 94, 177, 86)
        arrow(309, 94, 309, 86)
        canvas.line(385, 118, 405, 118)
        canvas.line(405, 118, 405, 16)
        arrow(405, 16, 237, 16)


story += [Paragraph('Implementación y flujo', P['HeadX']), Paragraph('La API llama a EnrollmentService desde tres clases consumidoras: EnrollmentCreatedConsumer, EnrollmentUpdatedConsumer y EnrollmentCancelledConsumer. El siguiente flujo refleja el orden implementado; el diagrama Mermaid queda disponible para editar.', P['BodyX']), MessageFlow(), Paragraph('Conclusión individual', P['HeadX']), Paragraph('Enrutar elige un consumidor según el tipo del mensaje; filtrar detiene mensajes de prioridad baja antes de seleccionar un destino. Los consumidores crean, actualizan o cancelan inscripciones reales del usuario autenticado. Los tipos no reconocidos quedan registrados como no_enrutables. No hay broker: las clases Laravel procesan los mensajes de forma sincrónica.', P['BodyX'])]
doc = SimpleDocTemplate(str(OUT), pagesize=A4, leftMargin=18 * mm, rightMargin=18 * mm, topMargin=15 * mm, bottomMargin=20 * mm, title='Práctica 3.2 patrones de enrutamiento y filtrado', author='Carlos Chile')
doc.build(story, onFirstPage=footer, onLaterPages=footer)
print(OUT)
