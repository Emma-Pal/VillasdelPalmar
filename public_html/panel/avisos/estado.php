<?php
// Alterna publicado/borrador directo desde la tabla de /panel/avisos (la
// pastilla de "Estado" es un botón para la mesa) — sin pasar por el
// formulario completo de edición.
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = (int) ($_GET['id'] ?? 0);
    if (getPublicacionPorId($id)) {
        alternarPublicadoPublicacion($id);
    }
}

// Regresa a la misma categoría/página desde donde se alternó el estado
// (viene de un campo oculto con la URL actual de la tabla), no siempre al
// feed general sin filtros.
$volver = $_POST['volver'] ?? '/panel/avisos';
if (mb_substr($volver, 0, 1) !== '/') {
    $volver = '/panel/avisos'; // nunca confiar en una URL externa aquí
}
header('Location: ' . $volver);
exit;
