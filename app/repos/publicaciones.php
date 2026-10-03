<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/archivos.php';

// "Convocatoria" y "acta" también usan la tabla publicaciones (misma
// mecánica de título/cuerpo/archivos/edición), pero viven en /panel/asambleas
// en vez de /panel/avisos — por eso se excluyen del feed general de avisos y
// de su contador de "nuevos".
const CATEGORIAS_ASAMBLEA = ['convocatoria', 'acta'];

// Desde el rediseño de oct. 2026, /panel/avisos ya no filtra por categoría
// (el formulario de captura dejó de preguntarla — toda publicación nueva es
// categoría 'aviso' fija). $limit/$offset se bindean como enteros a
// propósito: con PDO::ATTR_EMULATE_PREPARES=false, MySQL rechaza LIMIT/OFFSET
// si se bindean como texto (error típico "Incorrect arguments to
// mysqld_stmt_execute").
// $esMesa: false para propietarios (solo ven publicado=1 y audiencia='todos'),
// true para la mesa (ve también sus borradores y lo dirigido solo al
// comité, para poder administrarlo todo desde la misma tabla).
function getPublicaciones(int $limit = 10, int $offset = 0, bool $esMesa = false): array
{
    $filtroVisibilidad = $esMesa ? '' : " AND p.publicado = 1 AND p.audiencia = 'todos'";
    $stmt = db()->prepare(
        "SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
         FROM publicaciones p
         JOIN usuarios u ON u.id = p.autor_id
         WHERE p.categoria NOT IN ('" . implode("','", CATEGORIAS_ASAMBLEA) . "')$filtroVisibilidad
         ORDER BY p.destacado DESC, p.fecha DESC, p.id DESC LIMIT :limit OFFSET :offset"
    );
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $filas = $stmt->fetchAll();

    foreach ($filas as &$fila) {
        $fila['archivos'] = getArchivosDePublicacion($fila['id']);
    }
    unset($fila);

    return $filas;
}

function contarPublicaciones(bool $esMesa = false): int
{
    $filtroVisibilidad = $esMesa ? '' : " AND publicado = 1 AND audiencia = 'todos'";
    $stmt = db()->query(
        "SELECT COUNT(*) FROM publicaciones WHERE categoria NOT IN ('" . implode("','", CATEGORIAS_ASAMBLEA) . "')$filtroVisibilidad"
    );
    return (int) $stmt->fetchColumn();
}

// Publicaciones de /panel/asambleas (convocatorias y actas) — mismo patrón
// que getPublicaciones(), pero acotado a CATEGORIAS_ASAMBLEA. Sin paginación
// por ahora: se espera un volumen bajo (unas pocas asambleas al año).
function getPublicacionesAsamblea(?string $categoria = null, int $limit = 50): array
{
    $categoriasValidas = $categoria ? [$categoria] : CATEGORIAS_ASAMBLEA;
    $placeholders = implode(',', array_fill(0, count($categoriasValidas), '?'));

    $stmt = db()->prepare(
        "SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
         FROM publicaciones p JOIN usuarios u ON u.id = p.autor_id
         WHERE p.categoria IN ($placeholders)
         ORDER BY p.fecha DESC, p.id DESC LIMIT " . (int) $limit
    );
    $stmt->execute($categoriasValidas);
    $filas = $stmt->fetchAll();

    foreach ($filas as &$fila) {
        $fila['archivos'] = getArchivosDePublicacion($fila['id']);
    }
    unset($fila);

    return $filas;
}

// La próxima asamblea programada (fecha_evento en el futuro), para el
// callout "Próxima asamblea" del Panel. null si no hay ninguna.
function getProximaAsamblea(): ?array
{
    $stmt = db()->prepare(
        "SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
         FROM publicaciones p JOIN usuarios u ON u.id = p.autor_id
         WHERE p.categoria = 'convocatoria' AND p.fecha_evento >= CURDATE()
         ORDER BY p.fecha_evento ASC LIMIT 1"
    );
    $stmt->execute();
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function getPublicacionPorId($id): ?array
{
    $stmt = db()->prepare(
        'SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
         FROM publicaciones p JOIN usuarios u ON u.id = p.autor_id
         WHERE p.id = ?'
    );
    $stmt->execute([$id]);
    $publicacion = $stmt->fetch();
    if (!$publicacion) {
        return null;
    }
    $publicacion['archivos'] = getArchivosDePublicacion($id);
    return $publicacion;
}

// Se compara por fecha de creación real (creado_en), no por la fecha
// "editorial" (fecha), para saber qué publicaciones son nuevas para un
// usuario. $esMesa decide si cuentan también los borradores y lo dirigido
// solo al comité (igual que en getPublicaciones/contarPublicaciones).
function contarPublicacionesDesde(?string $fechaIso, bool $esMesa = false): int
{
    if (!$fechaIso) {
        return contarPublicaciones($esMesa);
    }
    $filtroVisibilidad = $esMesa ? '' : " AND publicado = 1 AND audiencia = 'todos'";
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM publicaciones
         WHERE creado_en > ?$filtroVisibilidad AND categoria NOT IN ('" . implode("','", CATEGORIAS_ASAMBLEA) . "')"
    );
    $stmt->execute([$fechaIso]);
    return (int) $stmt->fetchColumn();
}

