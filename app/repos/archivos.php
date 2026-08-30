<?php
require_once __DIR__ . '/../db.php';

function getArchivosDePublicacion($publicacionId): array
{
    $stmt = db()->prepare('SELECT * FROM archivos WHERE publicacion_id = ? ORDER BY id');
    $stmt->execute([$publicacionId]);
    return $stmt->fetchAll();
}

function getArchivoPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM archivos WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function agregarArchivo($publicacionId, string $archivo, string $archivoNombreOriginal): string
{
    $stmt = db()->prepare(
        'INSERT INTO archivos (publicacion_id, archivo, archivo_nombre_original) VALUES (?, ?, ?)'
    );
    $stmt->execute([$publicacionId, $archivo, $archivoNombreOriginal]);
    return db()->lastInsertId();
}

function eliminarArchivo($id): void
{
    $stmt = db()->prepare('DELETE FROM archivos WHERE id = ?');
    $stmt->execute([$id]);
}
