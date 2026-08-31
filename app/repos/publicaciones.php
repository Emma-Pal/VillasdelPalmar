<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/archivos.php';

// Las 3 categorías "de fábrica", con pestaña y color propio en el diseño.
// La mesa directiva puede además escribir una categoría libre ("Otra") al
// crear/editar una publicación — se guarda tal cual y se le da una pestaña
// dinámica en /panel/avisos (ver getCategoriasUsadas()).
const CATEGORIAS_BASE = ['financiero', 'mejora', 'aviso'];

// $categoria puede ser null (sin filtro). $limit/$offset se bindean como
// enteros a propósito: con PDO::ATTR_EMULATE_PREPARES=false, MySQL rechaza
// LIMIT/OFFSET si se bindean como texto (error típico "Incorrect arguments
// to mysqld_stmt_execute").
function getPublicaciones(?string $categoria, int $limit = 10, int $offset = 0): array
{
    $base = 'SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
              FROM publicaciones p
              JOIN usuarios u ON u.id = p.autor_id';

    if ($categoria) {
        $sql = "$base WHERE p.categoria = :categoria ORDER BY p.fecha DESC, p.id DESC LIMIT :limit OFFSET :offset";
    } else {
        $sql = "$base ORDER BY p.fecha DESC, p.id DESC LIMIT :limit OFFSET :offset";
    }

    $stmt = db()->prepare($sql);
    if ($categoria) {
        $stmt->bindValue(':categoria', $categoria, PDO::PARAM_STR);
    }
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

// Todas las categorías que ya se han usado alguna vez (para poder ofrecer
// pestaña de filtro también a las categorías "libres" que la mesa haya
// escrito con "Otra", además de las 3 de fábrica).
function getCategoriasUsadas(): array
{
    $stmt = db()->query('SELECT DISTINCT categoria FROM publicaciones ORDER BY categoria');
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function contarPublicaciones(?string $categoria = null): int
{
    if ($categoria) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM publicaciones WHERE categoria = ?');
        $stmt->execute([$categoria]);
    } else {
        $stmt = db()->query('SELECT COUNT(*) FROM publicaciones');
    }
    return (int) $stmt->fetchColumn();
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
// "editorial" (fecha), para saber qué publicaciones son nuevas para un usuario.
function contarPublicacionesDesde(?string $fechaIso): int
{
    if (!$fechaIso) {
        return contarPublicaciones();
    }
    $stmt = db()->prepare('SELECT COUNT(*) FROM publicaciones WHERE creado_en > ?');
    $stmt->execute([$fechaIso]);
    return (int) $stmt->fetchColumn();
}

function crearPublicacion($autorId, string $categoria, string $titulo, string $cuerpo, string $fecha): string
{
    $stmt = db()->prepare(
        'INSERT INTO publicaciones (autor_id, categoria, titulo, cuerpo, fecha, creado_en) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$autorId, $categoria, $titulo, $cuerpo, $fecha, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

// Sin parámetro de fecha a propósito: la fecha editorial se fija una sola
// vez al crear la publicación y no se puede modificar después. En cambio sí
// se registra editado_en, para poder avisar en pantalla "Editado el ...".
function actualizarPublicacion($id, string $categoria, string $titulo, string $cuerpo): void
{
    $stmt = db()->prepare(
        'UPDATE publicaciones SET categoria = ?, titulo = ?, cuerpo = ?, editado_en = ? WHERE id = ?'
    );
    $stmt->execute([$categoria, $titulo, $cuerpo, date('Y-m-d H:i:s'), $id]);
}

// Los registros de la tabla `archivos` se borran solos por el ON DELETE
// CASCADE; los ARCHIVOS FÍSICOS hay que borrarlos aparte (donde sí se conoce
// la carpeta de uploads), ANTES de llamar esto.
function eliminarPublicacion($id): void
{
    $stmt = db()->prepare('DELETE FROM publicaciones WHERE id = ?');
    $stmt->execute([$id]);
}
