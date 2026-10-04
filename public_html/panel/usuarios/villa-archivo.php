<?php
// Sirve los documentos del inmueble (escritura, relación de copropietarios)
// de una villa. Igual que panel/colaborador-archivo.php: requireMesa() y NO
// requireAuth() — nadie entra aquí adivinando la URL salvo Administración.
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

const CAMPOS_ARCHIVO_VILLA = ['escritura', 'relacion_copropietarios'];

$id = (int) ($_GET['id'] ?? 0);
$campo = $_GET['campo'] ?? '';

if (!in_array($campo, CAMPOS_ARCHIVO_VILLA, true)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$columnaArchivo = $campo . '_archivo';

$villa = getVillaPorId($id);
$nombreArchivo = $villa[$columnaArchivo] ?? null;
$ruta = $nombreArchivo ? rutaArchivoFisico($nombreArchivo) : null;

if (!$villa || !$nombreArchivo || !file_exists($ruta)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$mime = function_exists('mime_content_type') ? (mime_content_type($ruta) ?: 'application/octet-stream') : 'application/octet-stream';
$nombreDescarga = $villa[$columnaArchivo . '_nombre_original'];
$disposicion = esImagen($nombreDescarga) ? 'attachment' : 'inline';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: ' . $disposicion . '; filename="' . addslashes($nombreDescarga) . '"; filename*=UTF-8\'\'' . rawurlencode($nombreDescarga));
header('Cache-Control: private, no-store');
readfile($ruta);
exit;
