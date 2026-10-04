<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);
    $colaborador = getColaboradorPorId($id);

    if ($colaborador) {
        foreach (['foto', 'ine_archivo', 'curp_archivo', 'rfc_archivo', 'nss_archivo', 'domicilio_archivo', 'contrato_archivo'] as $campo) {
            if (!empty($colaborador[$campo])) {
                @unlink(rutaArchivoFisico($colaborador[$campo]));
            }
        }
        eliminarColaborador($id);
    }
}

header('Location: /panel/directorio');
exit;
