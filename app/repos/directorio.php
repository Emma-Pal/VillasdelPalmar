<?php
require_once __DIR__ . '/../db.php';

// ===================================================================
// Personal del condominio (colaboradores) — expediente con dos niveles:
// "Datos generales" los ve cualquier propietario; todo lo demás (abajo,
// COLUMNAS_COLABORADOR) es solo para comité/administración.
// ===================================================================

const AREAS_COLABORADOR = ['administracion', 'seguridad', 'mantenimiento', 'jardineria', 'limpieza'];

function etiquetaAreaColaborador(string $area): string
{
    $etiquetas = [
        'administracion' => 'Administración',
        'seguridad' => 'Seguridad',
        'mantenimiento' => 'Mantenimiento',
        'jardineria' => 'Jardinería',
        'limpieza' => 'Limpieza',
    ];
    return $etiquetas[$area] ?? $area;
}

// Todas las columnas de colaboradores excepto id/autor_id/creado_en/
// actualizado_en — en este orden exacto se arman el INSERT y el UPDATE de
// crearColaborador()/actualizarColaborador(). Vienen de un arreglo (no de
// ~30 parámetros posicionales) para que sea manejable; los nombres de
// columna son fijos (esta constante), nunca las llaves que mande quien
// llame, así que es seguro usarlos directo en el SQL.
const COLUMNAS_COLABORADOR = [
    'foto', 'nombre', 'puesto', 'area', 'fecha_ingreso', 'estatus',
    'ine_archivo', 'ine_archivo_nombre_original',
    'curp_numero', 'curp_archivo', 'curp_archivo_nombre_original',
    'rfc_numero', 'rfc_archivo', 'rfc_archivo_nombre_original',
    'nss_numero', 'nss_archivo', 'nss_archivo_nombre_original',
    'fecha_nacimiento', 'lugar_nacimiento', 'nacionalidad', 'estado_civil',
    'telefono', 'correo', 'domicilio', 'domicilio_archivo', 'domicilio_archivo_nombre_original',
    'tipo_contrato', 'turno_horario', 'contrato_archivo', 'contrato_archivo_nombre_original',
    'emergencia_nombre', 'emergencia_parentesco', 'emergencia_telefono1', 'emergencia_telefono2', 'emergencia_domicilio',
];

// $buscar: coincidencia parcial en nombre o puesto.
function getColaboradores(?string $area = null, ?string $estatus = null, ?string $buscar = null): array
{
    $condiciones = [];
    $parametros = [];
    if ($area) {
        $condiciones[] = 'area = ?';
        $parametros[] = $area;
    }
    if ($estatus) {
        $condiciones[] = 'estatus = ?';
        $parametros[] = $estatus;
    }
    if ($buscar !== null && $buscar !== '') {
        $condiciones[] = '(nombre LIKE ? OR puesto LIKE ? OR area LIKE ?)';
        $parametros[] = "%$buscar%";
        $parametros[] = "%$buscar%";
        $parametros[] = "%$buscar%";
    }
    $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
    $stmt = db()->prepare("SELECT * FROM colaboradores $where ORDER BY (estatus = 'baja'), nombre");
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}

function getColaboradorPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM colaboradores WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

