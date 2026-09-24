<?php
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$title = 'Panel — Villas del Palmar';
$description = 'Panel de Villas del Palmar.';
$ultimasPublicaciones = getPublicaciones(null, 10, 0);
$totalPublicaciones = contarPublicaciones();

// Fotos reales del residencial para el carrusel de la galería (todas ya
// están en /images, copiadas de las que se usaban en el sitio público).
$slidesGaleria = [
    [
        'url' => '/images/hero/atardecer-alberca.jpg',
        'titulo' => 'Atardecer en la alberca',
        'descripcion' => 'La vista que reciben los propietarios al caer la tarde, con las palmeras de fondo.',
        'tag' => 'Atardecer',
    ],
    [
        'url' => '/images/galeria/alberca-infinita.jpg',
        'titulo' => 'Alberca infinita con vista al mar',
        'descripcion' => 'Una de las albercas más fotografiadas del residencial, con vista directa a la bahía.',
        'tag' => 'Alberca',
    ],
    [
        'url' => '/images/galeria/entrada-principal-de.jpg',
        'titulo' => 'Entrada principal',
        'descripcion' => 'El acceso principal de Villas del Palmar, con caseta de vigilancia y control de entrada/salida.',
        'tag' => 'Acceso',
    ],
    [
        'url' => '/images/galeria/jardin-central.jpg',
        'titulo' => 'Jardín central',
        'descripcion' => 'Áreas verdes cuidadas todo el año, parte del mantenimiento cubierto por la cuota.',
        'tag' => 'Áreas verdes',
    ],
    [
        'url' => '/images/galeria/fachada-departamentos.jpg',
        'titulo' => 'Fachada de los departamentos',
        'descripcion' => 'La arquitectura característica de los edificios que conforman el residencial.',
        'tag' => 'Departamentos',
    ],
];
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
      <span class="eyebrow">Bienvenido, <?= htmlspecialchars($usuario['nombre']) ?></span>
      <h1>Panel <em>Principal</em></h1>
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
          <span class="eyebrow">Comité</span>
          <h2>Publicar algo nuevo</h2>
          <p>Comparte un estado financiero, una mejora o un aviso general.</p>
          <a href="/panel/avisos/nueva" class="btn btn-ghost-light">Nueva publicación</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="accesos-rapidos" data-reveal>
      <span class="eyebrow">Accesos rápidos</span>
      <div class="accesos-rapidos-lista">
        <a href="/panel/avisos" class="acceso-rapido"><span class="acceso-rapido-icono">📌</span> Avisos</a>
        <a href="/panel/instalaciones" class="acceso-rapido"><span class="acceso-rapido-icono">🏝️</span> Instalaciones</a>
        <a href="/panel/mesa" class="acceso-rapido"><span class="acceso-rapido-icono">👥</span> Comité</a>
        <?php if ($usuario['tipo'] === 'mesa'): ?>
          <a href="/panel/usuarios" class="acceso-rapido"><span class="acceso-rapido-icono">🔐</span> Usuarios</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="galeria-carousel" data-reveal>
      <div class="galeria-carousel-heading">
        <div>
          <span class="eyebrow">Galería</span>
          <h2>Imágenes de Villas del Palmar</h2>
        </div>
        <p class="galeria-carousel-contador" id="galeria-contador">01 / <?= sprintf('%02d', count($slidesGaleria)) ?></p>
      </div>

      <div class="galeria-carousel-main" id="galeria-main">
        <img
          src="<?= htmlspecialchars($slidesGaleria[0]['url']) ?>"
          alt="<?= htmlspecialchars($slidesGaleria[0]['titulo']) ?>"
          class="galeria-carousel-img"
          id="galeria-img"
        />
        <div class="galeria-carousel-overlay"></div>
        <div class="galeria-carousel-caption">
          <span class="galeria-carousel-tag" id="galeria-tag"><?= htmlspecialchars($slidesGaleria[0]['tag']) ?></span>
          <h3 id="galeria-titulo"><?= htmlspecialchars($slidesGaleria[0]['titulo']) ?></h3>
          <p id="galeria-descripcion"><?= htmlspecialchars($slidesGaleria[0]['descripcion']) ?></p>
        </div>
        <button type="button" class="galeria-carousel-flecha galeria-carousel-flecha--prev" id="galeria-prev" aria-label="Imagen anterior">‹</button>
        <button type="button" class="galeria-carousel-flecha galeria-carousel-flecha--next" id="galeria-next" aria-label="Imagen siguiente">›</button>
      </div>

      <div class="galeria-carousel-miniaturas" id="galeria-miniaturas">
        <?php foreach ($slidesGaleria as $i => $slide): ?>
          <button type="button" class="galeria-carousel-mini <?= $i === 0 ? 'is-active' : '' ?>" data-indice="<?= $i ?>" aria-label="Ir a la imagen <?= $i + 1 ?>">
            <img src="<?= htmlspecialchars($slide['url']) ?>" alt="" />
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <script>window.VP_GALERIA_SLIDES = <?= json_encode($slidesGaleria, JSON_UNESCAPED_UNICODE) ?>;</script>

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
