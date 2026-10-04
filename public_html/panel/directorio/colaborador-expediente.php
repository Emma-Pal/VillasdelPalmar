<?php
// "Descargar expediente": junta los documentos del colaborador en un solo
// ZIP. Mismo nivel de acceso que panel/colaborador-archivo.php — requireMesa(),
// nunca requireAuth().
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

$col = getColaboradorPorId((int) ($_GET['id'] ?? 0));
if (!$col) {
    header('Location: /panel/directorio');
    exit;
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    renderError('No disponible — Villas del Palmar', 'Función no disponible.', 'El servidor no tiene la extensión ZIP de PHP habilitada; descarga los documentos uno por uno desde la ficha.');
    exit;
}

$documentos = [
    'ine_archivo' => 'INE',
    'curp_archivo' => 'CURP',
    'rfc_archivo' => 'Constancia_RFC',
    'nss_archivo' => 'NSS_IMSS',
    'domicilio_archivo' => 'Comprobante_domicilio',
    'contrato_archivo' => 'Contrato_laboral',
];

$zipTmp = tempnam(sys_get_temp_dir(), 'expediente_');
$zip = new ZipArchive();
$zip->open($zipTmp, ZipArchive::OVERWRITE);

$agregados = 0;
foreach ($documentos as $campo => $prefijo) {
    if (empty($col[$campo])) continue;
    $ruta = rutaArchivoFisico($col[$campo]);
    if (!file_exists($ruta)) continue;

    $extension = pathinfo($col[$campo . '_nombre_original'] ?? $col[$campo], PATHINFO_EXTENSION);
    $zip->addFile($ruta, $prefijo . ($extension ? '.' . $extension : ''));
    $agregados++;
}
$zip->close();

if ($agregados === 0) {
    @unlink($zipTmp);
    http_response_code(404);
    renderError('No encontrado — Villas del Palmar', 'Sin documentos.', 'Este colaborador todavía no tiene documentos cargados.');
    exit;
}

$nombreZip = 'Expediente_' . preg_replace('/[^A-Za-z0-9]+/', '_', $col['nombre']) . '.zip';

header('Content-Type: application/zip');
header('Content-Length: ' . filesize($zipTmp));
header('Content-Disposition: attachment; filename="' . $nombreZip . '"');
header('Cache-Control: private, no-store');
readfile($zipTmp);
@unlink($zipTmp);
exit;
