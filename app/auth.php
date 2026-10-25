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

// Para las pocas acciones que son EXCLUSIVAS del superadmin (dar de alta o
// eliminar una cuenta de administrador). Requiere que ya haya pasado
// requireMesa() — no vuelve a checar sesión por separado.
function requireSuperadmin(): void
{
    if (!esSuperadmin()) {
        http_response_code(403);
        renderError(
            'Acción no permitida — Villas del Palmar',
            'Esta acción es solo para el superadministrador.',
            'Solo el superadministrador puede dar de alta o eliminar cuentas de administrador.'
        );
        exit;
    }
}
