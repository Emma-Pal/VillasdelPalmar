<?php
require_once __DIR__ . '/../db.php';

const ESTATUS_ACUERDO = ['pendiente', 'en_progreso', 'cumplido'];

function getAcuerdos(): array
{
    return db()->query(
        "SELECT a.*, u.nombre AS autor_nombre
         FROM acuerdos a JOIN usuarios u ON u.id = a.autor_id
         ORDER BY (a.estatus = 'cumplido'), a.fecha_limite IS NULL, a.fecha_limite ASC, a.id DESC"
    )->fetchAll();
}

function getAcuerdoPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM acuerdos WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function crearAcuerdo($autorId, string $descripcion, string $estatus, ?string $fechaLimite): string
{
    $stmt = db()->prepare(
        'INSERT INTO acuerdos (descripcion, estatus, fecha_limite, autor_id, creado_en) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$descripcion, $estatus, $fechaLimite, $autorId, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

function actualizarAcuerdo($id, string $descripcion, string $estatus, ?string $fechaLimite): void
{
    $stmt = db()->prepare(
        'UPDATE acuerdos SET descripcion = ?, estatus = ?, fecha_limite = ?, actualizado_en = ? WHERE id = ?'
    );
    $stmt->execute([$descripcion, $estatus, $fechaLimite, date('Y-m-d H:i:s'), $id]);
}

function eliminarAcuerdo($id): void
{
    $stmt = db()->prepare('DELETE FROM acuerdos WHERE id = ?');
    $stmt->execute([$id]);
}
