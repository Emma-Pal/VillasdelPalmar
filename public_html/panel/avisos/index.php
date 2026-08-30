<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

const POR_PAGINA = 10;

$title = 'Avisos — Villas del Palmar';
$description = 'Estados financieros, mejoras y avisos de Villas del Palmar.';

$categoriaActual = in_array($_GET['categoria'] ?? '', CATEGORIAS_VALIDAS, true) ? $_GET['categoria'] : null;
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));
$total = contarPublicaciones($categoriaActual);
$totalPaginas = max(1, (int) ceil($total / POR_PAGINA));

// La fecha de "última visita" ANTES de actualizarla es la que sirve para
// marcar qué publicaciones son nuevas en esta misma carga de la página.
$ultimaVisitaAnterior = getUltimaVisitaAvisos($usuario['id']);

$publicaciones = getPublicaciones($categoriaActual, POR_PAGINA, ($paginaActual - 1) * POR_PAGINA);
foreach ($publicaciones as &$p) {
    $p['esNueva'] = $ultimaVisitaAnterior ? $p['creado_en'] > $ultimaVisitaAnterior : true;
}
unset($p);

marcarVisitaAvisos($usuario['id'], date('Y-m-d H:i:s'));

$sufijoQuery = $categoriaActual ? '&categoria=' . urlencode($categoriaActual) : '';
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
      <span class="eyebrow">Comunicación</span>
      <h1>Avisos</h1>
      <p class="page-banner-lead">Estados financieros, mejoras y avisos generales publicados por la mesa directiva.</p>
    </div>
  </section>

  <section class="detail-sections">
    <div class="categoria-tabs" data-reveal>
      <a href="/panel/avisos" class="categoria-tab <?= !$categoriaActual ? 'is-active' : '' ?>">Todos</a>
      <a href="/panel/avisos?categoria=financiero" class="categoria-tab <?= $categoriaActual === 'financiero' ? 'is-active' : '' ?>">Estados financieros</a>
      <a href="/panel/avisos?categoria=mejora" class="categoria-tab <?= $categoriaActual === 'mejora' ? 'is-active' : '' ?>">Mejoras</a>
      <a href="/panel/avisos?categoria=aviso" class="categoria-tab <?= $categoriaActual === 'aviso' ? 'is-active' : '' ?>">Avisos generales</a>

      <?php if ($usuario['tipo'] === 'mesa'): ?>
        <a href="/panel/avisos/nueva" class="btn btn-primary categoria-tab-cta">+ Nueva publicación</a>
      <?php endif; ?>
    </div>

    <div class="publicaciones-list" data-reveal>
      <?php if (empty($publicaciones)): ?>
        <p class="placeholder-note">No hay publicaciones en esta categoría todavía.</p>
      <?php endif; ?>
      <?php foreach ($publicaciones as $pub): ?>
        <article class="publicacion-card" id="aviso-<?= (int) $pub['id'] ?>">
          <span class="publicacion-categoria publicacion-categoria--<?= htmlspecialchars($pub['categoria']) ?>">
            <?= htmlspecialchars(etiquetaCategoria($pub['categoria'])) ?>
          </span>
          <?php if ($pub['esNueva']): ?>
            <span class="publicacion-nueva">Nuevo</span>
          <?php endif; ?>
          <h3><?= htmlspecialchars($pub['titulo']) ?></h3>
          <p><?= nl2brSeguro($pub['cuerpo']) ?></p>

          <?php
          $imagenes = array_filter($pub['archivos'], function ($a) { return esImagen($a['archivo']); });
          $otros = array_filter($pub['archivos'], function ($a) { return !esImagen($a['archivo']); });
          ?>

          <?php if (!empty($imagenes)): ?>
            <div class="publicacion-imagenes <?= count($imagenes) > 1 ? 'imagenes-multiples' : '' ?>">
              <?php foreach ($imagenes as $archivo): ?>
                <button
                  type="button"
                  class="publicacion-imagen-btn"
                  data-lightbox-src="/panel/archivo?id=<?= (int) $archivo['id'] ?>"
                  data-lightbox-nombre="<?= htmlspecialchars($archivo['archivo_nombre_original']) ?>"
                >
                  <img src="/panel/archivo?id=<?= (int) $archivo['id'] ?>" alt="<?= htmlspecialchars($archivo['archivo_nombre_original']) ?>" class="publicacion-imagen" />
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($otros)): ?>
            <ul class="publicacion-archivos">
              <?php foreach ($otros as $archivo): ?>
                <li><a href="/panel/archivo?id=<?= (int) $archivo['id'] ?>">📎 <?= htmlspecialchars($archivo['archivo_nombre_original']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <footer>
            <span><?= htmlspecialchars($pub['autor_nombre']) ?> · <?= htmlspecialchars($pub['autor_cargo']) ?> — <?= htmlspecialchars($pub['fecha']) ?></span>
            <?php if ($usuario['tipo'] === 'mesa'): ?>
              <span class="publicacion-acciones">
                <a href="/panel/avisos/editar?id=<?= (int) $pub['id'] ?>" class="btn-editar">Editar</a>
                <form action="/panel/avisos/eliminar?id=<?= (int) $pub['id'] ?>" method="POST"
                      onsubmit="return confirm('¿Eliminar esta publicación? Esto no se puede deshacer.');">
                  <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                  <button type="submit" class="btn-eliminar">Eliminar</button>
                </form>
              </span>
            <?php endif; ?>
          </footer>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPaginas > 1): ?>
      <nav class="paginacion" data-reveal>
        <?php if ($paginaActual > 1): ?>
          <a href="/panel/avisos?pagina=<?= $paginaActual - 1 ?><?= $sufijoQuery ?>">← Anterior</a>
        <?php endif; ?>
        <span class="paginacion-actual">Página <?= $paginaActual ?> de <?= $totalPaginas ?></span>
        <?php if ($paginaActual < $totalPaginas): ?>
          <a href="/panel/avisos?pagina=<?= $paginaActual + 1 ?><?= $sufijoQuery ?>">Siguiente →</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  </section>

  <!-- ===== Lightbox: ver la imagen completa y desde ahí sí descargarla ===== -->
  <div class="lightbox-overlay" id="lightbox-overlay" hidden>
    <button type="button" class="lightbox-close" id="lightbox-close" aria-label="Cerrar">&times;</button>
    <div class="lightbox-content">
      <img src="" alt="" id="lightbox-img" class="lightbox-img" />
      <a href="#" id="lightbox-download" class="btn btn-primary">Descargar imagen</a>
    </div>
  </div>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
