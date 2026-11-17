<?php

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    return;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (! preg_match('#\A/estados/(\d+)\z#', $path, $matches)) {
    http_response_code(404);
    echo json_encode(['mensaje' => 'Estado de prueba no encontrado.'], JSON_UNESCAPED_UNICODE);
    return;
}

$solicitudId = (int) $matches[1];
echo json_encode(['solicitud_id' => $solicitudId, 'estado' => 'pendiente'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
