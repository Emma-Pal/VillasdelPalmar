<?php
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$title = 'Panel — Villas del Palmar';
$description = 'Panel de Villas del Palmar.';
$ultimasPublicaciones = getPublicaciones(null, 10, 0);
$totalPublicaciones = contarPublicaciones();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../partials/portal-header.php'; ?>

  <section class="panel-hero">
    <div class="panel-hero-content">
      <span class="eyebrow">Hola, <?= htmlspecialchars($usuario['nombre']) ?></span>
      <h1>Panel</h1>
    </div>
  </section>

  <section class="detail-sections">

    <div class="dashboard-grid" data-reveal>
      <div class="dashboard-card <?= $avisosNuevos > 0 ? 'dashboard-card--alerta' : 'dashboard-card--ok' ?>">
        <span class="eyebrow">Comunicación</span>
        <h2>
          <?php if ($avisosNuevos > 0): ?>
            <?= (int) $avisosNuevos ?> <?= $avisosNuevos === 1 ? 'publicación nueva' : 'publicaciones nuevas' ?>
          <?php else: ?>
            Estás al día
          <?php endif; ?>
        </h2>
        <p><?= (int) $totalPublicaciones ?> publicaciones en total entre estados financieros, mejoras y avisos.</p>
        <a href="/panel/avisos" class="btn btn-primary">Ver avisos</a>
      </div>

      <?php if ($usuario['tipo'] === 'mesa'): ?>
        <div class="dashboard-card">
          <span class="eyebrow">Mesa directiva</span>
          <h2>Publicar algo nuevo</h2>
          <p>Comparte un estado financiero, una mejora o un aviso general.</p>
          <a href="/panel/avisos/nueva" class="btn btn-ghost-light">Nueva publicación</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="section-heading" data-reveal style="margin-top: 24px;">
      <span class="eyebrow">Al día</span>
      <h2>Últimos avisos</h2>
    </div>

    <div class="publicaciones-grid" data-reveal>
      <?php if (empty($ultimasPublicaciones)): ?>
        <p class="placeholder-note">Todavía no hay publicaciones.</p>
      <?php endif; ?>
      <?php foreach ($ultimasPublicaciones as $pub): ?>
        <a href="/panel/avisos#aviso-<?= (int) $pub['id'] ?>" class="publicacion-card-link">
          <article class="publicacion-card">
            <span class="publicacion-categoria publicacion-categoria--<?= categoriaSlug($pub['categoria']) ?>">
              <?= htmlspecialchars(etiquetaCategoria($pub['categoria'])) ?>
            </span>
            <h3><?= htmlspecialchars($pub['titulo']) ?></h3>
            <p><?= nl2brSeguro($pub['cuerpo']) ?></p>
            <footer>
              <span><?= htmlspecialchars($pub['autor_nombre']) ?> · <?= htmlspecialchars($pub['autor_cargo']) ?> — <?= htmlspecialchars($pub['fecha']) ?></span>
            </footer>
            <?php if (!empty($pub['editado_en'])): ?>
              <p class="publicacion-editada">Editado el <?= htmlspecialchars(date('d/m/Y', strtotime($pub['editado_en']))) ?></p>
            <?php endif; ?>
          </article>
        </a>
      <?php endforeach; ?>
    </div>

    <p style="text-align: center; margin-top: 8px;">
      <a href="/panel/avisos" class="back-link back-link--dark" style="display: inline-flex; margin: 0 auto;">Ver todos los avisos →</a>
    </p>

  </section>

  <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
