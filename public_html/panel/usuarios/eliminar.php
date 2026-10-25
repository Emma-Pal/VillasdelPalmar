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

    // Esta ruta solo borra cuentas de administrador (las de propietario se
    // borran aparte, en villa-eliminar.php) — y entre administradores, hace
    // falta el permiso "gestionar admins" (el superadmin siempre lo tiene;
    // a otras cuentas se les puede otorgar desde "Editar administrador").
    $objetivo = getUsuarioPorId($id);
    if ($objetivo && $objetivo['tipo'] === 'mesa') {
        requireGestionarAdmins();

        // La cuenta del superadmin nunca se elimina desde aquí, ni siquiera
        // con el permiso otorgado — solo puede existir una, y borrarla
        // dejaría al sitio sin nadie que pueda volver a asignarla.
        if (!empty($objetivo['es_superadmin'])) {
            http_response_code(400);
            renderError(
                'Acción no permitida — Villas del Palmar',
                'No se puede eliminar al superadministrador.',
                'La cuenta del superadministrador no se puede eliminar desde aquí.'
            );
            exit;
        }
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
