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

// Etiqueta legible de la prioridad de un aviso. Son solo 3 valores fijos
// (columna ENUM), así que no hace falta un "slug" como el de categoría.
function etiquetaPrioridad(string $prioridad): string
{
    $etiquetas = [
        'urgente' => 'Urgente',
        'importante' => 'Importante',
        'informativo' => 'Informativo',
    ];
    return $etiquetas[$prioridad] ?? $prioridad;
}

// Etiqueta legible del tipo de una convocatoria/acta.
function etiquetaTipoAsamblea(?string $tipo): string
{
    return $tipo === 'extraordinaria' ? 'Extraordinaria' : 'Ordinaria';
}

// Mes abreviado en mayúsculas (ENE, FEB, ...) para el "bloque de fecha" tipo
// hoja de calendario — usado en Asambleas y en Guía del propietario.
function mesAbreviado(int $numeroMes): string
{
    $meses = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    return $meses[$numeroMes] ?? '';
}

// Etiqueta legible de los estatus de una solicitud.
function etiquetaEstatus(string $estatus): string
{
    $etiquetas = [
        'pendiente' => 'Pendiente',
        'en_progreso' => 'En progreso',
        'resuelto' => 'Resuelto',
    ];
    return $etiquetas[$estatus] ?? $estatus;
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
