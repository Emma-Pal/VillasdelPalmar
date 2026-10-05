<?php
require_once __DIR__ . '/../db.php';

const CUOTA_ADULTO_EXCEDENTE = 150.00;
const CUOTA_MENOR_EXCEDENTE = 75.00;

// El folio que ve el propietario es solo el id con prefijo — mismo patrón
// que folioSolicitud() en app/repos/solicitudes.php, no hace falta guardarlo
// aparte.
function folioEstancia($id): string
{
    return 'RE-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
}

// Reparte la capacidad entre adultos y menores (1 adulto = 1 equivalente,
// 1 menor = 1/2, los infantes no cuentan) — primero entran los adultos y
// luego los menores; lo que no cabe es excedente. Mismo criterio que la
// versión en JS (ver main.js) — si uno cambia, el otro también.
function calcularOcupacionEstancia(int $capacidad, int $adultos, int $menores): array
{
    $ocupacionEquivalente = $adultos + $menores * 0.5;

    if ($adultos > $capacidad) {
        $adultosExcedentes = $adultos - $capacidad;
        $menoresExcedentes = $menores;
    } else {
        $adultosExcedentes = 0;
        $equivalentesLibres = $capacidad - $adultos;
        $menoresQueCaben = (int) floor($equivalentesLibres * 2);
        $menoresExcedentes = max(0, $menores - $menoresQueCaben);
    }

    return [
        'ocupacion_equivalente' => $ocupacionEquivalente,
        'adultos_excedentes' => $adultosExcedentes,
        'menores_excedentes' => $menoresExcedentes,
    ];
}

function calcularNochesEstancia(string $fechaLlegada, string $fechaSalida): int
{
    $noches = (int) round((strtotime($fechaSalida) - strtotime($fechaLlegada)) / 86400);
    return max(1, $noches);
}

function calcularCuotaExcedenteEstancia(int $adultosExcedentes, int $menoresExcedentes, int $noches): float
{
    return $adultosExcedentes * CUOTA_ADULTO_EXCEDENTE * $noches + $menoresExcedentes * CUOTA_MENOR_EXCEDENTE * $noches;
}

function crearEstancia(array $datos, array $acompanantes, array $vehiculos): string
{
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO estancias (
            usuario_id, autor_id, responsable_nombre, responsable_telefono,
            responsable_id_archivo, responsable_id_archivo_nombre_original,
            fecha_llegada, hora_llegada, fecha_salida, hora_salida,
            adultos, menores, infantes, capacidad_villa, ocupacion_equivalente,
            adultos_excedentes, menores_excedentes, cuota_excedente,
            mascota_tipo, mascota_raza_tamano, mascota_cantidad,
            reglamento_aceptado, danos_aceptado, multas_aceptado, excedente_aceptado,
            firma_nombre, comentarios, estatus, creado_en
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'en_revision\', NOW())'
    );
    $stmt->execute([
        $datos['usuario_id'],
        $datos['autor_id'],
        $datos['responsable_nombre'],
        $datos['responsable_telefono'],
        $datos['responsable_id_archivo'],
        $datos['responsable_id_archivo_nombre_original'],
        $datos['fecha_llegada'],
        $datos['hora_llegada'],
        $datos['fecha_salida'],
        $datos['hora_salida'],
        $datos['adultos'],
        $datos['menores'],
        $datos['infantes'],
        $datos['capacidad_villa'],
        $datos['ocupacion_equivalente'],
        $datos['adultos_excedentes'],
        $datos['menores_excedentes'],
        $datos['cuota_excedente'],
        $datos['mascota_tipo'],
        $datos['mascota_raza_tamano'],
        $datos['mascota_cantidad'],
        $datos['reglamento_aceptado'] ? 1 : 0,
        $datos['danos_aceptado'] ? 1 : 0,
        $datos['multas_aceptado'] ? 1 : 0,
        $datos['excedente_aceptado'] ? 1 : 0,
        $datos['firma_nombre'],
        $datos['comentarios'],
    ]);
    $id = $pdo->lastInsertId();

    foreach ($acompanantes as $a) {
        $pdo->prepare('INSERT INTO estancia_acompanantes (estancia_id, nombre, tipo) VALUES (?, ?, ?)')
            ->execute([$id, $a['nombre'], $a['tipo']]);
    }
    foreach ($vehiculos as $v) {
        $pdo->prepare('INSERT INTO estancia_vehiculos (estancia_id, placas, tipo) VALUES (?, ?, ?)')
            ->execute([$id, $v['placas'], $v['tipo']]);
    }

    return $id;
}

function getEstanciaPorId($id): ?array
{
    $stmt = db()->prepare(
        'SELECT e.*, u.villa AS villa_numero, u.nombre AS villa_propietario
         FROM estancias e JOIN usuarios u ON u.id = e.usuario_id
         WHERE e.id = ?'
    );
    $stmt->execute([$id]);
    $estancia = $stmt->fetch();
    if (!$estancia) return null;

    $stmt = db()->prepare('SELECT * FROM estancia_acompanantes WHERE estancia_id = ? ORDER BY id ASC');
    $stmt->execute([$id]);
    $estancia['acompanantes'] = $stmt->fetchAll();

    $stmt = db()->prepare('SELECT * FROM estancia_vehiculos WHERE estancia_id = ? ORDER BY id ASC');
    $stmt->execute([$id]);
    $estancia['vehiculos'] = $stmt->fetchAll();

    return $estancia;
}

// "Mis próximas estancias" / "Historial" de una villa — ver registro-estancia/index.php.
// Trae el primer vehículo de cada una (es lo único que muestra esa tarjeta;
// el detalle completo, con todos, lo carga getEstanciaPorId()).
function getEstanciasDeVilla($usuarioId): array
{
    $stmt = db()->prepare('SELECT * FROM estancias WHERE usuario_id = ? ORDER BY fecha_llegada DESC, id DESC');
    $stmt->execute([$usuarioId]);
    $estancias = $stmt->fetchAll();
    foreach ($estancias as &$e) {
        $stmtV = db()->prepare('SELECT * FROM estancia_vehiculos WHERE estancia_id = ? ORDER BY id ASC LIMIT 1');
        $stmtV->execute([$e['id']]);
        $primerVehiculo = $stmtV->fetch();
        $e['vehiculos'] = $primerVehiculo ? [$primerVehiculo] : [];
    }
    unset($e);
    return $estancias;
}

// Vista de Administración: todas las villas, con filtro opcional de estatus.
function getEstancias(?string $estatus = null): array
{
    if ($estatus) {
        $stmt = db()->prepare(
            'SELECT e.*, u.villa AS villa_numero, u.nombre AS villa_propietario
             FROM estancias e JOIN usuarios u ON u.id = e.usuario_id
             WHERE e.estatus = ? ORDER BY e.fecha_llegada DESC, e.id DESC'
        );
        $stmt->execute([$estatus]);
        return $stmt->fetchAll();
    }
    return db()->query(
        'SELECT e.*, u.villa AS villa_numero, u.nombre AS villa_propietario
         FROM estancias e JOIN usuarios u ON u.id = e.usuario_id
         ORDER BY e.fecha_llegada DESC, e.id DESC'
    )->fetchAll();
}

function actualizarEstatusEstancia($id, string $estatus): void
{
    db()->prepare('UPDATE estancias SET estatus = ?, actualizado_en = NOW() WHERE id = ?')->execute([$estatus, $id]);
}
