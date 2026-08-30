<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Departamentos — Villas del Palmar';
$description = 'Fachadas y terrazas de los departamentos.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../partials/portal-header.php'; ?>

  <!-- ===== Banner ===== -->
  <section class="page-banner" style="background-image: url('/images/galeria/fachada-departamentos.jpg');">
    <div class="page-banner-content">
      <a href="/panel/instalaciones" class="back-link">← Volver a instalaciones</a>
      <span class="eyebrow">Espacios comunes</span>
      <h1>Departamentos</h1>
      <p class="page-banner-lead">
        Departamentos de dos niveles con acceso directo a todas las áreas comunes.
      </p>
    </div>
  </section>

  <!-- ===== Detalle ===== -->
  <section class="detail-sections">
    <div class="detail-block" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/fachada-departamentos.jpg" alt="Fachada de los departamentos con jardineras de flores" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Acabados cálidos</span>
        <h2>Fachadas y jardineras</h2>
        <p>
          Edificios de dos niveles con acabados cálidos, jardineras de flores y áreas comunes
          a solo unos pasos de la puerta.
        </p>
        <ul class="feature-list">
          <li>Acabados cálidos</li>
          <li>Jardineras y áreas verdes cercanas</li>
          <li>Acceso directo a amenidades</li>
        </ul>
      </div>
    </div>

    <div class="detail-block is-reverse" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/departamento-palapa.jpg" alt="Terraza techada tipo palapa" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Espacio extra</span>
        <h2>Terrazas techadas</h2>
        <p>
          Algunos departamentos cuentan con terraza techada tipo palapa, un espacio extra
          para el exterior sin exponerte al sol directo.
        </p>
        <ul class="feature-list">
          <li>Terraza tipo palapa</li>
          <li>Espacio extra al aire libre</li>
          <li>Vista al entorno natural</li>
        </ul>
      </div>
    </div>

    <div class="detail-block" data-reveal>
      <div class="detail-media">
        <img src="/images/galeria/departamento-fachada2.jpg" alt="Departamentos rodeados de palmeras y vegetación" />
      </div>
      <div class="detail-text">
        <span class="eyebrow">Rodeado de verde</span>
        <h2>Entorno natural</h2>
        <p>
          Rodeados de palmeras y vegetación de la colina, con vista hacia el residencial
          y sus áreas comunes.
        </p>
        <ul class="feature-list">
          <li>Rodeado de vegetación</li>
          <li>Vista al residencial</li>
          <li>Ambiente tranquilo</li>
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
