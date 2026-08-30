<?php
// Reglas de contraseña compartidas entre "Mi cuenta" y la administración de
// Usuarios — mismo criterio que lib/validacion.js en la versión Node.

define('MIN_LARGO_PASSWORD', 8);

function passwordEsValida(?string $password): bool
{
    return is_string($password) && strlen($password) >= MIN_LARGO_PASSWORD;
}
