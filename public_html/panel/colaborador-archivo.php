<?php
// Sirve los documentos del expediente de un colaborador (INE, CURP, RFC,
// NSS, comprobante de domicilio, contrato laboral) y su foto. Los
// documentos de identidad son datos personales sensibles (LFPDPPP) que
// ningún propietario debe poder ver, ni adivinando la URL — por eso exigen
// requireMesa(). La foto es la única excepción: es solo un retrato, se
// muestra en el listado público de /panel/directorio, así que ahí basta
// con requireAuth() (cualquier cuenta logueada, propietario o Administración).
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$campoSolicitado = $_GET['campo'] ?? '';
if ($campoSolicitado !== 'foto') {
    requireMesa();
}

// Cada "campo" mapea a una columna de la tabla colaboradores — lista fija
// (no se arma con el valor de la URL) para no poder apuntar a cualquier
// columna de la tabla. "foto" es la excepción de nombre: su columna es
// "foto" a secas (no "foto_archivo"), y no tiene nombre original guardado
// (es solo para mostrarse como avatar, nunca se "descarga con nombre").
const CAMPOS_ARCHIVO_COLABORADOR = ['foto', 'ine', 'curp', 'rfc', 'nss', 'domicilio', 'contrato'];

$id = (int) ($_GET['id'] ?? 0);
$campo = $_GET['campo'] ?? '';

if (!in_array($campo, CAMPOS_ARCHIVO_COLABORADOR, true)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$columnaArchivo = $campo === 'foto' ? 'foto' : $campo . '_archivo';

$colaborador = getColaboradorPorId($id);
$nombreArchivo = $colaborador[$columnaArchivo] ?? null;
$ruta = $nombreArchivo ? rutaArchivoFisico($nombreArchivo) : null;

if (!$colaborador || !$nombreArchivo || !file_exists($ruta)) {
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Documento no encontrado.', 'No encontramos ese documento.');
    exit;
}

$mime = function_exists('mime_content_type') ? (mime_content_type($ruta) ?: 'application/octet-stream') : 'application/octet-stream';
$nombreDescarga = $campo === 'foto' ? $nombreArchivo : $colaborador[$columnaArchivo . '_nombre_original'];

// Una imagen se ve en el visor flotante (<img src="...">, que ignora este
// header de cualquier forma) y ahí mismo se puede "Descargar imagen" — para
// que ese botón sí fuerce la descarga hace falta "attachment" (mismo
// comportamiento que panel/archivo.php, que ya usa el mismo visor en
// Avisos). Un PDF, en cambio, se abre en pestaña nueva para VERSE ahí
// — por eso se queda "inline".
$disposicion = esImagen($nombreDescarga) ? 'attachment' : 'inline';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($ruta));
header('Content-Disposition: ' . $disposicion . '; filename="' . addslashes($nombreDescarga) . '"; filename*=UTF-8\'\'' . rawurlencode($nombreDescarga));
header('Cache-Control: private, no-store');
readfile($ruta);
exit;
