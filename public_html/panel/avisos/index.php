<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

const POR_PAGINA = 10;

$title = 'Avisos — Villas del Palmar';
$description = 'Estados financieros, mejoras y avisos de Villas del Palmar.';

$categoriasUsadas = array_diff(getCategoriasUsadas(), CATEGORIAS_ASAMBLEA);
$categoriaActual = in_array($_GET['categoria'] ?? '', $categoriasUsadas, true) ? $_GET['categoria'] : null;
$categoriasLibres = array_diff($categoriasUsadas, CATEGORIAS_BASE);
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

// La mesa ve también sus borradores (publicado=0) para poder administrarlos;
// un propietario nunca ve nada que no esté publicado.
$esMesa = $usuario['tipo'] === 'mesa';
$total = contarPublicaciones($categoriaActual, $esMesa);
$totalPaginas = max(1, (int) ceil($total / POR_PAGINA));

// La fecha de "última visita" ANTES de actualizarla es la que sirve para
// marcar qué publicaciones son nuevas en esta misma carga de la página.
$ultimaVisitaAnterior = getUltimaVisitaAvisos($usuario['id']);

$publicaciones = getPublicaciones($categoriaActual, POR_PAGINA, ($paginaActual - 1) * POR_PAGINA, $esMesa);
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
      <p class="page-banner-lead">Estados financieros, mejoras y avisos generales publicados por el comité administrativo.</p>
    </div>
  </section>

  <section class="detail-sections">
    <div class="categoria-tabs" data-reveal>
      <a href="/panel/avisos" class="categoria-tab <?= !$categoriaActual ? 'is-active' : '' ?>">Todos</a>
      <a href="/panel/avisos?categoria=financiero" class="categoria-tab <?= $categoriaActual === 'financiero' ? 'is-active' : '' ?>">Estados financieros</a>
      <a href="/panel/avisos?categoria=mejora" class="categoria-tab <?= $categoriaActual === 'mejora' ? 'is-active' : '' ?>">Mejoras</a>
      <a href="/panel/avisos?categoria=aviso" class="categoria-tab <?= $categoriaActual === 'aviso' ? 'is-active' : '' ?>">Avisos generales</a>

      <?php foreach ($categoriasLibres as $cat): ?>
        <a href="/panel/avisos?categoria=<?= urlencode($cat) ?>" class="categoria-tab <?= $categoriaActual === $cat ? 'is-active' : '' ?>"><?= htmlspecialchars(etiquetaCategoria($cat)) ?></a>
      <?php endforeach; ?>

      <?php if ($usuario['tipo'] === 'mesa'): ?>
        <a href="/panel/avisos/nueva" class="btn btn-primary categoria-tab-cta">+ Nueva publicación</a>
      <?php endif; ?>
    </div>

    <?php if (empty($publicaciones)): ?>
      <p class="placeholder-note" data-reveal>No hay publicaciones en esta categoría todavía.</p>
    <?php else: ?>
      <div class="tabla-wrap" data-reveal>
        <table class="tabla-pagos tabla-avisos">
          <thead>
            <tr>
              <th>Aviso</th>
              <th>Prioridad</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($publicaciones as $pub): ?>
              <tr class="tabla-fila-clic" data-modal-target="aviso-modal-<?= (int) $pub['id'] ?>" tabindex="0">
                <td>
                  <span class="tabla-aviso-titulo">
                    <?= htmlspecialchars($pub['titulo']) ?>
                    <?php if (!empty($pub['destacado'])): ?><span class="publicacion-destacado">★</span><?php endif; ?>
                    <?php if (!empty($pub['esNueva'])): ?><span class="publicacion-nueva">Nuevo</span><?php endif; ?>
                  </span>
                  <span class="tabla-subtexto">Publicó: <?= htmlspecialchars($pub['autor_nombre']) ?> · <?= htmlspecialchars($pub['autor_cargo']) ?></span>
                </td>
                <td>
                  <span class="badge badge--prioridad-<?= htmlspecialchars($pub['prioridad']) ?>"><?= htmlspecialchars(etiquetaPrioridad($pub['prioridad'])) ?></span>
                </td>
                <td>
                  <span class="badge <?= $pub['publicado'] ? 'badge--ok' : 'badge--alerta' ?>">
                    <?= $pub['publicado'] ? 'Publicado' : 'Sin publicar' ?>
                  </span>
                </td>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($pub['fecha']))) ?></td>
                <td class="tabla-avisos-archivos" data-no-row-click>
                  <?php if (!empty($pub['archivos'])): ?>
                    <?php foreach ($pub['archivos'] as $archivo): ?>
                      <a href="/panel/archivo?id=<?= (int) $archivo['id'] ?>" class="tabla-archivo-link" title="Descargar: <?= htmlspecialchars($archivo['archivo_nombre_original']) ?>">📎</a>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="tabla-subtexto">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php foreach ($publicaciones as $pub): ?>
      <div class="aviso-modal-overlay" id="aviso-modal-<?= (int) $pub['id'] ?>" hidden>
        <div class="aviso-modal">
          <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
          <?php include __DIR__ . '/../../partials/tarjeta-publicacion.php'; ?>
        </div>
      </div>
    <?php endforeach; ?>

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
