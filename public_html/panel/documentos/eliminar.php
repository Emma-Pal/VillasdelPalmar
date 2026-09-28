<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);
    $documento = getDocumentoPorId($id);

    if ($documento) {
        @unlink(rutaArchivoFisico($documento['archivo']));
        eliminarDocumento($id);
        header('Location: /panel/documentos#categoria-' . $documento['categoria']);
        exit;
    }
}

header('Location: /panel/documentos');
exit;
