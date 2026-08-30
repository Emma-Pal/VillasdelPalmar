<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);
    $publicacion = getPublicacionPorId($id);

    // Los archivos físicos se borran del disco antes de eliminar el
    // registro (las filas de `archivos` se limpian solas por el
    // ON DELETE CASCADE en la base de datos).
    if ($publicacion) {
        foreach ($publicacion['archivos'] as $archivo) {
            @unlink(rutaArchivoFisico($archivo['archivo']));
        }
    }
    eliminarPublicacion($id);
}

header('Location: /panel/avisos');
exit;
