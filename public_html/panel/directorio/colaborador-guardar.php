<?php
// Procesa el modal "Nuevo colaborador" / "Editar colaborador" de
// /panel/directorio (Personal del condominio).
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/directorio');
    exit;
}

verificarCsrf();

$id = (int) ($_POST['id'] ?? 0);
$esEdicion = $id > 0;
$existente = $esEdicion ? getColaboradorPorId($id) : null;

if ($esEdicion && !$existente) {
    header('Location: /panel/directorio');
    exit;
}

$campos = [
    'nombre' => trim($_POST['nombre'] ?? ''),
    'puesto' => trim($_POST['puesto'] ?? ''),
    'area' => in_array($_POST['area'] ?? '', AREAS_COLABORADOR, true) ? $_POST['area'] : 'administracion',
    'fecha_ingreso' => trim($_POST['fecha_ingreso'] ?? ''),
    'estatus' => ($_POST['estatus'] ?? '') === 'baja' ? 'baja' : 'activo',
    'curp_numero' => strtoupper(trim($_POST['curp_numero'] ?? '')),
    'rfc_numero' => strtoupper(trim($_POST['rfc_numero'] ?? '')),
    'nss_numero' => trim($_POST['nss_numero'] ?? ''),
    'fecha_nacimiento' => trim($_POST['fecha_nacimiento'] ?? '') ?: null,
    'lugar_nacimiento' => trim($_POST['lugar_nacimiento'] ?? '') ?: null,
    'nacionalidad' => trim($_POST['nacionalidad'] ?? '') ?: null,
    'estado_civil' => trim($_POST['estado_civil'] ?? '') ?: null,
    'telefono' => trim($_POST['telefono'] ?? '') ?: null,
    'correo' => trim($_POST['correo'] ?? '') ?: null,
    'domicilio' => trim($_POST['domicilio'] ?? '') ?: null,
    'tipo_contrato' => trim($_POST['tipo_contrato'] ?? '') ?: null,
    'turno_horario' => trim($_POST['turno_horario'] ?? '') ?: null,
    'emergencia_nombre' => trim($_POST['emergencia_nombre'] ?? '') ?: null,
    'emergencia_parentesco' => trim($_POST['emergencia_parentesco'] ?? '') ?: null,
    'emergencia_telefono1' => trim($_POST['emergencia_telefono1'] ?? '') ?: null,
    'emergencia_telefono2' => trim($_POST['emergencia_telefono2'] ?? '') ?: null,
    'emergencia_domicilio' => trim($_POST['emergencia_domicilio'] ?? '') ?: null,
];

$error = null;
if ($campos['nombre'] === '' || $campos['puesto'] === '' || $campos['fecha_ingreso'] === ''
    || $campos['curp_numero'] === '' || $campos['rfc_numero'] === '' || $campos['nss_numero'] === '') {
    $error = 'Faltan campos obligatorios del colaborador.';
}

// Documentos: foto + los 5 archivos con nombre de campo propio (no
// "archivos[]"). Los 5 identitarios son obligatorios al CREAR; si no se
// sube uno nuevo al EDITAR, se conserva el que ya había.
$camposArchivo = ['foto' => false, 'ine_archivo' => true, 'curp_archivo' => true, 'rfc_archivo' => true, 'nss_archivo' => true, 'domicilio_archivo' => false, 'contrato_archivo' => true];
$subidos = [];

if ($error === null) {
    try {
        foreach ($camposArchivo as $campo => $obligatorio) {
            $subidos[$campo] = procesarArchivoSubido($campo);
        }
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }

    foreach ($camposArchivo as $campo => $obligatorio) {
        if (!$obligatorio) continue;
        $yaExistia = $esEdicion && !empty($existente[$campo]);
        if (!$subidos[$campo] && !$yaExistia) {
            $error = 'Falta adjuntar un documento obligatorio del expediente.';
            break;
        }
    }
}

if ($error !== null) {
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', $error);
    exit;
}

// Para cada campo de archivo: si se subió uno nuevo, usa ese; si no, y se
// está editando, conserva el que ya había (su nombre guardado Y su nombre
// original van en columnas separadas: {campo}_archivo / {campo}_archivo_
// nombre_original — "foto" es la única excepción, un solo campo sin
// "_nombre_original" porque no se descarga con nombre, solo se muestra).
$archivosViejosABorrar = [];
foreach ($camposArchivo as $campo => $obligatorio) {
    if ($subidos[$campo]) {
        if ($esEdicion && !empty($existente[$campo])) {
            $archivosViejosABorrar[] = $existente[$campo];
        }
        $campos[$campo] = $subidos[$campo]['archivo'];
        if ($campo !== 'foto') {
            $campos[$campo . '_nombre_original'] = $subidos[$campo]['archivo_nombre_original'];
        }
    } elseif ($esEdicion) {
        $campos[$campo] = $existente[$campo] ?? null;
        if ($campo !== 'foto') {
            $campos[$campo . '_nombre_original'] = $existente[$campo . '_nombre_original'] ?? null;
        }
    }
}

if ($esEdicion) {
    actualizarColaborador($id, $campos);
} else {
    crearColaborador($usuario['id'], $campos);
}

foreach ($archivosViejosABorrar as $archivoViejo) {
    @unlink(rutaArchivoFisico($archivoViejo));
}

header('Location: /panel/directorio');
exit;
