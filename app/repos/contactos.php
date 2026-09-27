<?php
require_once __DIR__ . '/../db.php';

const CATEGORIAS_CONTACTO = ['administracion', 'seguridad', 'mantenimiento', 'emergencia', 'proveedor'];

function etiquetaCategoriaContacto(string $categoria): string
{
    $etiquetas = [
        'administracion' => 'Administración',
        'seguridad' => 'Seguridad',
        'mantenimiento' => 'Mantenimiento',
        'emergencia' => 'Emergencias',
        'proveedor' => 'Proveedores autorizados',
    ];
    return $etiquetas[$categoria] ?? $categoria;
}

function getContactos(?string $categoria = null): array
{
    if ($categoria) {
        $stmt = db()->prepare('SELECT * FROM contactos WHERE categoria = ? ORDER BY nombre');
        $stmt->execute([$categoria]);
        return $stmt->fetchAll();
    }
    return db()->query('SELECT * FROM contactos ORDER BY categoria, nombre')->fetchAll();
}

function getContactoPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM contactos WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function crearContacto(string $categoria, string $nombre, ?string $puesto, ?string $telefono, ?string $correo, ?string $notas): string
{
    $stmt = db()->prepare(
        'INSERT INTO contactos (categoria, nombre, puesto, telefono, correo, notas, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$categoria, $nombre, $puesto, $telefono, $correo, $notas, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

function actualizarContacto($id, string $categoria, string $nombre, ?string $puesto, ?string $telefono, ?string $correo, ?string $notas): void
{
    $stmt = db()->prepare(
        'UPDATE contactos SET categoria = ?, nombre = ?, puesto = ?, telefono = ?, correo = ?, notas = ? WHERE id = ?'
    );
    $stmt->execute([$categoria, $nombre, $puesto, $telefono, $correo, $notas, $id]);
}

function eliminarContacto($id): void
{
    $stmt = db()->prepare('DELETE FROM contactos WHERE id = ?');
    $stmt->execute([$id]);
}
