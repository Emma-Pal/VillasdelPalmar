<?php
// Procesa el modal "Nuevo integrante" / "Editar integrante" de
// /panel/directorio (Integrantes del Comité).
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/directorio#comite');
    exit;
}

verificarCsrf();

$id = (int) ($_POST['id'] ?? 0);
$esEdicion = $id > 0;

if ($esEdicion && !getComiteMiembroPorId($id)) {
    header('Location: /panel/directorio#comite');
    exit;
}

$departamento = in_array($_POST['departamento'] ?? '', DEPARTAMENTOS_COMITE, true) ? $_POST['departamento'] : 'comite_administracion';
$titularSuplente = ($_POST['titular_suplente'] ?? '') === 'suplente' ? 'suplente' : 'titular';
$nombre = trim($_POST['nombre'] ?? '');
$villa = trim($_POST['villa'] ?? '');
$cargo = trim($_POST['cargo'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$periodoInicio = trim($_POST['periodo_inicio'] ?? '') ?: null;
$periodoFin = trim($_POST['periodo_fin'] ?? '') ?: null;

if ($nombre === '' || $villa === '' || $cargo === '' || $correo === '' || $telefono === '' || !ctype_digit($villa)) {
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', 'Faltan campos obligatorios, o el número de villa no es válido.');
    exit;
}

if ($esEdicion) {
    actualizarComiteMiembro($id, $departamento, $titularSuplente, $nombre, $villa, $cargo, $correo, $telefono, $periodoInicio, $periodoFin);
} else {
    // Presente solo cuando se creó desde el botón "+ Comité" de un titular
    // en /panel/usuarios (ver villa-campos / index.php) — vincula esta
    // fila con su titular de origen.
    $titularId = (int) ($_POST['titular_id'] ?? 0) ?: null;
    crearComiteMiembro($usuario['id'], $departamento, $titularSuplente, $nombre, $villa, $cargo, $correo, $telefono, $periodoInicio, $periodoFin, $titularId);
}

header('Location: /panel/directorio#comite');
exit;
