<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Áreas verdes — Villas del Palmar';
$description = 'Jardines y espacios abiertos de Villas del Palmar.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../partials/portal-header.php'; ?>

  <!-- ===== Banner ===== -->
  <section class="page-banner" style="background-image: url('/images/galeria/areas-verdes.jpg');">
    <div class="page-banner-content">
      <a href="/panel/instalaciones" class="back-link">← Volver a instalaciones</a>
      <span class="eyebrow">Espacios comunes</span>
      <h1>Áreas verdes</h1>
      <p class="page-banner-lead">
        Jardines, senderos y espacios abiertos que conectan cada rincón del residencial.
      </p>
    </div>
  </section>

  <!-- ===== Detalle ===== -->
  <section class="detail-sections">
    <div class="detail-block" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/jardin-central.jpg" alt="Jardín central con palmeras y andadores" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">El corazón del residencial</span>
        <h2>Jardines centrales</h2>
        <p>
          Vegetación tropical y palmeras enmarcan los andadores que conectan los edificios entre sí,
          dando sombra y frescura durante todo el año.
        </p>
        <ul class="feature-list">
          <li>Vegetación tropical</li>
          <li>Mantenimiento constante</li>
          <li>Sombra natural</li>
        </ul>
      </div>
    </div>

    <div class="detail-block is-reverse" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/jardin-sendero.jpg" alt="Sendero de piedra entre jardines" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Para caminar</span>
        <h2>Andadores y senderos</h2>
        <p>
          Caminos de piedra entre jardines, perfectos para caminar, pasear a tu mascota o simplemente
          dar un paseo tranquilo por las tardes.
        </p>
        <ul class="feature-list">
          <li>Pet friendly</li>
          <li>Iluminación nocturna</li>
          <li>Superficie antiderrapante</li>
        </ul>
      </div>
    </div>

    <div class="detail-block" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/areas-verdes.jpg" alt="Explanada abierta de césped bajo las palmeras" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Al aire libre</span>
        <h2>Explanada abierta</h2>
        <p>
          Un amplio espacio de césped bajo las palmeras, ideal para actividades al aire libre
          o simplemente estirarse en el pasto.
        </p>
        <ul class="feature-list">
          <li>Espacio abierto para actividades</li>
          <li>Ideal para niños</li>
          <li>Rodeado de palmeras</li>
        </ul>
      </div>
    </div>
  </section>

  <div class="detail-cta" data-reveal>
    <a href="/panel/instalaciones" class="back-link back-link--dark" style="justify-content: center; margin: 0 auto;">← Volver a instalaciones</a>
  </div>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