function crearPublicacion(
    $autorId,
    string $categoria,
    string $titulo,
    string $cuerpo,
    string $fecha,
    bool $destacado = false,
    ?string $fechaEvento = null,
    string $prioridad = 'informativo',
    bool $publicado = true,
    string $audiencia = 'todos'
): string {
    $stmt = db()->prepare(
        'INSERT INTO publicaciones (autor_id, categoria, prioridad, destacado, publicado, audiencia, titulo, cuerpo, fecha, fecha_evento, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$autorId, $categoria, $prioridad, $destacado ? 1 : 0, $publicado ? 1 : 0, $audiencia, $titulo, $cuerpo, $fecha, $fechaEvento, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

// Sin parámetro de fecha a propósito: la fecha editorial se fija una sola
// vez al crear la publicación y no se puede modificar después. En cambio sí
// se registra editado_en, para poder avisar en pantalla "Editado el ...".
function actualizarPublicacion(
    $id,
    string $categoria,
    string $titulo,
    string $cuerpo,
    bool $destacado = false,
    ?string $fechaEvento = null,
    string $prioridad = 'informativo',
    bool $publicado = true,
    string $audiencia = 'todos'
): void {
    $stmt = db()->prepare(
        'UPDATE publicaciones SET categoria = ?, prioridad = ?, destacado = ?, publicado = ?, audiencia = ?, titulo = ?, cuerpo = ?, fecha_evento = ?, editado_en = ? WHERE id = ?'
    );
    $stmt->execute([$categoria, $prioridad, $destacado ? 1 : 0, $publicado ? 1 : 0, $audiencia, $titulo, $cuerpo, $fechaEvento, date('Y-m-d H:i:s'), $id]);
}

// Crea/edita una convocatoria o acta (categoria IN CATEGORIAS_ASAMBLEA), con
// sus propios campos (tipo de asamblea, hora/lugar o año) — a propósito
// SEPARADO de crearPublicacion()/actualizarPublicacion(): así, editar un
// aviso normal desde /panel/avisos nunca puede pisar estas columnas (que ese
// formulario ni conoce), y viceversa. Las convocatorias/actas no usan
// prioridad/destacado/borrador/audiencia (son documentos oficiales, siempre
// visibles para todos), por eso van fijos aquí y no como parámetros.
function crearAsamblea(
    $autorId,
    string $categoria,
    string $titulo,
    string $cuerpo,
    string $tipoAsamblea,
    ?string $fechaEvento = null,
    ?string $horaEvento = null,
    ?string $lugarEvento = null,
    ?int $anioAsamblea = null
): string {
    $stmt = db()->prepare(
        "INSERT INTO publicaciones
            (autor_id, categoria, prioridad, destacado, publicado, audiencia, titulo, cuerpo, fecha, fecha_evento, tipo_asamblea, hora_evento, lugar_evento, anio_asamblea, creado_en)
         VALUES (?, ?, 'informativo', 0, 1, 'todos', ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$autorId, $categoria, $titulo, $cuerpo, date('Y-m-d'), $fechaEvento, $tipoAsamblea, $horaEvento, $lugarEvento, $anioAsamblea, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

function actualizarAsamblea(
    $id,
    string $titulo,
    string $cuerpo,
    string $tipoAsamblea,
    ?string $fechaEvento = null,
    ?string $horaEvento = null,
    ?string $lugarEvento = null,
    ?int $anioAsamblea = null
): void {
    $stmt = db()->prepare(
        'UPDATE publicaciones
         SET titulo = ?, cuerpo = ?, tipo_asamblea = ?, fecha_evento = ?, hora_evento = ?, lugar_evento = ?, anio_asamblea = ?, editado_en = ?
         WHERE id = ?'
    );
    $stmt->execute([$titulo, $cuerpo, $tipoAsamblea, $fechaEvento, $horaEvento, $lugarEvento, $anioAsamblea, date('Y-m-d H:i:s'), $id]);
}

// Alterna publicado/borrador desde la tabla de /panel/avisos, sin pasar por
// el formulario completo de edición. No toca editado_en a propósito: esto
// es un cambio de estado, no una edición de contenido.
function alternarPublicadoPublicacion($id): void
{
    $stmt = db()->prepare('UPDATE publicaciones SET publicado = NOT publicado WHERE id = ?');
    $stmt->execute([$id]);
}

// Los registros de la tabla `archivos` se borran solos por el ON DELETE
// CASCADE; los ARCHIVOS FÍSICOS hay que borrarlos aparte (donde sí se conoce
// la carpeta de uploads), ANTES de llamar esto.
function eliminarPublicacion($id): void
{
    $stmt = db()->prepare('DELETE FROM publicaciones WHERE id = ?');
    $stmt->execute([$id]);
}
