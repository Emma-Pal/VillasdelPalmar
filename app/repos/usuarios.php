<?php
require_once __DIR__ . '/../db.php';

function getUsuarioPorLogin(string $usuario): ?array
{
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE usuario = ?');
    $stmt->execute([$usuario]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function getUsuarioPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function getMesa(): array
{
    return db()->query("SELECT * FROM usuarios WHERE tipo = 'mesa' ORDER BY cargo")->fetchAll();
}

function getUsuarios(): array
{
    return db()->query('SELECT * FROM usuarios ORDER BY tipo DESC, cargo, nombre')->fetchAll();
}

function crearUsuario(string $tipo, string $nombre, ?string $cargo, string $usuario, string $passwordHash): string
{
    $cargoFinal = $tipo === 'mesa' ? ($cargo ?: null) : null;
    $stmt = db()->prepare(
        'INSERT INTO usuarios (tipo, nombre, cargo, usuario, password_hash) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$tipo, $nombre, $cargoFinal, $usuario, $passwordHash]);
    return db()->lastInsertId();
}

// $passwordHash es opcional (null) — si no se manda, se conserva la actual
// (así "editar" no obliga a resetear la clave).
function actualizarUsuario($id, string $tipo, string $nombre, ?string $cargo, string $usuario, ?string $passwordHash = null): void
{
    $cargoFinal = $tipo === 'mesa' ? ($cargo ?: null) : null;
    if ($passwordHash) {
        $stmt = db()->prepare(
            'UPDATE usuarios SET tipo = ?, nombre = ?, cargo = ?, usuario = ?, password_hash = ? WHERE id = ?'
        );
        $stmt->execute([$tipo, $nombre, $cargoFinal, $usuario, $passwordHash, $id]);
    } else {
        $stmt = db()->prepare(
            'UPDATE usuarios SET tipo = ?, nombre = ?, cargo = ?, usuario = ? WHERE id = ?'
        );
        $stmt->execute([$tipo, $nombre, $cargoFinal, $usuario, $id]);
    }
}

// Lanza PDOException (violación de FK) si el usuario tiene publicaciones —
// así no se puede borrar sin querer al autor de un estado financiero ya
// publicado. Quien llame a esto decide cómo mostrar el error de forma amigable.
function eliminarUsuario($id): void
{
    $stmt = db()->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
}

function getUltimaVisitaAvisos($usuarioId): ?string
{
    $stmt = db()->prepare('SELECT ultima_visita_avisos FROM usuarios WHERE id = ?');
    $stmt->execute([$usuarioId]);
    $valor = $stmt->fetchColumn();
    return $valor !== false ? $valor : null;
}

function marcarVisitaAvisos($usuarioId, string $fechaIso): void
{
    $stmt = db()->prepare('UPDATE usuarios SET ultima_visita_avisos = ? WHERE id = ?');
    $stmt->execute([$fechaIso, $usuarioId]);
}
