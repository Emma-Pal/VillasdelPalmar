<?php
// Una fila de la lista de Guía del propietario (documento vigente o
// archivado) — compartida por la lista principal y "Versiones anteriores".
// Espera $doc (fila de documentos + autor_nombre) y usa las globales
// $usuario/$csrfToken/$esMesa que ya pone bootstrap.php / documentos/index.php.
$rutaFisica = rutaArchivoFisico($doc['archivo']);
$tamanoMb = file_exists($rutaFisica) ? round(filesize($rutaFisica) / 1024 / 1024, 1) : null;
$fechaDoc = strtotime($doc['fecha_documento']);
?>
<div class="convocatoria-fila<?= $doc['vigente'] ? '' : ' documento-fila--archivado' ?>">
  <div class="fecha-bloque">
    <span class="fecha-bloque-mes"><?= htmlspecialchars(mesAbreviado((int) date('n', $fechaDoc))) ?></span>
    <span class="fecha-bloque-dia"><?= htmlspecialchars(date('d', $fechaDoc)) ?></span>
    <span class="fecha-bloque-anio"><?= htmlspecialchars(date('Y', $fechaDoc)) ?></span>
  </div>
  <div class="convocatoria-info">
    <div class="convocatoria-tags">
      <span class="badge badge--doc-<?= htmlspecialchars($doc['categoria']) ?>"><?= htmlspecialchars(etiquetaCategoriaDocumento($doc['categoria'])) ?></span>
      <?php if ($doc['vigente']): ?>
        <span class="badge badge--ok">Vigente</span>
      <?php else: ?>
        <span class="badge badge--alerta">Archivada</span>
      <?php endif; ?>
    </div>
    <h3><?= htmlspecialchars($doc['titulo']) ?></h3>
    <p class="convocatoria-meta">
      PDF<?= $tamanoMb !== null ? ' · ' . $tamanoMb . ' MB' : '' ?> · Publicado por <?= htmlspecialchars($doc['autor_nombre']) ?>
      <?php if (!empty($doc['vigente_desde'])): ?>
        · Vigente desde <?= htmlspecialchars(date('d/m/Y', strtotime($doc['vigente_desde']))) ?>
      <?php endif; ?>
    </p>
    <?php if (!empty($doc['descripcion'])): ?>
      <p class="convocatoria-mensaje"><?= nl2brSeguro($doc['descripcion']) ?></p>
    <?php endif; ?>
    <?php if ($esMesa): ?>
      <p class="publicacion-acciones" style="margin-top: 10px;">
        <button type="button" class="btn-editar" data-modal-open="modal-editar-documento-<?= (int) $doc['id'] ?>">Editar</button>
        <form action="/panel/documentos/eliminar?id=<?= (int) $doc['id'] ?>" method="POST"
              onsubmit="return confirm('¿Eliminar este documento? Esto no se puede deshacer.');">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <button type="submit" class="btn-eliminar">Eliminar</button>
        </form>
      </p>
    <?php endif; ?>
  </div>
  <a href="/panel/documento?id=<?= (int) $doc['id'] ?>" target="_blank" rel="noopener" class="btn btn-ghost-light convocatoria-pdf-btn">Ver PDF</a>
</div>
