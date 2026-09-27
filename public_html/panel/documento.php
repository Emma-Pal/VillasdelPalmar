<?php
// Igual que panel/archivo.php, pero para la tabla `documentos` (que no usa
// la tabla `archivos` porque cada documento es un solo archivo, no varios).
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$documento = getDocumentoPorId((int) ($_GET['id'] ?? 0));
$ruta = $documento ? rutaArchivoFisico($documento['archivo']) : null;

if (!$documento || !file_exists($ruta)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$mime = function_exists('mime_content_type') ? (mime_content_type($ruta) ?: 'application/octet-stream') : 'application/octet-stream';
$nombreDescarga = $documento['archivo_nombre_original'];

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: attachment; filename="' . addslashes($nombreDescarga) . '"; filename*=UTF-8\'\'' . rawurlencode($nombreDescarga));
header('Cache-Control: private');
readfile($ruta);
exit;
