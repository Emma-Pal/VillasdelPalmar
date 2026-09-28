<?php
// Compartido por nuevo.php y editar.php.

$esEdicion = isset($_GET['id']);
$acuerdoEditado = null;

if ($esEdicion) {
    $acuerdoEditado = getAcuerdoPorId((int) $_GET['id']);
    if (!$acuerdoEditado) {
        header('Location: /panel/asambleas');
        exit;
    }
}

$title = ($esEdicion ? 'Editar acuerdo' : 'Nuevo acuerdo') . ' — Villas del Palmar';
$description = 'Acuerdos de asamblea de Villas del Palmar.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $descripcion = trim($_POST['descripcion'] ?? '');
    $estatus = in_array($_POST['estatus'] ?? '', ESTATUS_ACUERDO, true) ? $_POST['estatus'] : 'pendiente';
    $fechaLimite = trim($_POST['fecha_limite'] ?? '') ?: null;

    if ($esEdicion) {
        actualizarAcuerdo($acuerdoEditado['id'], $descripcion, $estatus, $fechaLimite);
    } else {
        crearAcuerdo($usuario['id'], $descripcion, $estatus, $fechaLimite);
    }
    header('Location: /panel/asambleas#acuerdos');
    exit;
}

$datos = $esEdicion ? $acuerdoEditado : ['descripcion' => '', 'estatus' => 'pendiente', 'fecha_limite' => ''];
$accionFormulario = $esEdicion ? '/panel/asambleas/acuerdos/editar?id=' . (int) $acuerdoEditado['id'] : '/panel/asambleas/acuerdos/nuevo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <a href="/panel/asambleas" class="back-link">← Volver a asambleas</a>
      <span class="eyebrow">Comité</span>
      <h1><?= $esEdicion ? 'Editar acuerdo' : 'Nuevo acuerdo' ?></h1>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 560px; margin: 0 auto;">
      <form action="<?= htmlspecialchars($accionFormulario) ?>" method="POST" class="contact-form">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

        <label>
          Descripción del acuerdo
          <textarea name="descripcion" rows="4" required><?= htmlspecialchars($datos['descripcion']) ?></textarea>
        </label>

        <label>
          Estatus
          <select name="estatus" required>
            <?php foreach (ESTATUS_ACUERDO as $est): ?>
              <option value="<?= htmlspecialchars($est) ?>" <?= $datos['estatus'] === $est ? 'selected' : '' ?>><?= htmlspecialchars(etiquetaEstatus($est)) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Fecha límite (opcional)
          <input type="date" name="fecha_limite" value="<?= htmlspecialchars($datos['fecha_limite'] ?? '') ?>" />
        </label>

        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Crear acuerdo' ?></button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../../partials/footer.php'; ?>

</body>
</html>
