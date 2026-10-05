<?php
// Procesa el modal "Nuevo registro de estancia" de /panel/registro-estancia.
// Lo puede capturar un propietario (para su propia villa) o Administración
// (en nombre de cualquier villa).
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /panel/registro-estancia');
    exit;
}

verificarCsrf();

$esMesa = $usuario['tipo'] === 'mesa';

// El propietario NUNCA puede elegir otra villa aunque manipule el campo
// oculto desde el navegador — se ignora lo que mande y se usa su propia
// cuenta siempre. Solo Administración puede elegir libremente.
$villaUsuarioId = $esMesa ? (int) ($_POST['villa_usuario_id'] ?? 0) : (int) $usuario['id'];
$villa = getVillaPorId($villaUsuarioId);

$error = null;
if (!$villa) {
    $error = 'Selecciona una villa o departamento válido.';
}

$fechaLlegada = trim($_POST['fecha_llegada'] ?? '');
$horaLlegada = trim($_POST['hora_llegada'] ?? '');
$fechaSalida = trim($_POST['fecha_salida'] ?? '');
$horaSalida = trim($_POST['hora_salida'] ?? '');
$responsableNombre = trim($_POST['responsable_nombre'] ?? '');
$responsableTelefono = trim($_POST['responsable_telefono'] ?? '') ?: null;
$adultos = (int) ($_POST['adultos'] ?? 0);
$menores = (int) ($_POST['menores'] ?? 0);
$infantes = (int) ($_POST['infantes'] ?? 0);
$firmaNombre = trim($_POST['firma_nombre'] ?? '');
$comentarios = trim($_POST['comentarios'] ?? '') ?: null;
$reglamentoAceptado = !empty($_POST['reglamento_aceptado']);
$danosAceptado = !empty($_POST['danos_aceptado']);
$multasAceptado = !empty($_POST['multas_aceptado']);
$excedenteAceptadoPost = !empty($_POST['excedente_aceptado']);

$patronHora = '/^([01]\d|2[0-3]):[0-5]\d$/';

if ($error === null) {
    if ($fechaLlegada === '' || $horaLlegada === '' || $fechaSalida === '' || $horaSalida === '') {
        $error = 'Faltan las fechas u horas de llegada/salida.';
    } elseif (!preg_match($patronHora, $horaLlegada) || !preg_match($patronHora, $horaSalida)) {
        $error = 'La hora debe tener formato HH:MM en 24 horas.';
    } elseif (strtotime($fechaSalida) < strtotime($fechaLlegada)) {
        $error = 'La fecha de salida no puede ser antes de la fecha de llegada.';
    } elseif ($responsableNombre === '' || $firmaNombre === '') {
        $error = 'Faltan el nombre del responsable o la firma.';
    } elseif ($adultos < 1) {
        $error = 'Siempre debe haber al menos un adulto responsable.';
    } elseif (!$reglamentoAceptado || !$danosAceptado || !$multasAceptado) {
        $error = 'Debes aceptar los 3 compromisos del responsable.';
    }
}

// ===== Identificación del responsable (obligatoria) =====
$responsableIdArchivo = null;
if ($error === null) {
    try {
        $responsableIdArchivo = procesarArchivoSubido('responsable_identificacion');
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }
    if (!$responsableIdArchivo) {
        $error = 'Falta la identificación del responsable.';
    }
}

// ===== Acompañantes: campos planos indexados (acompanante_nombre_1, ...) =====
$acompanantes = [];
if ($error === null) {
    for ($i = 1; $i <= 30; $i++) {
        $nombre = trim($_POST["acompanante_nombre_$i"] ?? '');
        if ($nombre === '') continue;
        $tipo = in_array($_POST["acompanante_tipo_$i"] ?? '', ['adulto', 'menor', 'infante'], true) ? $_POST["acompanante_tipo_$i"] : 'adulto';
        $acompanantes[] = ['nombre' => $nombre, 'tipo' => $tipo];
    }
}

