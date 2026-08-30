<?php
// Protección CSRF (patrón "token sincronizador"), traducción directa de
// middleware/csrf.js: un token por sesión, viaja en un campo oculto _csrf
// en cada formulario, y se verifica en cada POST que coincida con el de sesión.

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['csrf_token'];
}

// Corta la ejecución con una página de error si el token no coincide
// (formulario armado en otro sitio, o sesión/formulario vencidos).
function verificarCsrf(): void
{
    $tokenEnviado = $_POST['_csrf'] ?? null;
    if (!$tokenEnviado || !hash_equals($_SESSION['csrf_token'] ?? '', $tokenEnviado)) {
        http_response_code(403);
        renderError(
            'Solicitud rechazada — Villas del Palmar',
            'Token de seguridad inválido.',
            'Tu sesión o el formulario expiraron. Regresa e inténtalo de nuevo.'
        );
        exit;
    }
}
