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

// Iniciales para un avatar circular (primera letra del nombre + primera del
// segundo "token", si existe — ej. "Carlos Aguilar" -> "CA"). Usada por el
// Directorio (lista de colaboradores/comité y la ficha de expediente).
function iniciales(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre));
    $ini = mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1));
    if (!empty($partes[1])) {
        $ini .= mb_strtoupper(mb_substr($partes[1], 0, 1));
    }
    return $ini;
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

// Etiqueta legible de los estatus de una solicitud o de un registro de
// estancia (comparten el mismo componente visual .estatus-pill).
function etiquetaEstatus(string $estatus): string
{
    $etiquetas = [
        'pendiente' => 'Pendiente',
        'en_progreso' => 'En progreso',
        'resuelto' => 'Resuelto',
        'en_revision' => 'En revisión',
        'confirmado' => 'Confirmado',
        'concluido' => 'Concluida',
    ];
    return $etiquetas[$estatus] ?? $estatus;
}

// Capacidad de ocupación de una villa según sus recámaras registradas —
// tabla fija que dio Emmanuel (estudio = 3, y sube de ahí). Más allá de la
// tabla (4+ recámaras) se extiende el mismo incremento de +3 por recámara
// que ya trae de 2 a 3. Usada tanto para precargar el campo oculto en
// villa-campos.php como, en espejo, por el mismo cálculo en main.js.
function capacidadPorRecamaras(int $recamaras): int
{
    $tabla = [0 => 3, 1 => 4, 2 => 7, 3 => 10];
    if (isset($tabla[$recamaras])) return $tabla[$recamaras];
    if ($recamaras > 3) return 10 + ($recamaras - 3) * 3;
    return 3;
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
