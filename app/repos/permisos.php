<?php
require_once __DIR__ . '/../db.php';

// Permisos otorgables por el superadmin a cuentas de administrador
// específicas — pensado para crecer ("etc."): por ahora solo existe
// 'gestionar_admins' (dar de alta o eliminar otras cuentas de
// administrador, lo mismo que ya puede hacer el superadmin). Para agregar
// uno nuevo en el futuro no hace falta tocar la base de datos, solo usar
// otra cadena de $permiso.
const PERMISO_GESTIONAR_ADMINS = 'gestionar_admins';

function tienePermiso($usuarioId, string $permiso): bool
{
    $stmt = db()->prepare('SELECT 1 FROM permisos_usuario WHERE usuario_id = ? AND permiso = ?');
    $stmt->execute([$usuarioId, $permiso]);
    return (bool) $stmt->fetchColumn();
}

function otorgarPermiso($usuarioId, string $permiso, $otorgadoPorId): void
{
    db()->prepare(
        'INSERT INTO permisos_usuario (usuario_id, permiso, otorgado_por, creado_en) VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE otorgado_por = VALUES(otorgado_por)'
    )->execute([$usuarioId, $permiso, $otorgadoPorId]);
}

function revocarPermiso($usuarioId, string $permiso): void
{
    db()->prepare('DELETE FROM permisos_usuario WHERE usuario_id = ? AND permiso = ?')->execute([$usuarioId, $permiso]);
}
