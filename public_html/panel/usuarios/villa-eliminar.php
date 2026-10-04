<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);
    $villa = getVillaPorId($id);

    if ($villa) {
        eliminarTitularesDeVilla($id); // borra también sus archivos de INE
        if ($villa['escritura_archivo']) @unlink(rutaArchivoFisico($villa['escritura_archivo']));
        if ($villa['relacion_copropietarios_archivo']) @unlink(rutaArchivoFisico($villa['relacion_copropietarios_archivo']));

        try {
            eliminarUsuario($id);
        } catch (PDOException $e) {
            http_response_code(400);
            renderError(
                'No se puede eliminar — Villas del Palmar',
                'Esta villa tiene contenido asociado.',
                'No se puede eliminar: esta cuenta tiene publicaciones a su nombre. Elimina esas publicaciones primero si de verdad quieres borrar la villa.'
            );
            exit;
        }
    }
}

header('Location: /panel/usuarios');
exit;
