<?php
require_once __DIR__ . '/../db.php';

const LOGIN_MAX_INTENTOS = 3;
const LOGIN_BLOQUEO_SEGUNDOS = 60;

// Cuántos segundos le quedan de bloqueo a esta IP (0 = no está bloqueada).
// Se revisa SIEMPRE al cargar /login, no solo al enviar el formulario — así
// recargar la página o cerrar y volver a abrir el navegador no sirve para
// "resetear" el bloqueo (la IP es lo que persiste, no la sesión ni la
// pestaña).
//
// TIMESTAMPDIFF se calcula DENTRO de MySQL, comparando bloqueado_hasta
// contra el NOW() de MySQL mismo — a propósito, para nunca restar la hora
// de MySQL contra time() de PHP: si el huso horario de uno no coincide
// exactamente con el del otro (muy común en hosting compartido, donde cada
// uno puede traer su propia zona configurada por separado), esa resta sale
// mal — normalmente da un número negativo, que max(0, ...) convierte en 0,
// o sea "nunca bloqueado" aunque sí se haya bloqueado. Así se evita esa
// clase de bug por completo.
function segundosBloqueadoLogin(string $ip): int
{
    $stmt = db()->prepare('SELECT TIMESTAMPDIFF(SECOND, NOW(), bloqueado_hasta) FROM login_intentos WHERE ip = ?');
    $stmt->execute([$ip]);
    $segundos = $stmt->fetchColumn();
    if ($segundos === false || $segundos === null) {
        return 0;
    }
    return max(0, (int) $segundos);
}

// Suma un intento fallido para esta IP (sin importar qué "usuario" haya
// escrito — el límite es por equipo, no por cuenta, para que no sirva
// probar varios usuarios distintos desde la misma IP para esquivarlo). Al
// llegar a LOGIN_MAX_INTENTOS se bloquea por LOGIN_BLOQUEO_SEGUNDOS y el
// contador vuelve a 0, para que al expirar el bloqueo arranque limpio.
function registrarIntentoFallidoLogin(string $ip): void
{
    $pdo = db();
    $pdo->prepare(
        'INSERT INTO login_intentos (ip, intentos, actualizado_en) VALUES (?, 1, NOW())
         ON DUPLICATE KEY UPDATE intentos = intentos + 1, actualizado_en = NOW()'
    )->execute([$ip]);

    $stmt = $pdo->prepare('SELECT intentos FROM login_intentos WHERE ip = ?');
    $stmt->execute([$ip]);
    $intentos = (int) $stmt->fetchColumn();

    if ($intentos >= LOGIN_MAX_INTENTOS) {
        $pdo->prepare(
            'UPDATE login_intentos SET intentos = 0, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL ' . LOGIN_BLOQUEO_SEGUNDOS . ' SECOND) WHERE ip = ?'
        )->execute([$ip]);
    }
}

// Se llama tras un login correcto — una cuenta que sí acertó no debe
// arrastrar los fallos previos de esa IP.
function limpiarIntentosLogin(string $ip): void
{
    db()->prepare('DELETE FROM login_intentos WHERE ip = ?')->execute([$ip]);
}
