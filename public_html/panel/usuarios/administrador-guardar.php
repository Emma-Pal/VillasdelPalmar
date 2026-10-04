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
$existente = $esEdicion ? getUsuarioPorId($id) : null;

if ($esEdicion && (!$existente || $existente['tipo'] !== 'mesa')) {
    header('Location: /panel/usuarios');
    exit;
}

$nombre = trim($_POST['nombre'] ?? '');
$cargo = trim($_POST['cargo'] ?? '');
$usuarioLogin = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirmar = $_POST['passwordConfirmar'] ?? '';

$error = null;
if ($nombre === '' || $cargo === '' || $usuarioLogin === '') {
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
        actualizarUsuario($id, 'mesa', $nombre, $cargo, $usuarioLogin, $passwordHash);

        // Si el administrador se edita a sí mismo, se refresca la sesión
        // para que el header muestre los datos correctos de inmediato.
        if ((int) $_SESSION['usuario']['id'] === $id) {
            $_SESSION['usuario'] = ['id' => $id, 'tipo' => 'mesa', 'nombre' => $nombre, 'cargo' => $cargo];
        }
    } else {
        crearUsuario('mesa', $nombre, $cargo, $usuarioLogin, password_hash($password, PASSWORD_BCRYPT));
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
