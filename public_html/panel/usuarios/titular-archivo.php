<?php
// Sirve el INE (frente/reverso) de un titular de villa. Igual que
// panel/colaborador-archivo.php: requireMesa() y NO requireAuth() — el INE
// es un dato personal sensible que ningún propietario debe poder ver, ni
// siquiera el de su propia villa, adivinando la URL.
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

const CAMPOS_ARCHIVO_TITULAR = ['ine_frente', 'ine_reverso'];

$id = (int) ($_GET['id'] ?? 0);
$campo = $_GET['campo'] ?? '';

if (!in_array($campo, CAMPOS_ARCHIVO_TITULAR, true)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$columnaArchivo = $campo . '_archivo';

$titular = getTitularPorId($id);
$nombreArchivo = $titular[$columnaArchivo] ?? null;
$ruta = $nombreArchivo ? rutaArchivoFisico($nombreArchivo) : null;

if (!$titular || !$nombreArchivo || !file_exists($ruta)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$mime = function_exists('mime_content_type') ? (mime_content_type($ruta) ?: 'application/octet-stream') : 'application/octet-stream';
$nombreDescarga = $titular[$columnaArchivo . '_nombre_original'];
$disposicion = esImagen($nombreDescarga) ? 'attachment' : 'inline';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: ' . $disposicion . '; filename="' . addslashes($nombreDescarga) . '"; filename*=UTF-8\'\'' . rawurlencode($nombreDescarga));
header('Cache-Control: private, no-store');
readfile($ruta);
exit;
