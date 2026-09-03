<?php
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$title = 'Comité — Villas del Palmar';
$description = 'Integrantes del comité administrativo de Villas del Palmar.';
$mesa = getMesa();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <span class="eyebrow">Quién es quién</span>
      <h1>Comité</h1>
      <p class="page-banner-lead">Integrantes actuales y su cargo.</p>
    </div>
  </section>

  <section class="detail-sections">
    <div class="amenities-grid" data-reveal>
      <?php foreach ($mesa as $m): ?>
        <div class="amenity-card">
          <div class="amenity-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <h3><?= htmlspecialchars($m['nombre']) ?></h3>
          <p><?= htmlspecialchars($m['cargo']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
