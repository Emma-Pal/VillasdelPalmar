<?php
require_once __DIR__ . '/../db.php';

const CATEGORIAS_GALERIA = ['alberca', 'areas-verdes', 'departamentos'];

function etiquetaCategoriaGaleria(string $categoria): string
{
    $etiquetas = [
        'alberca' => 'Alberca & terraza',
        'areas-verdes' => 'Áreas verdes',
        'departamentos' => 'Departamentos',
    ];
    return $etiquetas[$categoria] ?? $categoria;
}

function getGaleriaItems(?string $categoria = null): array
{
    if ($categoria) {
        $stmt = db()->prepare('SELECT * FROM galeria_items WHERE categoria = ? ORDER BY id');
        $stmt->execute([$categoria]);
        return $stmt->fetchAll();
    }
    return db()->query('SELECT * FROM galeria_items ORDER BY categoria, id')->fetchAll();
}

function getGaleriaItemPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM galeria_items WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function crearGaleriaItem(
    $autorId,
    string $categoria,
    string $eyebrow,
    string $titulo,
    string $descripcion,
    ?string $caracteristicas,
    string $archivo,
    string $archivoNombreOriginal
): string {
    $stmt = db()->prepare(
        'INSERT INTO galeria_items (categoria, eyebrow, titulo, descripcion, caracteristicas, archivo, archivo_nombre_original, autor_id, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$categoria, $eyebrow, $titulo, $descripcion, $caracteristicas, $archivo, $archivoNombreOriginal, $autorId, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

// $archivoNuevo opcional — si no se sube uno nuevo al editar, se conserva
// la foto que ya tenía (mismo patrón que actualizarDocumento()).
function actualizarGaleriaItem(
    $id,
    string $categoria,
    string $eyebrow,
    string $titulo,
    string $descripcion,
    ?string $caracteristicas,
    ?string $archivoNuevo = null,
    ?string $archivoNombreOriginalNuevo = null
): void {
    if ($archivoNuevo) {
        $stmt = db()->prepare(
            'UPDATE galeria_items SET categoria = ?, eyebrow = ?, titulo = ?, descripcion = ?, caracteristicas = ?, archivo = ?, archivo_nombre_original = ? WHERE id = ?'
        );
        $stmt->execute([$categoria, $eyebrow, $titulo, $descripcion, $caracteristicas, $archivoNuevo, $archivoNombreOriginalNuevo, $id]);
    } else {
        $stmt = db()->prepare(
            'UPDATE galeria_items SET categoria = ?, eyebrow = ?, titulo = ?, descripcion = ?, caracteristicas = ? WHERE id = ?'
        );
        $stmt->execute([$categoria, $eyebrow, $titulo, $descripcion, $caracteristicas, $id]);
    }
}

function eliminarGaleriaItem($id): void
{
    $stmt = db()->prepare('DELETE FROM galeria_items WHERE id = ?');
    $stmt->execute([$id]);
}
