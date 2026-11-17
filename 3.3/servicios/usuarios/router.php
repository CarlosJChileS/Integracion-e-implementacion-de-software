<?php

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    echo json_encode(['mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    return;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path !== '/usuarios/7') {
    http_response_code(404);
    echo json_encode(['mensaje' => 'Usuario de prueba no encontrado.'], JSON_UNESCAPED_UNICODE);
    return;
}

echo json_encode(['id' => 7, 'nombre' => 'Ana'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
