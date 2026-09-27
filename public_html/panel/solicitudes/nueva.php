<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Nueva solicitud — Villas del Palmar';
$description = 'Levanta una queja, falla o sugerencia en Villas del Palmar.';

$folioGenerado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $tipo = in_array($_POST['tipo'] ?? '', TIPOS_SOLICITUD, true) ? $_POST['tipo'] : 'falla';
    $asunto = trim($_POST['asunto'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '') ?: null;

    if ($asunto !== '' && $descripcion !== '') {
        $id = crearSolicitud($usuario['id'], $tipo, $asunto, $descripcion, $ubicacion);
        $folioGenerado = folioSolicitud($id);
    }
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
      <span class="eyebrow">Reportar algo</span>
      <h1>Nueva solicitud</h1>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 560px; margin: 0 auto;">

      <?php if ($folioGenerado): ?>
        <div class="folio-confirmacion">
          <span class="eyebrow">Solicitud recibida</span>
          <p class="folio-numero"><?= htmlspecialchars($folioGenerado) ?></p>
          <p>Anota este folio — con él puedes <a href="/panel/solicitudes/consultar">consultar el estatus</a> más adelante.</p>
          <a href="/panel/solicitudes" class="btn btn-primary">Volver a Solicitudes</a>
        </div>
      <?php else: ?>
        <form action="/panel/solicitudes/nueva" method="POST" class="contact-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

          <label>
            Tipo
            <select name="tipo" required>
              <?php foreach (TIPOS_SOLICITUD as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars(etiquetaTipoSolicitud($t)) ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <label>
            Asunto
            <input type="text" name="asunto" placeholder="ej. Fuga de agua en área común" required />
          </label>

          <label>
            Ubicación (opcional)
            <input type="text" name="ubicacion" placeholder="ej. Edificio 3, área de albercas" />
          </label>

          <label>
            Descripción
            <textarea name="descripcion" rows="5" placeholder="Cuéntanos con detalle qué pasó..." required></textarea>
          </label>

          <button type="submit" class="btn btn-primary">Enviar solicitud</button>
        </form>
      <?php endif; ?>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
