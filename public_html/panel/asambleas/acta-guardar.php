<?php
// Procesa el modal "Nueva acta" / "Editar acta" de /panel/asambleas.
// Separado de panel/avisos/formulario.php a propósito — ver el comentario
// en app/repos/publicaciones.php sobre crearAsamblea().
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/asambleas#actas');
    exit;
}

verificarCsrf();

$id = (int) ($_POST['id'] ?? 0);
$esEdicion = $id > 0;
$existente = $esEdicion ? getPublicacionPorId($id) : null;

if ($esEdicion && (!$existente || $existente['categoria'] !== 'acta')) {
    header('Location: /panel/asambleas#actas');
    exit;
}

// "Concepto del documento": un select con las opciones más comunes + "Otro"
// con texto libre, mismo patrón que ya usaba Avisos para categorías libres.
$conceptoPost = trim($_POST['concepto'] ?? '');
$conceptosFijos = ['Acta de asamblea', 'Lista de asistencia y quórum', 'Orden del día firmado'];
if ($conceptoPost === '__otro__') {
    $titulo = trim($_POST['concepto_otro'] ?? '');
} else {
    $titulo = in_array($conceptoPost, $conceptosFijos, true) ? $conceptoPost : '';
}

$tipoAsamblea = in_array($_POST['tipo_asamblea'] ?? '', ['ordinaria', 'extraordinaria'], true) ? $_POST['tipo_asamblea'] : 'ordinaria';
$anioAsamblea = (int) ($_POST['anio_asamblea'] ?? 0);
$fechaEvento = trim($_POST['fecha_evento'] ?? '') ?: null;
$cuerpo = trim($_POST['descripcion'] ?? '');

$error = null;
if ($titulo === '' || $anioAsamblea < 2000) {
    $error = 'Faltan campos obligatorios del acta.';
}

if ($error === null) {
    try {
        $archivosSubidos = procesarArchivosSubidos();
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }

    // El documento anexo (PDF firmado) es obligatorio: al crear, en este
    // envío; al editar, puede venir uno nuevo (reemplaza al anterior) o
    // ninguno (se conserva el que ya tenía).
    if (!$esEdicion && empty($archivosSubidos)) {
        $error = 'Falta adjuntar el documento anexo (PDF firmado).';
    }
}

if ($error !== null) {
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', $error);
    exit;
}

if ($esEdicion) {
    actualizarAsamblea($id, $titulo, $cuerpo, $tipoAsamblea, $fechaEvento, null, null, $anioAsamblea);
    $idDestino = $id;
} else {
    $idDestino = crearAsamblea($usuario['id'], 'acta', $titulo, $cuerpo, $tipoAsamblea, $fechaEvento, null, null, $anioAsamblea);
}

if (!empty($archivosSubidos)) {
    // Reemplaza el PDF anterior: un acta lleva un solo documento anexo, no
    // una lista de adjuntos acumulados como un aviso normal.
    if ($esEdicion) {
        foreach ($existente['archivos'] as $archivoViejo) {
            @unlink(rutaArchivoFisico($archivoViejo['archivo']));
            eliminarArchivo($archivoViejo['id']);
        }
    }
    foreach ($archivosSubidos as $archivo) {
        agregarArchivo($idDestino, $archivo['archivo'], $archivo['archivo_nombre_original']);
    }
}

header('Location: /panel/asambleas#actas');
exit;