// $campos: arreglo asociativo con (algunas o todas) las llaves de
// COLUMNAS_COLABORADOR; las que falten se guardan como NULL.
function crearColaborador($autorId, array $campos): string
{
    $valores = array_map(fn($c) => $campos[$c] ?? null, COLUMNAS_COLABORADOR);
    $marcadores = implode(',', array_fill(0, count(COLUMNAS_COLABORADOR), '?'));

    $stmt = db()->prepare(
        'INSERT INTO colaboradores (' . implode(',', COLUMNAS_COLABORADOR) . ', autor_id, creado_en)
         VALUES (' . $marcadores . ', ?, ?)'
    );
    $stmt->execute([...$valores, $autorId, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

function actualizarColaborador($id, array $campos): void
{
    $valores = array_map(fn($c) => $campos[$c] ?? null, COLUMNAS_COLABORADOR);
    $sets = implode(', ', array_map(fn($c) => "$c = ?", COLUMNAS_COLABORADOR));

    $stmt = db()->prepare("UPDATE colaboradores SET $sets, actualizado_en = ? WHERE id = ?");
    $stmt->execute([...$valores, date('Y-m-d H:i:s'), $id]);
}

function eliminarColaborador($id): void
{
    $stmt = db()->prepare('DELETE FROM colaboradores WHERE id = ?');
    $stmt->execute([$id]);
}

// ===================================================================
// Integrantes del Comité — a diferencia de colaboradores, este directorio
// es público: lo ve cualquier propietario completo, sin restricciones.
// ===================================================================

const DEPARTAMENTOS_COMITE = ['comite_administracion', 'comite_vigilancia', 'contraloria'];

function etiquetaDepartamentoComite(string $departamento): string
{
    $etiquetas = [
        'comite_administracion' => 'Comité de Administración',
        'comite_vigilancia' => 'Comité de Vigilancia',
        'contraloria' => 'Contraloría',
    ];
    return $etiquetas[$departamento] ?? $departamento;
}

// Cargos sugeridos en el datalist del formulario — no es una lista cerrada
// (el campo es de texto libre), solo atajos para los más comunes.
function getCargosComiteSugeridos(): array
{
    return [
        'Presidente', 'Secretario', 'Secretaria', 'Tesorero', 'Tesorera', 'Vocal', 'Comisario',
        'Contralora', 'Contralor', 'Prosecretario', 'Obras y Mantenimiento',
        'Coordinación de Planeación', 'Coordinación de Servicios al Propietario',
        'Director de Relaciones', 'Directora de Relaciones',
    ];
}

function getComiteMiembros(?string $departamento = null, ?string $buscar = null): array
{
    $condiciones = [];
    $parametros = [];
    if ($departamento) {
        $condiciones[] = 'departamento = ?';
        $parametros[] = $departamento;
    }
    if ($buscar !== null && $buscar !== '') {
        $condiciones[] = '(nombre LIKE ? OR cargo LIKE ?)';
        $parametros[] = "%$buscar%";
        $parametros[] = "%$buscar%";
    }
    $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';
    $stmt = db()->prepare("SELECT * FROM comite_miembros $where ORDER BY departamento, titular_suplente DESC, nombre");
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}

function getComiteMiembroPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM comite_miembros WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

// Usada por /panel/usuarios para saber, por cada titular de una villa, si
// ya tiene una fila en comite_miembros — así esa fila muestra "Ya es del
// Comité" en vez de ofrecer el botón "+ Comité" otra vez.
function getComiteMiembroPorTitularId($titularId): ?array
{
    $stmt = db()->prepare('SELECT * FROM comite_miembros WHERE titular_id = ?');
    $stmt->execute([$titularId]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function crearComiteMiembro(
    $autorId,
    string $departamento,
    string $titularSuplente,
    string $nombre,
    ?string $villa,
    string $cargo,
    ?string $correo,
    ?string $telefono,
    ?string $periodoInicio,
    ?string $periodoFin,
    $titularId = null
): string {
    $stmt = db()->prepare(
        'INSERT INTO comite_miembros (departamento, titular_suplente, nombre, villa, titular_id, cargo, correo, telefono, periodo_inicio, periodo_fin, autor_id, creado_en)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$departamento, $titularSuplente, $nombre, $villa, $titularId ?: null, $cargo, $correo, $telefono, $periodoInicio, $periodoFin, $autorId, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

function actualizarComiteMiembro(
    $id,
    string $departamento,
    string $titularSuplente,
    string $nombre,
    ?string $villa,
    string $cargo,
    ?string $correo,
    ?string $telefono,
    ?string $periodoInicio,
    ?string $periodoFin
): void {
    $stmt = db()->prepare(
        'UPDATE comite_miembros SET departamento = ?, titular_suplente = ?, nombre = ?, villa = ?, cargo = ?, correo = ?, telefono = ?, periodo_inicio = ?, periodo_fin = ?, actualizado_en = ? WHERE id = ?'
    );
    $stmt->execute([$departamento, $titularSuplente, $nombre, $villa, $cargo, $correo, $telefono, $periodoInicio, $periodoFin, date('Y-m-d H:i:s'), $id]);
}

function eliminarComiteMiembro($id): void
{
    $stmt = db()->prepare('DELETE FROM comite_miembros WHERE id = ?');
    $stmt->execute([$id]);
}
