<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);
    $contacto = getContactoPorId($id);

    if ($contacto) {
        eliminarContacto($id);
        header('Location: /panel/directorio#categoria-' . $contacto['categoria']);
        exit;
    }
}

header('Location: /panel/directorio');
exit;
