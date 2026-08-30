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
