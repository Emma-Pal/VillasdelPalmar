<?php
// "Recuperar contraseña" de emergencia: no hay un flujo de autoservicio en
// la página (eso requeriría poder enviar correos, y no hay un servicio de
// correo configurado). Si alguien de la mesa se queda sin poder entrar,
// esto sirve como respaldo: quien tenga acceso por SSH puede resetear
// cualquier contraseña directo en la base de datos.
//
// Uso: php scripts/reset-password.php <usuario> <nueva-contraseña>
// Ejemplo: php scripts/reset-password.php tesorero unaClaveNueva123
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/repos/usuarios.php';

$usuarioLogin = $argv[1] ?? null;
$nuevaPassword = $argv[2] ?? null;

if (!$usuarioLogin || !$nuevaPassword) {
    fwrite(STDERR, "Uso: php scripts/reset-password.php <usuario> <nueva-contraseña>\n");
    exit(1);
}

$cuenta = getUsuarioPorLogin($usuarioLogin);
if (!$cuenta) {
    fwrite(STDERR, "No existe ninguna cuenta con el usuario \"$usuarioLogin\".\n");
    exit(1);
}

actualizarUsuario(
    $cuenta['id'],
    $cuenta['tipo'],
    $cuenta['nombre'],
    $cuenta['cargo'],
    $cuenta['usuario'],
    password_hash($nuevaPassword, PASSWORD_BCRYPT)
);

echo "Contraseña actualizada para \"$usuarioLogin\" ({$cuenta['nombre']}).\n";
