<?php
// Se incluye al inicio de cada página. Junta lo que en la versión Node
// hacían los middlewares globales de server.js: arrancar la sesión, exponer
// el usuario logueado y el token CSRF a las vistas, y calcular el globito
// de "avisos nuevos" para el header.

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/funciones.php';
require_once __DIR__ . '/validacion.php';
require_once __DIR__ . '/subida.php';
require_once __DIR__ . '/repos/usuarios.php';
require_once __DIR__ . '/repos/publicaciones.php';
require_once __DIR__ . '/repos/archivos.php';

// Disponibles en todas las vistas (equivalente a res.locals en Express).
$usuario = $_SESSION['usuario'] ?? null;
$csrfToken = csrfToken();
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$avisosNuevos = 0;
if ($usuario) {
    $ultimaVisita = getUltimaVisitaAvisos($usuario['id']);
    $avisosNuevos = contarPublicacionesDesde($ultimaVisita);
}
