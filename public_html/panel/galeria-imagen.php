<?php
// Igual que panel/documento.php, pero para la tabla `galeria_items`. Se usa
// como src de <img> en las páginas de detalle de Galería — el navegador
// muestra la imagen igual aunque la respuesta lleve Content-Disposition
// (eso solo importa si alguien la abre directo, no al insertarla en <img>).
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$item = getGaleriaItemPorId((int) ($_GET['id'] ?? 0));
$ruta = $item ? rutaArchivoFisico($item['archivo']) : null;

if (!$item || !file_exists($ruta)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Imagen no encontrada.', 'No encontramos esa imagen.');
    exit;
}

$mime = function_exists('mime_content_type') ? (mime_content_type($ruta) ?: 'application/octet-stream') : 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: inline; filename="' . addslashes($item['archivo_nombre_original']) . '"');
header('Cache-Control: private, max-age=86400');
readfile($ruta);
exit;
