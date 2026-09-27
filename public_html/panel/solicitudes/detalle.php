<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

$solicitud = getSolicitudPorId((int) ($_GET['id'] ?? 0));
if (!$solicitud) {
    header('Location: /panel/solicitudes');
    exit;
}

$title = 'Solicitud ' . folioSolicitud($solicitud['id']) . ' — Villas del Palmar';
$description = 'Detalle de una solicitud de Villas del Palmar.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $estatusValidos = ['pendiente', 'en_progreso', 'resuelto'];
    $estatus = in_array($_POST['estatus'] ?? '', $estatusValidos, true) ? $_POST['estatus'] : $solicitud['estatus'];
    $respuesta = trim($_POST['respuesta'] ?? '') ?: null;

    actualizarEstatusSolicitud($solicitud['id'], $estatus, $respuesta);
    header('Location: /panel/solicitudes/detalle?id=' . $solicitud['id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <a href="/panel/solicitudes" class="back-link">← Volver a solicitudes</a>
      <span class="eyebrow"><?= htmlspecialchars(etiquetaTipoSolicitud($solicitud['tipo'])) ?></span>
      <h1><?= htmlspecialchars(folioSolicitud($solicitud['id'])) ?></h1>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 620px; margin: 0 auto;">
      <h2 style="margin-bottom: 4px;"><?= htmlspecialchars($solicitud['asunto']) ?></h2>
      <p class="form-nota">
        Reportado por <?= htmlspecialchars($solicitud['autor_nombre']) ?> el <?= htmlspecialchars(date('d/m/Y', strtotime($solicitud['creado_en']))) ?>
        <?php if (!empty($solicitud['ubicacion'])): ?> · <?= htmlspecialchars($solicitud['ubicacion']) ?><?php endif; ?>
      </p>
      <p style="margin: 16px 0;"><?= nl2brSeguro($solicitud['descripcion']) ?></p>

      <form action="/panel/solicitudes/detalle?id=<?= (int) $solicitud['id'] ?>" method="POST" class="contact-form">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

        <label>
          Estatus
          <select name="estatus" required>
            <option value="pendiente" <?= $solicitud['estatus'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
            <option value="en_progreso" <?= $solicitud['estatus'] === 'en_progreso' ? 'selected' : '' ?>>En progreso</option>
            <option value="resuelto" <?= $solicitud['estatus'] === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
          </select>
        </label>

        <label>
          Respuesta (visible para quien consulte el folio)
          <textarea name="respuesta" rows="4"><?= htmlspecialchars($solicitud['respuesta'] ?? '') ?></textarea>
        </label>

        <button type="submit" class="btn btn-primary">Guardar</button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
