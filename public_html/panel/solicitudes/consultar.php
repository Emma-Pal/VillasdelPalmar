<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Consultar folio — Villas del Palmar';
$description = 'Consulta el estatus de una solicitud en Villas del Palmar.';

$solicitud = null;
$noEncontrada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $folioTexto = trim($_POST['folio'] ?? '');
    $id = idDesdeFolio($folioTexto);
    $solicitud = $id ? getSolicitudPorId($id) : null;
    $noEncontrada = !$solicitud;
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
      <span class="eyebrow">Dar seguimiento</span>
      <h1>Consultar un folio</h1>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 480px; margin: 0 auto;">
      <?php if ($noEncontrada): ?>
        <p class="login-error">No encontramos ninguna solicitud con ese folio.</p>
      <?php endif; ?>

      <form action="/panel/solicitudes/consultar" method="POST" class="contact-form">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
        <label>
          Folio
          <input type="text" name="folio" placeholder="ej. SOL-000042" value="<?= isset($_POST['folio']) ? htmlspecialchars($_POST['folio']) : '' ?>" required autofocus />
        </label>
        <button type="submit" class="btn btn-primary">Consultar</button>
      </form>

      <?php if ($solicitud): ?>
        <div class="folio-resultado">
          <span class="estatus-pill estatus-pill--<?= htmlspecialchars($solicitud['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($solicitud['estatus'])) ?></span>
          <h3><?= htmlspecialchars($solicitud['asunto']) ?></h3>
          <p class="form-nota">Reportada el <?= htmlspecialchars(date('d/m/Y', strtotime($solicitud['creado_en']))) ?></p>
          <?php if (!empty($solicitud['respuesta'])): ?>
            <p><strong>Respuesta:</strong><br /><?= nl2brSeguro($solicitud['respuesta']) ?></p>
          <?php else: ?>
            <p class="placeholder-note">Todavía no hay una respuesta registrada.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
