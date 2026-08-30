<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);

    if ($id === (int) $usuario['id']) {
        http_response_code(400);
        renderError(
            'Acción no permitida — Villas del Palmar',
            'No puedes eliminar tu propia cuenta.',
            'No puedes eliminar tu propia cuenta mientras la tienes abierta.'
        );
        exit;
    }

    try {
        eliminarUsuario($id);
    } catch (PDOException $e) {
        // FK constraint: el usuario tiene publicaciones (autor_id) y no se cascadea a propósito.
        http_response_code(400);
        renderError(
            'No se puede eliminar — Villas del Palmar',
            'Este usuario tiene contenido asociado.',
            'No se puede eliminar: este usuario tiene publicaciones a su nombre. Elimina esas publicaciones primero si de verdad quieres borrar la cuenta.'
        );
        exit;
    }
}

header('Location: /panel/usuarios');
exit;
