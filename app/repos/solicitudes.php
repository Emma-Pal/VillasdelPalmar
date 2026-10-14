<?php
require_once __DIR__ . '/../db.php';

const TIPOS_SOLICITUD = ['queja', 'falla', 'sugerencia'];

function etiquetaTipoSolicitud(string $tipo): string
{
    $etiquetas = ['queja' => 'Queja', 'falla' => 'Falla', 'sugerencia' => 'Sugerencia'];
    return $etiquetas[$tipo] ?? $tipo;
}

// El folio que ve el propietario es el id con un prefijo MÁS un token
// aleatorio de 4 caracteres (ej. "SOL-000042-K7Q4") — el id solo no basta
// para "Consultar un folio", porque al ser secuencial cualquiera logueado
// podría ir probando números y leer solicitudes ajenas (quejas, fallas,
// etc. de otros propietarios). El token lo hace imposible de adivinar sin
// que alguien te lo haya dado. $token en folioSolicitud() es la columna
// folio_token de la fila — null en solicitudes creadas ANTES de este
// cambio, que por compatibilidad se siguen consultando sin token (ver
// getSolicitudPorFolio()).
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

// Usada por /panel/solicitudes/consultar.php: acepta "SOL-000042-K7Q4",
// "SOL-000042" o incluso "42" a secas, tolerante a como lo haya anotado la
// persona. El id es el primer grupo de dígitos que aparezca; el token, si
// viene, son los últimos 4 caracteres alfanuméricos tras un guion. Una
// solicitud CON token guardado exige que el escrito coincida exacto — una
// solicitud vieja SIN token (folio_token NULL, de antes de este cambio) se
// acepta tal cual, para no invalidar folios que ya se le dieron a alguien.
function getSolicitudPorFolio(string $folioTexto): ?array
{
    $texto = strtoupper(trim($folioTexto));
    if (!preg_match('/(\d+)/', $texto, $mId)) {
        return null;
    }
    $solicitud = getSolicitudPorId((int) $mId[1]);
    if (!$solicitud) {
        return null;
    }

    $tokenReal = $solicitud['folio_token'] ?? null;
    if (!$tokenReal) {
        return $solicitud; // folio anterior a este cambio, sin token
    }

    $tokenEscrito = null;
    if (preg_match('/-([A-Z0-9]{4})\s*$/', $texto, $mToken)) {
        $tokenEscrito = $mToken[1];
    }

    if (!$tokenEscrito || !hash_equals($tokenReal, $tokenEscrito)) {
        return null;
    }

    return $solicitud;
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
