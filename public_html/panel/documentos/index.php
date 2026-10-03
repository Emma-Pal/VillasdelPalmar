<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Guía del propietario — Villas del Palmar';
$description = 'Reglamentos, políticas de uso, cartas y demás documentos oficiales de Villas del Palmar.';

$esMesa = $usuario['tipo'] === 'mesa';

$categoriaFiltro = in_array($_GET['categoria'] ?? '', CATEGORIAS_DOCUMENTO, true) ? $_GET['categoria'] : null;
$buscar = trim($_GET['buscar'] ?? '');
$orden = ($_GET['orden'] ?? '') === 'asc' ? 'asc' : 'desc';

$documentos = getDocumentos($categoriaFiltro, true, $buscar !== '' ? $buscar : null, $orden);
$documentosArchivados = getDocumentos($categoriaFiltro, false, $buscar !== '' ? $buscar : null, $orden);

// Nombres sugeridos en el datalist de "Nombre del documento": algunos
// comunes de ejemplo + los que ya se hayan usado antes en este condominio.
$nombresSugeridos = array_unique(array_merge(
    [
        'Reglamento Interno', 'Reglamento de Áreas Comunes', 'Reglamento de Uso de Alberca',
        'Reglamento de Convivencia', 'Reglamento del Club de Playa', 'Política de Uso del Club de Playa',
        'Número de Ocupantes por Villa', 'Carta de Reserva',
    ],
    getNombresDocumentosUsados()
));

// Para que el pencil/"Editar" de cada fila, los filtros y el buscador no se
// pierdan entre sí al cambiar uno (mismo patrón que Asambleas).
$sufijoFiltros = '';
if ($buscar !== '') $sufijoFiltros .= '&buscar=' . urlencode($buscar);
if ($orden === 'asc') $sufijoFiltros .= '&orden=asc';
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
      <span class="eyebrow">Administración</span>
      <h1>Guía del propietario</h1>
      <p class="page-banner-lead">Reglamentos, políticas de uso, cartas y demás documentos oficiales del condominio, del más reciente al más antiguo.</p>
    </div>
  </section>

  <section class="detail-sections">
    <div class="section-heading-fila">
      <form method="GET" action="/panel/documentos" class="buscador-wrap" style="flex: 1; min-width: 240px;">
        <?php if ($categoriaFiltro): ?><input type="hidden" name="categoria" value="<?= htmlspecialchars($categoriaFiltro) ?>" /><?php endif; ?>
        <?php if ($orden === 'asc'): ?><input type="hidden" name="orden" value="asc" /><?php endif; ?>
        <span class="buscador-icono" aria-hidden="true">🔍</span>
        <input type="search" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar documento..." />
      </form>
      <?php if ($esMesa): ?>
        <button type="button" class="btn btn-primary" data-modal-open="modal-nuevo-documento">+ Nuevo documento</button>
      <?php endif; ?>
    </div>

    <div class="section-heading-fila">
      <div class="categoria-tabs" style="margin-bottom: 0;">
        <a href="/panel/documentos?<?= substr($sufijoFiltros, 1) ?>" class="categoria-tab <?= !$categoriaFiltro ? 'is-active' : '' ?>">Todos</a>
        <?php foreach (CATEGORIAS_DOCUMENTO as $cat): ?>
          <a href="/panel/documentos?categoria=<?= urlencode($cat) . $sufijoFiltros ?>" class="categoria-tab <?= $categoriaFiltro === $cat ? 'is-active' : '' ?>"><?= htmlspecialchars(etiquetaCategoriaDocumento($cat)) ?></a>
        <?php endforeach; ?>
      </div>

      <form method="GET" action="/panel/documentos" class="filtros-fila" style="margin-bottom: 0;">
        <?php if ($categoriaFiltro): ?><input type="hidden" name="categoria" value="<?= htmlspecialchars($categoriaFiltro) ?>" /><?php endif; ?>
        <?php if ($buscar !== ''): ?><input type="hidden" name="buscar" value="<?= htmlspecialchars($buscar) ?>" /><?php endif; ?>
        <label>
          Ordenar
          <select name="orden" onchange="this.form.submit();">
            <option value="desc" <?= $orden === 'desc' ? 'selected' : '' ?>>Más recientes primero</option>
            <option value="asc" <?= $orden === 'asc' ? 'selected' : '' ?>>Más antiguos primero</option>
          </select>
        </label>
      </form>
    </div>

    <p class="seccion-subtexto" style="margin-bottom: 16px;"><?= count($documentos) ?> documento<?= count($documentos) === 1 ? '' : 's' ?></p>

    <?php if (empty($documentos)): ?>
      <p class="placeholder-note">No hay documentos que coincidan.</p>
    <?php else: ?>
      <div class="convocatorias-lista">
        <?php foreach ($documentos as $doc): ?>
          <?php include __DIR__ . '/../../partials/documento-fila.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($documentosArchivados)): ?>
      <details class="versiones-anteriores" data-reveal>
        <summary>Versiones anteriores (archivadas)</summary>
        <div class="convocatorias-lista" style="margin-top: 16px;">
          <?php foreach ($documentosArchivados as $doc): ?>
            <?php include __DIR__ . '/../../partials/documento-fila.php'; ?>
          <?php endforeach; ?>
        </div>
      </details>
    <?php endif; ?>
  </section>

  <?php if ($esMesa): ?>
    <!-- ===== Modal: Nuevo documento ===== -->
    <div class="aviso-modal-overlay" id="modal-nuevo-documento" hidden>
      <div class="aviso-modal">
        <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
        <div class="form-card">
          <span class="eyebrow">Guía del propietario</span>
          <h2>Nuevo documento</h2>
          <form action="/panel/documentos/guardar" method="POST" enctype="multipart/form-data" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <?php $doc = []; include __DIR__ . '/../../partials/documento-campos.php'; ?>
            <div class="modal-botones">
              <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-primary">Publicar documento</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ===== Modales: Editar documento (uno por documento, vigente o archivado) ===== -->
    <?php foreach (array_merge($documentos, $documentosArchivados) as $doc): ?>
      <div class="aviso-modal-overlay" id="modal-editar-documento-<?= (int) $doc['id'] ?>" hidden>
        <div class="aviso-modal">
          <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
          <div class="form-card">
            <span class="eyebrow">Guía del propietario</span>
            <h2>Editar documento</h2>
            <form action="/panel/documentos/guardar" method="POST" enctype="multipart/form-data" class="contact-form">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
              <input type="hidden" name="id" value="<?= (int) $doc['id'] ?>" />
              <?php include __DIR__ . '/../../partials/documento-campos.php'; ?>
              <div class="modal-botones">
                <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <datalist id="nombres-documentos-sugeridos">
    <?php foreach ($nombresSugeridos as $n): ?>
      <option value="<?= htmlspecialchars($n) ?>"></option>
    <?php endforeach; ?>
  </datalist>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
