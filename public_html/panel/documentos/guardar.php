<?php
// Procesa el modal "Nuevo documento" / "Editar documento" de
// /panel/documentos (Guía del propietario).
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/documentos');
    exit;
}

verificarCsrf();

$id = (int) ($_POST['id'] ?? 0);
$esEdicion = $id > 0;
$existente = $esEdicion ? getDocumentoPorId($id) : null;

if ($esEdicion && !$existente) {
    header('Location: /panel/documentos');
    exit;
}

$categoria = in_array($_POST['categoria'] ?? '', CATEGORIAS_DOCUMENTO, true) ? $_POST['categoria'] : 'reglamento';
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$fechaDocumento = trim($_POST['fecha_documento'] ?? '');
$vigenteDesde = trim($_POST['vigente_desde'] ?? '') ?: null;
$reemplaza = !$esEdicion && isset($_POST['reemplaza']);

$error = null;
if ($titulo === '' || $fechaDocumento === '') {
    $error = 'Faltan campos obligatorios del documento.';
}

if ($error === null) {
    try {
        $archivosSubidos = procesarArchivosSubidos();
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }

    if (!$esEdicion && empty($archivosSubidos)) {
        $error = 'Falta adjuntar el archivo (PDF) del documento.';
    }
}

if ($error !== null) {
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', $error);
    exit;
}

if ($esEdicion) {
    if (!empty($archivosSubidos)) {
        actualizarDocumento(
            $id,
            $categoria,
            $titulo,
            $descripcion,
            $fechaDocumento,
            $vigenteDesde,
            $archivosSubidos[0]['archivo'],
            $archivosSubidos[0]['archivo_nombre_original']
        );
        @unlink(rutaArchivoFisico($existente['archivo']));
    } else {
        actualizarDocumento($id, $categoria, $titulo, $descripcion, $fechaDocumento, $vigenteDesde);
    }
} else {
    if ($reemplaza) {
        archivarDocumentosConMismoTitulo($titulo);
    }
    crearDocumento(
        $usuario['id'],
        $categoria,
        $titulo,
        $descripcion,
        $fechaDocumento,
        $vigenteDesde,
        $archivosSubidos[0]['archivo'],
        $archivosSubidos[0]['archivo_nombre_original']
    );
}

header('Location: /panel/documentos');
exit;
