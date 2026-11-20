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
    Paragraph('Implementé Content-Based Router y Message Filter en el proceso ILIA de inscripciones. Un endpoint API valida un evento JSON, decide su consumidor por el tipo, evalúa la prioridad para eventos de creación, y registra cada decisión con un ID de correlación. Los casos funcionales comprueban tres rutas, tipo desconocido, mensaje filtrado y validación incompleta.', P['BodyX']),
    Paragraph('Alcance de la implementación', P['HeadX']),
    Paragraph('La solución amplía la API Laravel local del proyecto de aula. Los tres consumidores son clases invocadas sincrónicamente, simuladas en el sentido indicado por la guía; no se configuró RabbitMQ ni otra cola. Los eventos de demostración sirven para evidenciar distribución lógica y no modifican las inscripciones persistidas.', P['BodyX']),
    Paragraph('Contrato JSON', P['HeadX']),
    Paragraph('<font name="Courier">{ "id": "11111111-1111-4111-8111-111111111111", "type": "enrollment.created",<br/>  "priority": "normal", "data": { "enrollment_id": "22222222-2222-4222-8222-222222222222",<br/>  "course_slug": "prompting-para-docentes" } }</font>', P['SmallX']),
    Paragraph('Se exige id, type, priority y data con enrollment_id y course_slug. Los identificadores usan UUID; priority acepta baja, normal o alta. La API conserva X-Correlation-ID o genera un UUID y lo devuelve en la respuesta y el registro.', P['BodyX']),
    Paragraph('Tabla de reglas', P['HeadX']),
]
rows = [
    ['Condición', 'Destino / decisión'],
    ['enrollment.created · prioridad normal o alta', 'inscripciones · consume'],
    ['enrollment.created · prioridad baja', 'inscripciones · filtrado; no llama al consumidor'],
    ['enrollment.updated', 'actualizaciones · consume'],
    ['enrollment.cancelled', 'cancelaciones · consume'],
    ['Tipo fuera del catálogo', 'no_enrutables · se registra y responde 202'],
    ['Campo requerido ausente o inválido', 'ningún destino · respuesta 422'],
]
table = Table([[Paragraph(str(cell), P['SmallX']) for cell in row] for row in rows], colWidths=[79 * mm, 91 * mm], repeatRows=1)
table.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),colors.HexColor('#173C3A')),('TEXTCOLOR',(0,0),(-1,0),colors.white),('FONTNAME',(0,0),(-1,0),'Helvetica-Bold'),('GRID',(0,0),(-1,-1),0.4,colors.HexColor('#D6DED8')),('ROWBACKGROUNDS',(0,1),(-1,-1),[colors.white,colors.HexColor('#F3F5F1')]),('VALIGN',(0,0),(-1,-1),'TOP'),('LEFTPADDING',(0,0),(-1,-1),6),('RIGHTPADDING',(0,0),(-1,-1),6),('TOPPADDING',(0,0),(-1,-1),5),('BOTTOMPADDING',(0,0),(-1,-1),5)]))
story += [table, PageBreak(), Paragraph('Flujo y pruebas', P['HeadX']), Paragraph('Productor → API / Sanctum → validación → Message Filter (para creación) → Content-Based Router → consumidor o no_enrutables. Las decisiones se correlacionan y se guardan en Laravel log.', P['BodyX'])]
cases = [
    ['Caso', 'Resultado esperado', 'HTTP'],
    ['01 Creada', 'inscripciones · consumido', '200'],
    ['02 Actualizada', 'actualizaciones · consumido', '200'],
    ['03 Cancelada', 'cancelaciones · consumido', '200'],
    ['04 Desconocido', 'no_enrutables · no_enrutable', '202'],
    ['05 Prioridad baja', 'inscripciones · filtrado', '200'],
    ['06 Falta course_slug', 'error data.course_slug', '422'],
]
table2 = Table([[Paragraph(str(cell), P['SmallX']) for cell in row] for row in cases], colWidths=[36 * mm, 98 * mm, 36 * mm], repeatRows=1)
table2.setStyle(TableStyle([('BACKGROUND',(0,0),(-1,0),colors.HexColor('#173C3A')),('TEXTCOLOR',(0,0),(-1,0),colors.white),('GRID',(0,0),(-1,-1),0.4,colors.HexColor('#D6DED8')),('ROWBACKGROUNDS',(0,1),(-1,-1),[colors.white,colors.HexColor('#F3F5F1')]),('VALIGN',(0,0),(-1,-1),'TOP')]))
story += [table2, Spacer(1, 8), Paragraph('Evidencia de ejecución', P['HeadX']), Paragraph('La evidencia incluida contiene la solicitud y respuesta JSON de cada caso del cliente HTTP de pruebas Laravel. La suite ejercita la ruta protegida, validación, controlador y servicios; todas las aserciones de los seis escenarios pasan. La colección Postman incluida permite repetir los mismos casos desde Postman con un token Sanctum local, guardado en una variable privada y no incluido en la entrega.', P['BodyX']), Paragraph('Seis casos aprobados · 23 aserciones · evidencia detallada en evidencias/practica-3.2/*.json', P['BodyX']), Paragraph('Implementación', P['HeadX'])]


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

        box(0, 102, 60, 32, ['Productor', 'evento ILIA'])
        box(90, 102, 85, 32, ['API + Sanctum', 'validación JSON'])
        box(208, 102, 90, 32, ['Content-Based', 'Router'])
        arrow(60, 118, 90, 118)
        arrow(175, 118, 208, 118)
        box(0, 48, 90, 38, ['Message Filter', 'created · prioridad'], '#E5EFE9')
        box(104, 48, 80, 38, ['inscripciones', 'consumidor'])
        box(199, 48, 86, 38, ['actualizaciones', 'consumidor'])
        box(300, 48, 80, 38, ['cancelaciones', 'consumidor'])
        box(112, 0, 105, 32, ['No reconocido', 'no_enrutables · 202'], '#F7EEDC')
        box(0, 0, 90, 32, ['Baja prioridad', 'filtrado · sin consumo'], '#F7EEDC')
        arrow(250, 102, 45, 86)
        arrow(250, 102, 144, 86)
        arrow(265, 102, 242, 86)
        arrow(279, 102, 340, 86)
        arrow(44, 48, 44, 32)
        arrow(250, 102, 190, 32)


story += [Paragraph('Implementación y flujo', P['HeadX']), Paragraph('La copia ejecutable contiene la validación, el controlador, Content-Based Router, Message Filter y consumidores. El siguiente flujo muestra dónde se aplican las reglas; el diagrama Mermaid queda disponible para editar.', P['BodyX']), MessageFlow(), Paragraph('Conclusión individual', P['HeadX']), Paragraph('Enrutar selecciona un destino usando el contenido del evento; filtrar impide que un mensaje válido para cierto destino alcance ese consumidor si incumple una condición. Un evento de tipo desconocido se registra como no enrutable, mientras que una prioridad baja se registra como filtrada. Ambos casos difieren de un fallo de transporte. La práctica usa consumidores Laravel simulados en memoria, sin broker real.', P['BodyX'])]
doc = SimpleDocTemplate(str(OUT), pagesize=A4, leftMargin=18 * mm, rightMargin=18 * mm, topMargin=15 * mm, bottomMargin=20 * mm, title='Práctica 3.2 patrones de enrutamiento y filtrado', author='Carlos Chile')
doc.build(story, onFirstPage=footer, onLaterPages=footer)
print(OUT)