// ===== Vehículos (opcional — "Sin vehículo" los omite todos) =====
$vehiculos = [];
$tieneVehiculo = ($_POST['tiene_vehiculo'] ?? 'no') === 'si';
if ($error === null && $tieneVehiculo) {
    for ($i = 1; $i <= 10; $i++) {
        $placas = trim($_POST["vehiculo_placas_$i"] ?? '');
        if ($placas === '') continue;
        $tipo = trim($_POST["vehiculo_tipo_$i"] ?? 'automovil');
        $vehiculos[] = ['placas' => strtoupper($placas), 'tipo' => $tipo];
    }
    if (count($vehiculos) === 0) {
        $error = 'Indica al menos las placas de un vehículo, o marca "Sin vehículo".';
    }
}

// ===== Mascota (opcional, un solo registro — no es repetible, "Cantidad" cubre varias de la misma) =====
$tieneMascota = ($_POST['tiene_mascota'] ?? 'no') === 'si';
$mascotaTipo = null;
$mascotaRazaTamano = null;
$mascotaCantidad = null;
if ($tieneMascota) {
    $mascotaTipo = trim($_POST['mascota_tipo'] ?? '') ?: 'otro';
    $mascotaRazaTamano = trim($_POST['mascota_raza_tamano'] ?? '') ?: null;
    $mascotaCantidad = max(1, (int) ($_POST['mascota_cantidad'] ?? 1));
}

// ===== Cálculo de ocupación/excedente — autoritativo en el servidor, nunca
// se confía en lo que haya mostrado el JS en el navegador. =====
if ($error === null) {
    $capacidad = capacidadPorRecamaras((int) $villa['recamaras_registradas']);
    $calculo = calcularOcupacionEstancia($capacidad, $adultos, $menores);
    $hayExcedente = $calculo['adultos_excedentes'] > 0 || $calculo['menores_excedentes'] > 0;

    if ($hayExcedente && !$excedenteAceptadoPost) {
        $error = 'Debes aceptar la cuota por excedente de ocupación para continuar.';
    }
}

if ($error !== null) {
    http_response_code(400);
    renderError('No se pudo completar — Villas del Palmar', 'Faltan datos.', $error);
    exit;
}

$noches = calcularNochesEstancia($fechaLlegada, $fechaSalida);
$cuotaExcedente = $hayExcedente ? calcularCuotaExcedenteEstancia($calculo['adultos_excedentes'], $calculo['menores_excedentes'], $noches) : 0;

crearEstancia([
    'usuario_id' => $villaUsuarioId,
    'autor_id' => $usuario['id'],
    'responsable_nombre' => $responsableNombre,
    'responsable_telefono' => $responsableTelefono,
    'responsable_id_archivo' => $responsableIdArchivo['archivo'],
    'responsable_id_archivo_nombre_original' => $responsableIdArchivo['archivo_nombre_original'],
    'fecha_llegada' => $fechaLlegada,
    'hora_llegada' => $horaLlegada,
    'fecha_salida' => $fechaSalida,
    'hora_salida' => $horaSalida,
    'adultos' => $adultos,
    'menores' => $menores,
    'infantes' => $infantes,
    'capacidad_villa' => $capacidad,
    'ocupacion_equivalente' => $calculo['ocupacion_equivalente'],
    'adultos_excedentes' => $calculo['adultos_excedentes'],
    'menores_excedentes' => $calculo['menores_excedentes'],
    'cuota_excedente' => $cuotaExcedente,
    'mascota_tipo' => $mascotaTipo,
    'mascota_raza_tamano' => $mascotaRazaTamano,
    'mascota_cantidad' => $mascotaCantidad,
    'reglamento_aceptado' => $reglamentoAceptado,
    'danos_aceptado' => $danosAceptado,
    'multas_aceptado' => $multasAceptado,
    'excedente_aceptado' => $hayExcedente ? $excedenteAceptadoPost : false,
    'firma_nombre' => $firmaNombre,
    'comentarios' => $comentarios,
], $acompanantes, $vehiculos);

header('Location: /panel/registro-estancia');
exit;
