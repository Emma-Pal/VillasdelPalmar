<?php
// Procesa el modal "Nuevo administrador" / "Editar administrador" de
// /panel/usuarios — cuentas tipo='mesa' (privilegios de administrador).
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/usuarios');
    exit;
}

verificarCsrf();

$id = (int) ($_POST['id'] ?? 0);
$esEdicion = $id > 0;
$esUnoMismo = $esEdicion && $id === (int) $usuario['id'];
$existente = $esEdicion ? getUsuarioPorId($id) : null;

if ($esEdicion && (!$existente || $existente['tipo'] !== 'mesa')) {
    header('Location: /panel/usuarios');
    exit;
}

// Dar de alta un administrador nuevo: requiere el permiso "gestionar
// admins" (el superadmin siempre lo tiene; a otras cuentas se les puede
// otorgar, ver más abajo). Editar la cuenta de OTRO administrador (su
// usuario, su contraseña) es exclusivo del superadmin de verdad — eso nunca
// se delega. Editar la propia cuenta siempre está permitido, para eso está
// este mismo formulario además de /panel/mi-cuenta.
if (!$esEdicion) {
    requireGestionarAdmins();
} elseif (!$esUnoMismo) {
    requireSuperadmin();
}

$nombre = trim($_POST['nombre'] ?? '');
$usuarioLogin = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirmar = $_POST['passwordConfirmar'] ?? '';

$error = null;
if ($nombre === '' || $usuarioLogin === '') {
    $error = 'Faltan campos obligatorios.';
} elseif (!$esEdicion || $password !== '') {
    if (!passwordEsValida($password)) {
        $error = 'La contraseña debe tener al menos ' . MIN_LARGO_PASSWORD . ' caracteres.';
    } elseif ($password !== $passwordConfirmar) {
        $error = 'La confirmación no coincide con la contraseña.';
    }
}

if ($error !== null) {
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', $error);
    exit;
}

try {
    if ($esEdicion) {
        $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;
        // "Cargo" ya no aplica a una cuenta de administrador (ver nota en
        // administrador-campos.php) — se guarda null pase lo que pase.
        actualizarUsuario($id, 'mesa', $nombre, null, $usuarioLogin, $passwordHash);

        // Si el administrador se edita a sí mismo, se refresca la sesión
        // para que el header muestre los datos correctos de inmediato —
        // conservando es_superadmin, que este formulario nunca toca.
        if ($esUnoMismo) {
            $_SESSION['usuario'] = ['id' => $id, 'tipo' => 'mesa', 'nombre' => $nombre, 'cargo' => null, 'es_superadmin' => esSuperadmin()];
        } elseif (esSuperadmin()) {
            // Solo el superadmin, y solo editando a OTRO administrador,
            // puede otorgarle o quitarle el permiso de gestionar admins —
            // el checkbox del formulario ni siquiera se le muestra a nadie más.
            if (!empty($_POST['permiso_gestionar_admins'])) {
                otorgarPermiso($id, PERMISO_GESTIONAR_ADMINS, $usuario['id']);
            } else {
                revocarPermiso($id, PERMISO_GESTIONAR_ADMINS);
            }
        }
    } else {
        crearUsuario('mesa', $nombre, null, $usuarioLogin, password_hash($password, PASSWORD_BCRYPT));
    }
} catch (PDOException $e) {
    http_response_code(400);
    $mensaje = stripos($e->getMessage(), 'Duplicate entry') !== false
        ? 'Ese nombre de usuario ya existe. Elige otro.'
        : 'No se pudo guardar la cuenta. Revisa los datos.';
    renderError('No se pudo completar — Villas del Palmar', 'No se pudo guardar.', $mensaje);
    exit;
}

header('Location: /panel/usuarios');
exit;
