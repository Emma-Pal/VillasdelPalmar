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

// Villas y departamentos (cuentas tipo='propietario'), cada una con su
// arreglo de titulares ya cargado — mismo patrón que getPublicaciones()
// adjuntando 'archivos' con getArchivosDePublicacion() por fila.
function getVillas(?string $buscar = null): array
{
    if ($buscar !== null && $buscar !== '') {
        $stmt = db()->prepare(
            "SELECT DISTINCT u.* FROM usuarios u LEFT JOIN titulares t ON t.usuario_id = u.id
             WHERE u.tipo = 'propietario' AND (u.villa LIKE ? OR u.nombre LIKE ? OR t.nombre LIKE ?)
             ORDER BY u.villa + 0 ASC"
        );
        $comodin = '%' . $buscar . '%';
        $stmt->execute([$comodin, $comodin, $comodin]);
    } else {
        $stmt = db()->query("SELECT * FROM usuarios WHERE tipo = 'propietario' ORDER BY villa + 0 ASC");
    }
    $villas = $stmt->fetchAll();
    foreach ($villas as &$v) {
        $v['titulares'] = getTitularesDeVilla($v['id']);
    }
    return $villas;
}

function getVillaPorId($id): ?array
{
    $stmt = db()->prepare("SELECT * FROM usuarios WHERE id = ? AND tipo = 'propietario'");
    $stmt->execute([$id]);
    $villa = $stmt->fetch();
    if (!$villa) return null;
    $villa['titulares'] = getTitularesDeVilla($villa['id']);
    return $villa;
}

function crearVilla(
    string $villa,
    string $nombrePropietario,
    string $passwordHash,
    ?int $recamarasRegistradas,
    ?int $recamarasFisicas,
    ?int $capacidad,
    ?array $escritura,
    ?array $relacion
): string {
    $stmt = db()->prepare(
        'INSERT INTO usuarios (tipo, nombre, villa, usuario, password_hash, recamaras_registradas, recamaras_fisicas, capacidad_ocupacion, escritura_archivo, escritura_archivo_nombre_original, relacion_copropietarios_archivo, relacion_copropietarios_archivo_nombre_original)
         VALUES (\'propietario\', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $nombrePropietario,
        $villa,
        $villa, // el login es el número de villa — no hay un "usuario" aparte que capturar
        $passwordHash,
        $recamarasRegistradas,
        $recamarasFisicas,
        $capacidad,
        $escritura['archivo'] ?? null,
        $escritura['archivo_nombre_original'] ?? null,
        $relacion['archivo'] ?? null,
        $relacion['archivo_nombre_original'] ?? null,
    ]);
    return db()->lastInsertId();
}

// $passwordHash, $escritura y $relacion en null = conservar lo que ya había.
function actualizarVilla(
    $id,
    string $villa,
    string $nombrePropietario,
    ?string $passwordHash,
    ?int $recamarasRegistradas,
    ?int $recamarasFisicas,
    ?int $capacidad,
    ?array $escritura,
    ?array $relacion
): void {
    $campos = ['nombre = ?', 'villa = ?', 'usuario = ?', 'recamaras_registradas = ?', 'recamaras_fisicas = ?', 'capacidad_ocupacion = ?'];
    $valores = [$nombrePropietario, $villa, $villa, $recamarasRegistradas, $recamarasFisicas, $capacidad];

    if ($passwordHash) {
        $campos[] = 'password_hash = ?';
        $valores[] = $passwordHash;
    }
    if ($escritura || $relacion) {
        $actual = getVillaPorId($id);
        if ($escritura && $actual && $actual['escritura_archivo']) {
            @unlink(rutaArchivoFisico($actual['escritura_archivo']));
        }
        if ($relacion && $actual && $actual['relacion_copropietarios_archivo']) {
            @unlink(rutaArchivoFisico($actual['relacion_copropietarios_archivo']));
        }
    }
    if ($escritura) {
        $campos[] = 'escritura_archivo = ?';
        $campos[] = 'escritura_archivo_nombre_original = ?';
        $valores[] = $escritura['archivo'];
        $valores[] = $escritura['archivo_nombre_original'];
    }
    if ($relacion) {
        $campos[] = 'relacion_copropietarios_archivo = ?';
        $campos[] = 'relacion_copropietarios_archivo_nombre_original = ?';
        $valores[] = $relacion['archivo'];
        $valores[] = $relacion['archivo_nombre_original'];
    }
    $valores[] = $id;

    db()->prepare('UPDATE usuarios SET ' . implode(', ', $campos) . " WHERE id = ? AND tipo = 'propietario'")->execute($valores);
}

function crearUsuario(string $tipo, string $nombre, ?string $cargo, string $usuario, string $passwordHash, ?string $villa = null): string
{
    $cargoFinal = $tipo === 'mesa' ? ($cargo ?: null) : null;
    $villaFinal = $tipo === 'propietario' ? ($villa ?: null) : null;
    $stmt = db()->prepare(
        'INSERT INTO usuarios (tipo, nombre, cargo, villa, usuario, password_hash) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$tipo, $nombre, $cargoFinal, $villaFinal, $usuario, $passwordHash]);
    return db()->lastInsertId();
}

// $passwordHash es opcional (null) — si no se manda, se conserva la actual
// (así "editar" no obliga a resetear la clave).
function actualizarUsuario($id, string $tipo, string $nombre, ?string $cargo, string $usuario, ?string $passwordHash = null, ?string $villa = null): void
{
    $cargoFinal = $tipo === 'mesa' ? ($cargo ?: null) : null;
    $villaFinal = $tipo === 'propietario' ? ($villa ?: null) : null;
    if ($passwordHash) {
        $stmt = db()->prepare(
            'UPDATE usuarios SET tipo = ?, nombre = ?, cargo = ?, villa = ?, usuario = ?, password_hash = ? WHERE id = ?'
        );
        $stmt->execute([$tipo, $nombre, $cargoFinal, $villaFinal, $usuario, $passwordHash, $id]);
    } else {
        $stmt = db()->prepare(
            'UPDATE usuarios SET tipo = ?, nombre = ?, cargo = ?, villa = ?, usuario = ? WHERE id = ?'
        );
        $stmt->execute([$tipo, $nombre, $cargoFinal, $villaFinal, $usuario, $id]);
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
