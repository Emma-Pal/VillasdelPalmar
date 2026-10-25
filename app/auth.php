<?php
// Guardas de acceso al portal. $_SESSION['usuario'] guarda el usuario
// completo (sin password_hash) tras un login exitoso — equivalente a
// req.session.user en la versión Node.

function requireAuth(): void
{
    if (empty($_SESSION['usuario'])) {
        header('Location: /login');
        exit;
    }
}

function requireMesa(): void
{
    if (empty($_SESSION['usuario'])) {
        header('Location: /login');
        exit;
    }
    if ($_SESSION['usuario']['tipo'] !== 'mesa') {
        http_response_code(403);
        renderError(
            'Acceso no permitido — Villas del Palmar',
            'Esta sección es solo para la mesa directiva.',
            'Esta sección es solo para la mesa directiva.'
        );
        exit;
    }
}

// El superadmin es una cuenta de mesa normal (tipo='mesa', conserva TODO lo
// que ya puede hacer un administrador) con un extra: solo ella puede dar de
// alta o eliminar OTRAS cuentas de administrador. Por diseño, nunca hay un
// formulario que active esta bandera — se asigna una sola vez por SQL
// directo (ver sql/schema.sql), así que no hace falta "proteger" que exista
// más de un superadmin: sencillamente no hay manera de crear uno segundo
// desde la aplicación.
function esSuperadmin(): bool
{
    return !empty($_SESSION['usuario']['es_superadmin']);
}

// Para las acciones que son EXCLUSIVAS del superadmin de verdad (editar la
// cuenta de OTRO administrador — su usuario, su contraseña). Esto nunca se
// puede otorgar como permiso: ver dar de alta/eliminar abajo, que sí.
// Requiere que ya haya pasado requireMesa().
function requireSuperadmin(): void
{
    if (!esSuperadmin()) {
        http_response_code(403);
        renderError(
            'Acción no permitida — Villas del Palmar',
            'Esta acción es solo para el superadministrador.',
            'Solo el superadministrador puede editar la cuenta de otro administrador.'
        );
        exit;
    }
}

// Dar de alta o eliminar cuentas de administrador: del superadmin por
// default, pero el superadmin puede otorgarle este permiso a cuentas
// específicas (ver PERMISO_GESTIONAR_ADMINS en app/repos/permisos.php). Se
// consulta la base de datos directo (no la sesión) para que, si el
// superadmin le quita el permiso a alguien, se note de inmediato, sin
// esperar a que esa cuenta cierre sesión.
function puedeGestionarAdmins(): bool
{
    if (esSuperadmin()) return true;
    $id = $_SESSION['usuario']['id'] ?? null;
    return $id ? tienePermiso($id, PERMISO_GESTIONAR_ADMINS) : false;
}

function requireGestionarAdmins(): void
{
    if (!puedeGestionarAdmins()) {
        http_response_code(403);
        renderError(
            'Acción no permitida — Villas del Palmar',
            'No tienes permiso para gestionar administradores.',
            'Dar de alta o eliminar cuentas de administrador requiere el permiso correspondiente — pídeselo al superadministrador.'
        );
        exit;
    }
}
