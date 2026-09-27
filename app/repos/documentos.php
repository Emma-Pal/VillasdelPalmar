<?php
require_once __DIR__ . '/../db.php';

const CATEGORIAS_DOCUMENTO = ['reglamento', 'escritura', 'politica', 'formato'];

function etiquetaCategoriaDocumento(string $categoria): string
{
    $etiquetas = [
        'reglamento' => 'Reglamento interno',
        'escritura' => 'Escritura constitutiva',
        'politica' => 'Políticas de áreas comunes',
        'formato' => 'Formatos',
    ];
    return $etiquetas[$categoria] ?? $categoria;
}

function getDocumentos(?string $categoria = null): array
{
    if ($categoria) {
        $stmt = db()->prepare(
            'SELECT d.*, u.nombre AS autor_nombre
             FROM documentos d JOIN usuarios u ON u.id = d.autor_id
             WHERE d.categoria = ? ORDER BY d.titulo'
        );
        $stmt->execute([$categoria]);
        return $stmt->fetchAll();
    }
    return db()->query(
        'SELECT d.*, u.nombre AS autor_nombre
         FROM documentos d JOIN usuarios u ON u.id = d.autor_id
         ORDER BY d.categoria, d.titulo'
    )->fetchAll();
}

function getDocumentoPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM documentos WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function crearDocumento(
    $autorId,
    string $categoria,
    string $titulo,
    ?string $descripcion,
    string $archivo,
    string $archivoNombreOriginal
): string {
    $stmt = db()->prepare(
        'INSERT INTO documentos (categoria, titulo, descripcion, archivo, archivo_nombre_original, autor_id, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$categoria, $titulo, $descripcion, $archivo, $archivoNombreOriginal, $autorId, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

// $archivoNuevo/$archivoNombreOriginalNuevo son opcionales — si no se sube
// un archivo nuevo al editar, se conserva el que ya había (igual que la
// contraseña opcional en actualizarUsuario()).
function actualizarDocumento(
    $id,
    string $categoria,
    string $titulo,
    ?string $descripcion,
    ?string $archivoNuevo = null,
    ?string $archivoNombreOriginalNuevo = null
): void {
    if ($archivoNuevo) {
        $stmt = db()->prepare(
            'UPDATE documentos SET categoria = ?, titulo = ?, descripcion = ?, archivo = ?, archivo_nombre_original = ?, actualizado_en = ? WHERE id = ?'
        );
        $stmt->execute([$categoria, $titulo, $descripcion, $archivoNuevo, $archivoNombreOriginalNuevo, date('Y-m-d H:i:s'), $id]);
    } else {
        $stmt = db()->prepare(
            'UPDATE documentos SET categoria = ?, titulo = ?, descripcion = ?, actualizado_en = ? WHERE id = ?'
        );
        $stmt->execute([$categoria, $titulo, $descripcion, date('Y-m-d H:i:s'), $id]);
    }
}

function eliminarDocumento($id): void
{
    $stmt = db()->prepare('DELETE FROM documentos WHERE id = ?');
    $stmt->execute([$id]);
}
