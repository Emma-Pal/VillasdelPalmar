<?php
require_once __DIR__ . '/../../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);
    $item = getGaleriaItemPorId($id);

    if ($item) {
        @unlink(rutaArchivoFisico($item['archivo']));
        eliminarGaleriaItem($id);
        header('Location: /panel/galeria/' . $item['categoria']);
        exit;
    }
}

header('Location: /panel/galeria/admin');
exit;
