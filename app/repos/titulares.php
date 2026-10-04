<?php
require_once __DIR__ . '/../db.php';

function getTitularPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM titulares WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getTitularesDeVilla($usuarioId): array
{
    $stmt = db()->prepare(
        "SELECT * FROM titulares WHERE usuario_id = ? ORDER BY (caracter = 'propietario') DESC, id ASC"
    );
    $stmt->execute([$usuarioId]);
    return $stmt->fetchAll();
}

// Reemplaza el conjunto completo de titulares de una villa a partir de lo
// capturado en el formulario: actualiza los que ya existían (vienen con
// 'id'), crea los nuevos, y borra los que ya no están en $titulares. Así el
// formulario no tiene que mandar operaciones individuales de alta/baja, solo
// el estado final que quiere para la villa.
function reemplazarTitulares($usuarioId, array $titulares): void
{
    $pdo = db();
    $idsConservados = array_filter(array_column($titulares, 'id'));

    if ($idsConservados) {
        $marcadores = implode(',', array_fill(0, count($idsConservados), '?'));
        $stmt = $pdo->prepare("SELECT ine_frente_archivo, ine_reverso_archivo FROM titulares WHERE usuario_id = ? AND id NOT IN ($marcadores)");
        $stmt->execute([$usuarioId, ...$idsConservados]);
    } else {
        $stmt = $pdo->prepare('SELECT ine_frente_archivo, ine_reverso_archivo FROM titulares WHERE usuario_id = ?');
        $stmt->execute([$usuarioId]);
    }
    foreach ($stmt->fetchAll() as $eliminado) {
        if ($eliminado['ine_frente_archivo']) @unlink(rutaArchivoFisico($eliminado['ine_frente_archivo']));
        if ($eliminado['ine_reverso_archivo']) @unlink(rutaArchivoFisico($eliminado['ine_reverso_archivo']));
    }

    if ($idsConservados) {
        $marcadores = implode(',', array_fill(0, count($idsConservados), '?'));
        $pdo->prepare("DELETE FROM titulares WHERE usuario_id = ? AND id NOT IN ($marcadores)")
            ->execute([$usuarioId, ...$idsConservados]);
    } else {
        $pdo->prepare('DELETE FROM titulares WHERE usuario_id = ?')->execute([$usuarioId]);
    }

    foreach ($titulares as $t) {
        if (!empty($t['id'])) {
            $campos = ['nombre = ?', 'caracter = ?', 'curp = ?', 'telefono = ?', 'correo = ?'];
            $valores = [$t['nombre'], $t['caracter'], $t['curp'], $t['telefono'], $t['correo']];
            if (!empty($t['ine_frente_archivo'])) {
                $anterior = getTitularPorId($t['id']);
                if ($anterior && $anterior['ine_frente_archivo']) @unlink(rutaArchivoFisico($anterior['ine_frente_archivo']));
                $campos[] = 'ine_frente_archivo = ?';
                $campos[] = 'ine_frente_archivo_nombre_original = ?';
                $valores[] = $t['ine_frente_archivo'];
                $valores[] = $t['ine_frente_archivo_nombre_original'];
            }
            if (!empty($t['ine_reverso_archivo'])) {
                $anterior = getTitularPorId($t['id']);
                if ($anterior && $anterior['ine_reverso_archivo']) @unlink(rutaArchivoFisico($anterior['ine_reverso_archivo']));
                $campos[] = 'ine_reverso_archivo = ?';
                $campos[] = 'ine_reverso_archivo_nombre_original = ?';
                $valores[] = $t['ine_reverso_archivo'];
                $valores[] = $t['ine_reverso_archivo_nombre_original'];
            }
            $valores[] = $t['id'];
            $pdo->prepare('UPDATE titulares SET ' . implode(', ', $campos) . ' WHERE id = ?')->execute($valores);
        } else {
            $pdo->prepare(
                'INSERT INTO titulares (usuario_id, nombre, caracter, curp, ine_frente_archivo, ine_frente_archivo_nombre_original, ine_reverso_archivo, ine_reverso_archivo_nombre_original, telefono, correo, creado_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            )->execute([
                $usuarioId,
                $t['nombre'],
                $t['caracter'],
                $t['curp'],
                $t['ine_frente_archivo'] ?? null,
                $t['ine_frente_archivo_nombre_original'] ?? null,
                $t['ine_reverso_archivo'] ?? null,
                $t['ine_reverso_archivo_nombre_original'] ?? null,
                $t['telefono'],
                $t['correo'],
            ]);
        }
    }
}

function eliminarTitularesDeVilla($usuarioId): void
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT ine_frente_archivo, ine_reverso_archivo FROM titulares WHERE usuario_id = ?');
    $stmt->execute([$usuarioId]);
    foreach ($stmt->fetchAll() as $t) {
        if ($t['ine_frente_archivo']) @unlink(rutaArchivoFisico($t['ine_frente_archivo']));
        if ($t['ine_reverso_archivo']) @unlink(rutaArchivoFisico($t['ine_reverso_archivo']));
    }
    $pdo->prepare('DELETE FROM titulares WHERE usuario_id = ?')->execute([$usuarioId]);
}
