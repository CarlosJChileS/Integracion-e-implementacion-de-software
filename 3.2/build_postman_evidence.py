from __future__ import annotations

import json
import re
import sys
from datetime import datetime
from pathlib import Path

import yaml
from PIL import Image, ImageDraw, ImageFont


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    name = 'arialbd.ttf' if bold else 'arial.ttf'
    return ImageFont.truetype(str(Path('C:/Windows/Fonts') / name), size)


def wrap(text: str, face: ImageFont.FreeTypeFont, max_width: int) -> list[str]:
    words = text.split()
    lines: list[str] = []
    line = ''
    for word in words:
        candidate = f'{line} {word}'.strip()
        if face.getlength(candidate) <= max_width or not line:
            line = candidate
        else:
            lines.append(line)
            line = word
    if line:
        lines.append(line)
    return lines


def log_entries(path: Path) -> list[dict[str, object]]:
    pattern = re.compile(r'^\[(.*?)\] local\.([A-Z]+): ([^ ]+) (\{.*\})\s*$')
    entries = []
    for line in path.read_text(encoding='utf-8', errors='replace').splitlines():
        match = pattern.match(line)
        if match:
            try:
                context = json.loads(match.group(4))
            except json.JSONDecodeError:
                continue
            entries.append({
                'timestamp': match.group(1),
                'level': match.group(2),
                'event': match.group(3),
                'context': context,
                'raw': line,
            })
    return entries


def draw_capture(record: dict[str, object], target: Path) -> None:
    width = 1500
    margin = 58
    body_font = font(19)
    mono_font = ImageFont.truetype('C:/Windows/Fonts/consola.ttf', 17)
    title_font = font(31, True)
    heading_font = font(21, True)

    sections: list[tuple[str, list[str]]] = []
    request = json.dumps(record['solicitud'], ensure_ascii=False, indent=2)
    response = json.dumps(record['respuesta'], ensure_ascii=False, indent=2)
    sections.append(('SOLICITUD · POST /api/enrollment-events', request.splitlines()))
    sections.append((f"RESPUESTA · HTTP {record['status_http']}", response.splitlines()))
    traces = record['trazas_servidor']
    trace_lines = [f"{line['timestamp']} {line['level']} {line['event']} {json.dumps(line['context'], ensure_ascii=False)}" for line in traces]
    if not trace_lines:
        trace_lines = ['Sin eventos de router o consumidor: la validación respondió antes del enrutamiento.']
    sections.append(('TRAZA DEL SERVIDOR', trace_lines))

    line_counts = sum(max(1, len(wrap(line, mono_font, width - 2 * margin - 42))) for _, lines in sections for line in lines)
    height = 350 + line_counts * 23 + len(sections) * 88
    image = Image.new('RGB', (width, height), '#edf1ec')
    draw = ImageDraw.Draw(image)
    draw.rounded_rectangle((22, 22, width - 22, height - 22), radius=22, fill='white', outline='#d6ded8', width=2)
    draw.text((margin, 48), 'Práctica 3.2 · Evidencia Postman CLI', font=title_font, fill='#173c3a')
    draw.text((margin, 98), str(record['caso']), font=font(23, True), fill='#28645a')
    draw.text((margin, 140), f"Resultado: {record['verificacion']}    Correlation ID: {record['correlation_id'] or 'no generado (validación previa)'}", font=body_font, fill='#53665f')
    y = 195

    for heading, lines in sections:
        draw.text((margin, y), heading, font=heading_font, fill='#173c3a')
        y += 38
        block_lines = []
        for line in lines:
            block_lines.extend(wrap(line, mono_font, width - 2 * margin - 42))
        block_height = max(52, len(block_lines) * 23 + 22)
        draw.rounded_rectangle((margin, y, width - margin, y + block_height), radius=9, fill='#f5f7f4')
        text_y = y + 12
        for line in block_lines:
            draw.text((margin + 18, text_y), line, font=mono_font, fill='#263a33')
            text_y += 23
        y += block_height + 25

    image.save(target)


def main() -> None:
    if len(sys.argv) != 4:
        raise SystemExit('usage: python build_postman_evidence.py EXECUTIONS.yaml LARAVEL.log OUTPUT_DIR')

    execution_path, log_path, output_dir = map(Path, sys.argv[1:])
    output_dir.mkdir(parents=True, exist_ok=True)
    documents = list(yaml.safe_load_all(execution_path.read_text(encoding='utf-8')))
    runs = [document for document in documents if isinstance(document, dict) and 'item' in document]
    started_at = datetime.fromisoformat(documents[0]['generatedAt'].replace('Z', '+00:00')).replace(tzinfo=None, microsecond=0)
    logs = [entry for entry in log_entries(log_path) if datetime.fromisoformat(str(entry['timestamp'])) >= started_at]
    results = []
    clean_trace_lines = []

    for index, run in enumerate(runs, start=1):
        item = run['item']
        request = run['request']
        response = run['response']
        body = request.get('body', {}).get('raw', '{}')
        request_body = json.loads(body)
        response_body = json.loads(response['body'])
        correlation_id = next(
            (header['value'] for header in response.get('headers', []) if header.get('key', '').lower() == 'x-correlation-id'),
            next(
                (header['value'] for header in request.get('headers', []) if header.get('key', '').lower() == 'x-correlation-id'),
                response_body.get('correlation_id'),
            ),
        )
        traces = [entry for entry in logs if entry['context'].get('correlation_id') == correlation_id]
        tests = run.get('tests', [])
        passed = bool(tests) and all(test.get('status') == 'passed' for test in tests)
        case = item['name']
        record: dict[str, object] = {
            'caso': case,
            'metodo': request['method'],
            'endpoint': '/api/enrollment-events',
            'solicitud': request_body,
            'status_http': response['code'],
            'respuesta': response_body,
            'correlation_id': correlation_id,
            'resultado_assertion_postman': tests,
            'efecto_consumidor': response_body.get('effect'),
            'trazas_servidor': [{key: entry[key] for key in ('timestamp', 'level', 'event', 'context')} for entry in traces],
            'verificacion': 'OK' if passed else 'REVISAR',
            'origen': 'Postman CLI 1.90.0 contra Laravel local y SQLite; token eliminado del artefacto.',
        }
        results.append(record)
        slug = f'{index:02d}'
        (output_dir / f'{slug}-postman.json').write_text(json.dumps(record, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
        draw_capture(record, output_dir / f'{slug}-captura.png')
        clean_trace_lines.extend(str(entry['raw']) for entry in traces)

    summary = {
        'herramienta': 'Postman CLI 1.90.0',
        'servidor': 'Laravel local sobre SQLite aislada',
        'peticiones': len(results),
        'assertions_aprobadas': sum(len(run['resultado_assertion_postman']) for run in results if run['verificacion'] == 'OK'),
        'casos_aprobados': sum(run['verificacion'] == 'OK' for run in results),
        'casos_totales': len(results),
        'credenciales_incluidas': False,
    }
    (output_dir / 'resumen-postman.json').write_text(json.dumps(summary, ensure_ascii=False, indent=2) + '\n', encoding='utf-8')
    (output_dir / 'trazas-servidor.log').write_text('\n'.join(clean_trace_lines) + '\n', encoding='utf-8')
    if summary['casos_aprobados'] != 6:
        raise SystemExit(f"Expected 6 passing cases, got {summary['casos_aprobados']}")
    print(json.dumps(summary, ensure_ascii=False))


if __name__ == '__main__':
    main()
