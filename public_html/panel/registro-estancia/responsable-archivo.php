<?php
// Sirve la identificación del responsable de un registro de estancia.
// A diferencia de panel/colaborador-archivo.php (solo Administración), aquí
// SÍ puede verlo la villa dueña del registro — es su propio documento, lo
// subió ella misma — además de Administración. requireAuth() + verificación
// de dueño, nunca requireMesa() a secas.
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$estancia = getEstanciaPorId($id);

if (!$estancia || (!($usuario['tipo'] === 'mesa') && (int) $estancia['usuario_id'] !== (int) $usuario['id'])) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$nombreArchivo = $estancia['responsable_id_archivo'];
$ruta = rutaArchivoFisico($nombreArchivo);

if (!file_exists($ruta)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$mime = function_exists('mime_content_type') ? (mime_content_type($ruta) ?: 'application/octet-stream') : 'application/octet-stream';
$nombreDescarga = $estancia['responsable_id_archivo_nombre_original'];
$disposicion = esImagen($nombreDescarga) ? 'attachment' : 'inline';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: ' . $disposicion . '; filename="' . addslashes($nombreDescarga) . '"; filename*=UTF-8\'\'' . rawurlencode($nombreDescarga));
header('Cache-Control: private, no-store');
readfile($ruta);
exit;
