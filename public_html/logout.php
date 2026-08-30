<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
}

$_SESSION = [];
session_destroy();
header('Location: /login');
exit;
