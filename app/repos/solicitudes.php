<?php
require_once __DIR__ . '/../db.php';

const TIPOS_SOLICITUD = ['queja', 'falla', 'sugerencia'];

function etiquetaTipoSolicitud(string $tipo): string
{
    $etiquetas = ['queja' => 'Queja', 'falla' => 'Falla', 'sugerencia' => 'Sugerencia'];
    return $etiquetas[$tipo] ?? $tipo;
}

// El folio es solo un número de referencia legible (ej. "SOL-000042-K7Q4")
// para cuando un propietario necesita mencionar una solicitud por teléfono
// o correo — ya no hace falta para buscarla (/panel/solicitudes la muestra
// directo en "Mis solicitudes"), pero se conserva como identificador corto.
function folioSolicitud($id, ?string $token = null): string
{
    $folio = 'SOL-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    return $token ? $folio . '-' . $token : $folio;
}

function generarTokenFolio(): string
{
    $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $token = '';
    for ($i = 0; $i < 4; $i++) {
        $token .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
    }
    return $token;
}

function crearSolicitud($autorId, string $tipo, string $asunto, string $descripcion, ?string $ubicacion): string
{
    $token = generarTokenFolio();
    $stmt = db()->prepare(
        'INSERT INTO solicitudes (tipo, asunto, descripcion, ubicacion, folio_token, autor_id, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$tipo, $asunto, $descripcion, $ubicacion, $token, $autorId, date('Y-m-d H:i:s')]);
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

// "Mis solicitudes" de /panel/solicitudes — reemplaza la búsqueda manual
// por folio: el propietario ya ve ahí mismo todo lo que él reportó.
function getSolicitudesDeAutor($autorId): array
{
    $stmt = db()->prepare('SELECT * FROM solicitudes WHERE autor_id = ? ORDER BY creado_en DESC');
    $stmt->execute([$autorId]);
    return $stmt->fetchAll();
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
