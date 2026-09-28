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

    // Una convocatoria/acta se elimina desde /panel/asambleas — regresar
    // ahí (con ancla), igual que al crearla/editarla.
    if ($publicacion && in_array($publicacion['categoria'], CATEGORIAS_ASAMBLEA, true)) {
        header('Location: /panel/asambleas#' . ($publicacion['categoria'] === 'convocatoria' ? 'convocatorias' : 'actas'));
        exit;
    }
}

header('Location: /panel/avisos');
exit;
