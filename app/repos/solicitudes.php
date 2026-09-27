<?php
require_once __DIR__ . '/../db.php';

const TIPOS_SOLICITUD = ['queja', 'falla', 'sugerencia'];

function etiquetaTipoSolicitud(string $tipo): string
{
    $etiquetas = ['queja' => 'Queja', 'falla' => 'Falla', 'sugerencia' => 'Sugerencia'];
    return $etiquetas[$tipo] ?? $tipo;
}

// El folio que ve el propietario es solo el id con un prefijo — no hace
// falta guardarlo aparte. idDesdeFolio() acepta tanto "SOL-000042" como
// "42" a secas, para que la búsqueda en /panel/solicitudes/consultar.php
// sea tolerante a como lo haya anotado la persona.
function folioSolicitud($id): string
{
    return 'SOL-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
}

function idDesdeFolio(string $folio): ?int
{
    $digitos = preg_replace('/\D+/', '', $folio);
    return $digitos !== '' ? (int) $digitos : null;
}

function crearSolicitud($autorId, string $tipo, string $asunto, string $descripcion, ?string $ubicacion): string
{
    $stmt = db()->prepare(
        'INSERT INTO solicitudes (tipo, asunto, descripcion, ubicacion, autor_id, creado_en) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$tipo, $asunto, $descripcion, $ubicacion, $autorId, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

function getSolicitudPorId($id): ?array
{
    $stmt = db()->prepare(
        'SELECT s.*, u.nombre AS autor_nombre
         FROM solicitudes s JOIN usuarios u ON u.id = s.autor_id
         WHERE s.id = ?'
    );
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function getSolicitudes(?string $estatus = null): array
{
    if ($estatus) {
        $stmt = db()->prepare(
            'SELECT s.*, u.nombre AS autor_nombre
             FROM solicitudes s JOIN usuarios u ON u.id = s.autor_id
             WHERE s.estatus = ? ORDER BY s.creado_en DESC'
        );
        $stmt->execute([$estatus]);
        return $stmt->fetchAll();
    }
    return db()->query(
        'SELECT s.*, u.nombre AS autor_nombre
         FROM solicitudes s JOIN usuarios u ON u.id = s.autor_id
         ORDER BY s.creado_en DESC'
    )->fetchAll();
}

function actualizarEstatusSolicitud($id, string $estatus, ?string $respuesta): void
{
    $stmt = db()->prepare(
        'UPDATE solicitudes SET estatus = ?, respuesta = ?, actualizado_en = ? WHERE id = ?'
    );
    $stmt->execute([$estatus, $respuesta, date('Y-m-d H:i:s'), $id]);
}
