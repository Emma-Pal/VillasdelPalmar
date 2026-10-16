<?php
require_once __DIR__ . '/../db.php';

// Límite de "acciones por ventana de tiempo" POR CUENTA — distinto de
// login_intentos (que es por IP y para fuerza bruta de contraseñas). Este
// es para que una cuenta ya logueada (comprometida, o solo un error de
// quien la usa) no pueda inundar el sitio creando cientos de solicitudes,
// registros de estancia o avisos en poco tiempo.
//
// Ventana FIJA, no deslizante: cada $accion lleva su propio contador y su
// propio "desde cuándo" — si ya pasó $ventanaSegundos desde que arrancó la
// ventana actual, se reinicia sola en 1 en vez de seguir sumando. Regresa
// true si esta acción #N todavía cabe dentro del límite (y la cuenta como
// ya ocurrida); false si ya se pasó — quien llame debe rechazar la acción
// en ese caso, sin haber hecho el trabajo real todavía.
function limiteAccionPermitido($usuarioId, string $accion, int $maxPorVentana, int $ventanaSegundos): bool
{
    $pdo = db();
    $pdo->prepare(
        'INSERT INTO limite_acciones (usuario_id, accion, ventana_inicio, cantidad)
         VALUES (?, ?, NOW(), 1)
         ON DUPLICATE KEY UPDATE
           cantidad = IF(TIMESTAMPDIFF(SECOND, ventana_inicio, NOW()) > ?, 1, cantidad + 1),
           ventana_inicio = IF(TIMESTAMPDIFF(SECOND, ventana_inicio, NOW()) > ?, NOW(), ventana_inicio)'
    )->execute([$usuarioId, $accion, $ventanaSegundos, $ventanaSegundos]);

    $stmt = $pdo->prepare('SELECT cantidad FROM limite_acciones WHERE usuario_id = ? AND accion = ?');
    $stmt->execute([$usuarioId, $accion]);

    return (int) $stmt->fetchColumn() <= $maxPorVentana;
}
