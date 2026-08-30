<?php
// Plantilla de configuración. En el servidor real, copiar este archivo a
// config.php (que SÍ existe en el servidor pero NUNCA se sube a git) y
// llenarlo con los datos que dio el Database Wizard de cPanel.
//
// En cPanel, la base y el usuario normalmente llevan el prefijo de tu
// cuenta, ej. "villasdel2309_palmar" y "villasdel2309_admin".

return [
    'db' => [
        'host' => 'localhost',
        'nombre' => 'villasdel2309_palmar',
        'usuario' => 'villasdel2309_admin',
        'password' => 'CAMBIAR_ESTO',
    ],
    // Secreto usado para reforzar la sesión de PHP (opcional pero recomendado).
    'session_secret' => 'CAMBIAR_ESTO_POR_ALGO_LARGO_Y_UNICO',
];
