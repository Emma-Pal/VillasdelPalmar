<?php
// Sirve un archivo adjunto solo si hay sesión — por eso no vive dentro de
// /public_html accesible directo, y por eso este script existe.
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$archivo = getArchivoPorId((int) ($_GET['id'] ?? 0));
$ruta = $archivo ? rutaArchivoFisico($archivo['archivo']) : null;

if (!$archivo || !file_exists($ruta)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Archivo no encontrado.', 'No encontramos ese archivo.');
    exit;
}

$mime = function_exists('mime_content_type') ? (mime_content_type($ruta) ?: 'application/octet-stream') : 'application/octet-stream';
$nombreDescarga = $archivo['archivo_nombre_original'];

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: attachment; filename="' . addslashes($nombreDescarga) . '"; filename*=UTF-8\'\'' . rawurlencode($nombreDescarga));
header('Cache-Control: private');
readfile($ruta);
exit;
