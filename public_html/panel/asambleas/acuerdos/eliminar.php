<?php
require_once __DIR__ . '/../../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    eliminarAcuerdo((int) ($_GET['id'] ?? 0));
}

header('Location: /panel/asambleas#acuerdos');
exit;
