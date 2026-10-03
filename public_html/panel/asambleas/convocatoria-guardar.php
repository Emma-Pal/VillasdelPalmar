<?php
// Procesa el modal "Nueva convocatoria" / "Editar convocatoria" de
// /panel/asambleas. Separado de panel/avisos/formulario.php a propósito —
// ver el comentario en app/repos/publicaciones.php sobre crearAsamblea().
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/asambleas#convocatorias');
    exit;
}

verificarCsrf();

$id = (int) ($_POST['id'] ?? 0);
$esEdicion = $id > 0;
$existente = $esEdicion ? getPublicacionPorId($id) : null;

if ($esEdicion && (!$existente || $existente['categoria'] !== 'convocatoria')) {
    header('Location: /panel/asambleas#convocatorias');
    exit;
}

$tipoAsamblea = in_array($_POST['tipo_asamblea'] ?? '', ['ordinaria', 'extraordinaria'], true) ? $_POST['tipo_asamblea'] : 'ordinaria';
$titulo = trim($_POST['titulo'] ?? '');
$fechaEvento = trim($_POST['fecha_evento'] ?? '');
$horaEvento = trim($_POST['hora_evento'] ?? '');
$lugarEvento = trim($_POST['lugar_evento'] ?? '');
$cuerpo = trim($_POST['cuerpo'] ?? '');

$error = null;
if ($titulo === '' || $fechaEvento === '' || $horaEvento === '' || $lugarEvento === '') {
    $error = 'Faltan campos obligatorios de la convocatoria.';
}

if ($error === null) {
    try {
        $archivosSubidos = procesarArchivosSubidos();
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }

    // El documento de convocatoria es obligatorio: al crear, en este envío;
    // al editar, puede venir uno nuevo (reemplaza al anterior) o ninguno
    // (se conserva el que ya tenía).
    if (!$esEdicion && empty($archivosSubidos)) {
        $error = 'Falta adjuntar el documento de convocatoria (PDF).';
    }
}

if ($error !== null) {
    // No hay una pantalla de error amigable a la que volver con los datos
    // ya escritos (el formulario vive en un modal de /panel/asambleas) —
    // se manda un mensaje de error simple, consistente con el resto del
    // sitio para fallos de validación inesperados.
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', $error);
    exit;
}

if ($esEdicion) {
    actualizarAsamblea($id, $titulo, $cuerpo, $tipoAsamblea, $fechaEvento, $horaEvento, $lugarEvento);
    $idDestino = $id;
} else {
    $idDestino = crearAsamblea($usuario['id'], 'convocatoria', $titulo, $cuerpo, $tipoAsamblea, $fechaEvento, $horaEvento, $lugarEvento);
}

if (!empty($archivosSubidos)) {
    // Reemplaza el PDF anterior: una convocatoria lleva un solo documento
    // oficial, no una lista de adjuntos acumulados como un aviso normal.
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

header('Location: /panel/asambleas#convocatorias');
exit;
