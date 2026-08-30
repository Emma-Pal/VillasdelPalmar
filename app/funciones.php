<?php
// Convierte texto plano (lo que se escribe en un <textarea>) a HTML seguro:
// escapa cualquier etiqueta y respeta los saltos de línea como <br>. PHP ya
// trae nl2br() nativo — solo se envuelve con htmlspecialchars() para que
// siga siendo seguro (equivalente a app.locals.nl2br en la versión Node).
function nl2brSeguro(string $texto): string
{
    return nl2br(htmlspecialchars($texto));
}

// Para decidir si un archivo adjunto se muestra como vista previa de imagen
// o como link de descarga (ej. un PDF).
function esImagen(string $nombreArchivo): bool
{
    return (bool) preg_match('/\.(jpe?g|png)$/i', $nombreArchivo);
}

// Etiqueta legible de cada categoría de publicación.
function etiquetaCategoria(string $categoria): string
{
    $etiquetas = ['financiero' => 'Estado financiero', 'mejora' => 'Mejora', 'aviso' => 'Aviso'];
    return $etiquetas[$categoria] ?? $categoria;
}

// Usadas por partials/portal-header.php para marcar la sección activa del
// nav. strpos() en vez de str_starts_with() para no depender de PHP 8.
function rutaActivaExacta(string $ruta, string $currentPath): string
{
    return $currentPath === $ruta ? 'is-active' : '';
}

function rutaActivaPrefijo(string $prefijo, string $currentPath): string
{
    return strpos($currentPath, $prefijo) === 0 ? 'is-active' : '';
}

// Equivalente a res.render('error', {...}): imprime la página de error
// reutilizando el layout normal. IMPORTANTE: quien la llame debe hacer
// `exit;` justo después (esta función no corta la ejecución por sí sola).
function renderError(string $title, string $description, string $mensaje): void
{
    global $usuario, $csrfToken, $avisosNuevos, $currentPath;
    include __DIR__ . '/../public_html/partials/pagina-error.php';
}
