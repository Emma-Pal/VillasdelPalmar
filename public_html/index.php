<?php
require_once __DIR__ . '/../app/bootstrap.php';

// El sitio es completamente privado: "/" solo decide a dónde mandar según la sesión.
header('Location: ' . ($usuario ? '/panel' : '/login'));
exit;
