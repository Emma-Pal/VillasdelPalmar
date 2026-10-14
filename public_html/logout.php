<?php
// Solo POST — con GET, cualquier sitio externo podría cerrar la sesión de
// alguien con solo un <img src="/logout"> o un link, sin que la persona lo
// pidiera (un CSRF de poca gravedad, pero gratis de evitar: por eso
// verificarCsrf() es obligatorio aquí, no opcional).
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel');
    exit;
}

verificarCsrf();

$_SESSION = [];
session_destroy();
header('Location: /login');
exit;
