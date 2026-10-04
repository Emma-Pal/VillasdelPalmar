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
    // Caso especial: un formulario con varios archivos (ej. "Nuevo
    // colaborador" en Directorio, con hasta 7) puede sumar más que el
    // post_max_size del servidor — ahí PHP vacía $_POST por completo
    // (incluido este mismo campo _csrf), lo cual sin este chequeo se vería
    // igual a una sesión vencida aunque el formulario esté perfectamente
    // bien llenado. Content-Length sí sigue reflejando el tamaño real
    // enviado, así que comparándolo contra el límite se distingue un caso
    // del otro y se avisa lo que de verdad pasó.
    if (empty($_POST) && empty($_FILES) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $limiteBytes = convertirAtBytes(ini_get('post_max_size'));
        $enviadoBytes = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($limiteBytes > 0 && $enviadoBytes > $limiteBytes) {
            http_response_code(413);
            renderError(
                'Solicitud rechazada — Villas del Palmar',
                'Los archivos son demasiado pesados en conjunto.',
                'El formulario no se pudo enviar porque, entre todos los archivos adjuntos, pesan más de lo que el servidor acepta de una sola vez. Intenta con fotos/documentos más ligeros, o avisa al desarrollador para subir ese límite.'
            );
            exit;
        }
    }

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

// "8M", "1G", "512K" -> bytes. ini_get() de post_max_size/upload_max_filesize
// viene en ese formato "shorthand" de PHP, no en bytes directo.
function convertirAtBytes(string $valor): int
{
    $valor = trim($valor);
    if ($valor === '') {
        return 0;
    }
    $unidad = strtoupper(substr($valor, -1));
    $numero = (int) $valor;
    switch ($unidad) {
        case 'G': return $numero * 1024 * 1024 * 1024;
        case 'M': return $numero * 1024 * 1024;
        case 'K': return $numero * 1024;
        default: return (int) $valor;
    }
}
