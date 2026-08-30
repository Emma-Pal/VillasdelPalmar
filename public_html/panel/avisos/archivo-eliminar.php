<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $archivo = getArchivoPorId((int) ($_GET['archivo'] ?? 0));
    if ($archivo) {
        @unlink(rutaArchivoFisico($archivo['archivo']));
        eliminarArchivo($archivo['id']);
    }
}

header('Location: /panel/avisos/editar?id=' . (int) ($_GET['pub'] ?? 0));
exit;
