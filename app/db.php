<?php
// Conexión PDO a MySQL, una sola vez por request (patrón singleton simple).
// Equivalente al `db/index.js` de la versión Node, pero contra MySQL en vez
// de SQLite.

function obtenerConfig(): array
{
    static $config = null;
    if ($config === null) {
        $rutaConfig = __DIR__ . '/config.php';
        if (!file_exists($rutaConfig)) {
            http_response_code(500);
            die('Falta app/config.php — copia app/config.example.php a app/config.php y llénalo con los datos reales de tu base de datos.');
        }
        $config = require $rutaConfig;
    }
    return $config;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $config = obtenerConfig()['db'];
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['nombre']);
        $pdo = new PDO($dsn, $config['usuario'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}
