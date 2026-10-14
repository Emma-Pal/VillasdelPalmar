<?php
// Procesa el modal "Alta de villa" / "Editar villa" de /panel/usuarios
// (Villas). El login de una villa es su propio número — no
// hay un campo "usuario" aparte que capturar, así que "usuario" y "villa"
// siempre se guardan iguales.
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/usuarios');
    exit;
}

verificarCsrf();

const MAX_TITULARES = 30;

$id = (int) ($_POST['id'] ?? 0);
$esEdicion = $id > 0;
$villaActual = $esEdicion ? getVillaPorId($id) : null;

if ($esEdicion && !$villaActual) {
    header('Location: /panel/usuarios');
    exit;
}

$villaNumero = trim($_POST['villa_numero'] ?? '');
$password = trim($_POST['password_villa'] ?? '');
// Recámaras/capacidad son opcionales — en blanco se guardan como 0 (el
// mismo "Estudio" que ya usa capacidadPorRecamaras() como caso base).
$recamarasRegistradas = max(0, (int) ($_POST['recamaras_registradas'] ?? 0));
$recamarasFisicas = max(0, (int) ($_POST['recamaras_fisicas'] ?? 0));
$capacidadOcupacion = max(0, (int) ($_POST['capacidad_ocupacion'] ?? 0));

$error = null;

if ($villaNumero === '' || !ctype_digit($villaNumero)) {
    $error = 'El número de villa es obligatorio y solo puede contener dígitos.';
} elseif (!$esEdicion && $password === '') {
    $error = 'La contraseña asignada es obligatoria para una villa nueva.';
} elseif ($password !== '' && !passwordEsValida($password)) {
    $error = 'La contraseña debe tener al menos ' . MIN_LARGO_PASSWORD . ' caracteres.';
}

// ===== Titulares: campos planos indexados titular_nombre_1, titular_nombre_2,
// ... (no "titulares[][...]") para poder usar procesarArchivoSubido() tal
// cual con cada archivo. Se recorren los 30 posibles índices y solo se
// toman los que de verdad traen nombre. =====
$titularesData = [];
if ($error === null) {
    try {
        for ($i = 1; $i <= MAX_TITULARES; $i++) {
            $nombre = trim($_POST["titular_nombre_$i"] ?? '');
            if ($nombre === '') continue;

            $tId = (int) ($_POST["titular_id_$i"] ?? 0) ?: null;
            $caracter = $i === 1
                ? 'propietario'
                : (in_array($_POST["titular_caracter_$i"] ?? '', ['propietario', 'copropietario'], true) ? $_POST["titular_caracter_$i"] : 'copropietario');
            $curp = strtoupper(trim($_POST["titular_curp_$i"] ?? ''));
            $telefono = trim($_POST["titular_telefono_$i"] ?? '') ?: null;
            $correo = trim($_POST["titular_correo_$i"] ?? '') ?: null;

            $ineFrente = procesarArchivoSubido("titular_ine_frente_$i");
            $ineReverso = procesarArchivoSubido("titular_ine_reverso_$i");

            if ($i === 1) {
                if (!$telefono && !$correo) {
                    $error = 'Falta el teléfono o el correo del titular 1 (propietario) — se necesita al menos uno para poder contactarlo.';
                    break;
                }
            }

            $titularesData[] = [
                'id' => $tId,
                'nombre' => $nombre,
                'caracter' => $caracter,
                'curp' => $curp,
                'telefono' => $telefono,
                'correo' => $correo,
                'ine_frente_archivo' => $ineFrente['archivo'] ?? null,
                'ine_frente_archivo_nombre_original' => $ineFrente['archivo_nombre_original'] ?? null,
                'ine_reverso_archivo' => $ineReverso['archivo'] ?? null,
                'ine_reverso_archivo_nombre_original' => $ineReverso['archivo_nombre_original'] ?? null,
            ];
        }

        if ($error === null && count($titularesData) === 0) {
            $error = 'Debes capturar al menos al titular 1 (propietario).';
        }
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir un archivo.', $e->getMessage());
        exit;
    }
}

// ===== Documentos del inmueble (ambos opcionales) =====
$escritura = null;
$relacion = null;
if ($error === null) {
    try {
        $escritura = procesarArchivoSubido('escritura');
        $relacion = procesarArchivoSubido('relacion_copropietarios');
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir un archivo.', $e->getMessage());
        exit;
    }
}

if ($error !== null) {
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', $error);
    exit;
}

try {
    if ($esEdicion) {
        $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;
        actualizarVilla($id, $villaNumero, $titularesData[0]['nombre'], $passwordHash, $recamarasRegistradas, $recamarasFisicas, $capacidadOcupacion, $escritura, $relacion);
        reemplazarTitulares($id, $titularesData);
    } else {
        $nuevoId = crearVilla($villaNumero, $titularesData[0]['nombre'], password_hash($password, PASSWORD_BCRYPT), $recamarasRegistradas, $recamarasFisicas, $capacidadOcupacion, $escritura, $relacion);
        reemplazarTitulares($nuevoId, $titularesData);
    }
} catch (PDOException $e) {
    http_response_code(400);
    $mensaje = stripos($e->getMessage(), 'villa') !== false || stripos($e->getMessage(), 'usuario') !== false
        ? 'Ese número de villa ya está registrado.'
        : 'No se pudo guardar la villa. Revisa los datos.';
    renderError('No se pudo completar — Villas del Palmar', 'No se pudo guardar.', $mensaje);
    exit;
}

header('Location: /panel/usuarios');
exit;
