<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Alberca & terraza — Villas del Palmar';
$description = 'Las tres albercas de Villas del Palmar.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../partials/portal-header.php'; ?>

  <!-- ===== Banner ===== -->
  <section class="page-banner" style="background-image: url('/images/galeria/alberca-tobogan.jpg');">
    <div class="page-banner-content">
      <a href="/panel/instalaciones" class="back-link">← Volver a instalaciones</a>
      <span class="eyebrow">Espacios comunes</span>
      <h1>Alberca &amp; terraza</h1>
      <p class="page-banner-lead">
        Tres espacios distintos para nadar, tomar el sol o simplemente desconectar sin salir del residencial.
      </p>
    </div>
  </section>

  <!-- ===== Detalle ===== -->
  <section class="detail-sections">
    <div class="detail-block" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/alberca-tobogan.jpg" alt="Alberca con tobogán de piedra con vista al mar" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Para todas las edades</span>
        <h2>Alberca con tobogán</h2>
        <p>
          Tallada en roca natural y con vista directa al mar, es de las amenidades más queridas por chicos y grandes.
          Ideal para las tardes de sol en familia.
        </p>
        <ul class="feature-list">
          <li>Tobogán de piedra natural</li>
          <li>Vista directa al mar</li>
          <li>Zona de camastros junto a la alberca</li>
        </ul>
      </div>
    </div>

    <div class="detail-block is-reverse" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/alberca-infinita.jpg" alt="Alberca infinita con vista a la bahía" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Con vista</span>
        <h2>Alberca infinita</h2>
        <p>
          De borde infinito, se funde con el horizonte de la bahía. Perfecta para nadar viendo el atardecer
          o simplemente relajarte con esa vista de frente.
        </p>
        <ul class="feature-list">
          <li>Vista panorámica a la bahía</li>
          <li>Ideal para el atardecer</li>
          <li>Ambiente tranquilo</li>
        </ul>
      </div>
    </div>

    <div class="detail-block" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/alberca-comun.jpg" alt="Alberca común rodeada de palapas y camastros" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Para convivir</span>
        <h2>Alberca común</h2>
        <p>
          Más grande y rodeada de palapas, mesas y camastros: el punto de encuentro para las tardes
          en familia o con amigos.
        </p>
        <ul class="feature-list">
          <li>Palapas y mesas para convivir</li>
          <li>Ideal para familias</li>
          <li>Cerca de las áreas de descanso</li>
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
