<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Galería · Conoce Villas del Palmar';
$description = 'Fotografías de las instalaciones y amenidades de Villas del Palmar, con sus horarios.';
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
      <span class="eyebrow">Conoce Villas del Palmar</span>
      <h1>Galería</h1>
      <p class="page-banner-lead">Fotografías de las instalaciones y amenidades del residencial, con sus horarios.</p>
    </div>
  </section>

  <section class="gallery" style="max-width: var(--max-width); margin: 0 auto; padding: 40px 24px 96px;">
    <?php if ($usuario['tipo'] === 'mesa'): ?>
      <p style="text-align: right; margin-bottom: 16px;">
        <a href="/panel/galeria/admin" class="btn btn-ghost-light">Administrar galería</a>
      </p>
    <?php endif; ?>
    <div class="gallery-grid" data-reveal>
      <a href="/panel/galeria/alberca" class="gallery-item gallery-item--wide">
        <span class="gallery-thumb thumb-1"></span>
        <span class="gallery-caption">Alberca &amp; terraza <em>Ver más →</em></span>
      </a>
      <a href="/panel/galeria/areas-verdes" class="gallery-item">
        <span class="gallery-thumb thumb-2"></span>
        <span class="gallery-caption">Áreas verdes <em>Ver más →</em></span>
      </a>
      <a href="/panel/galeria/departamentos" class="gallery-item">
        <span class="gallery-thumb thumb-3"></span>
        <span class="gallery-caption">Departamentos <em>Ver más →</em></span>
      </a>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
