<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

const POR_PAGINA = 10;

$title = 'Avisos — Villas del Palmar';
$description = 'Avisos publicados por el comité administrativo de Villas del Palmar.';

$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));

// La mesa ve también sus borradores (publicado=0) para poder administrarlos;
// un propietario nunca ve nada que no esté publicado. Ya no hay filtro de
// categoría (el formulario de captura dejó de preguntarla — ver formulario.php).
$esMesa = $usuario['tipo'] === 'mesa';
$total = contarPublicaciones($esMesa);
$totalPaginas = max(1, (int) ceil($total / POR_PAGINA));

// La fecha de "última visita" ANTES de actualizarla es la que sirve para
// marcar qué publicaciones son nuevas en esta misma carga de la página.
$ultimaVisitaAnterior = getUltimaVisitaAvisos($usuario['id']);

$publicaciones = getPublicaciones(POR_PAGINA, ($paginaActual - 1) * POR_PAGINA, $esMesa);
foreach ($publicaciones as &$p) {
    $p['esNueva'] = $ultimaVisitaAnterior ? $p['creado_en'] > $ultimaVisitaAnterior : true;
}
unset($p);

marcarVisitaAvisos($usuario['id'], date('Y-m-d H:i:s'));

// Para que el botón de "Estado" regrese a la misma página desde
// donde se alternó, no siempre al feed general sin filtros.
$urlActual = $_SERVER['REQUEST_URI'];
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
      <p class="page-banner-lead">Avisos publicados por el comité administrativo.</p>
    </div>
  </section>

  <section class="detail-sections">
    <?php if ($esMesa): ?>
      <p style="text-align: right; margin-bottom: 16px;" data-reveal>
        <a href="/panel/avisos/nueva" class="btn btn-primary">+ Nueva publicación</a>
      </p>
    <?php endif; ?>

    <?php if (empty($publicaciones)): ?>
      <p class="placeholder-note" data-reveal>No hay publicaciones todavía.</p>
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
                    <?php if ($esMesa && $pub['audiencia'] === 'comite'): ?><span class="badge badge--alerta">🔒 Solo comité</span><?php endif; ?>
                  </span>
                  <span class="tabla-subtexto">Publicó: <?= htmlspecialchars($pub['autor_nombre']) ?> · <?= htmlspecialchars($pub['autor_cargo']) ?></span>
                </td>
                <td>
                  <span class="badge badge--prioridad-<?= htmlspecialchars($pub['prioridad']) ?>"><?= htmlspecialchars(etiquetaPrioridad($pub['prioridad'])) ?></span>
                </td>
                <td data-no-row-click>
                  <?php if ($esMesa): ?>
                    <form method="POST" action="/panel/avisos/estado?id=<?= (int) $pub['id'] ?>">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                      <input type="hidden" name="volver" value="<?= htmlspecialchars($urlActual) ?>" />
                      <button
                        type="submit"
                        class="badge badge--clic <?= $pub['publicado'] ? 'badge--ok' : 'badge--alerta' ?>"
                        title="Clic para <?= $pub['publicado'] ? 'pasar a borrador' : 'publicar' ?>"
                      ><?= $pub['publicado'] ? 'Publicado' : 'Sin publicar' ?></button>
                    </form>
                  <?php else: ?>
                    <span class="badge <?= $pub['publicado'] ? 'badge--ok' : 'badge--alerta' ?>">
                      <?= $pub['publicado'] ? 'Publicado' : 'Sin publicar' ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($pub['fecha']))) ?></td>
                <td data-no-row-click>
                  <?php $archivosPub = $pub['archivos']; ?>
                  <?php if (empty($archivosPub)): ?>
                    <span class="tabla-subtexto">—</span>
                  <?php elseif (count($archivosPub) === 1): ?>
                    <a href="/panel/archivo?id=<?= (int) $archivosPub[0]['id'] ?>" class="btn-archivos" title="Descargar: <?= htmlspecialchars($archivosPub[0]['archivo_nombre_original']) ?>">Archivos</a>
                  <?php else: ?>
                    <div class="archivos-dropdown">
                      <button type="button" class="btn-archivos" data-archivos-toggle>Archivos (<?= count($archivosPub) ?>)</button>
                      <div class="archivos-dropdown-menu" hidden>
                        <?php foreach ($archivosPub as $archivo): ?>
                          <a href="/panel/archivo?id=<?= (int) $archivo['id'] ?>"><?= htmlspecialchars($archivo['archivo_nombre_original']) ?></a>
                        <?php endforeach; ?>
                      </div>
                    </div>
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
          <a href="/panel/avisos?pagina=<?= $paginaActual - 1 ?>">← Anterior</a>
        <?php endif; ?>
        <span class="paginacion-actual">Página <?= $paginaActual ?> de <?= $totalPaginas ?></span>
        <?php if ($paginaActual < $totalPaginas): ?>
          <a href="/panel/avisos?pagina=<?= $paginaActual + 1 ?>">Siguiente →</a>
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
