<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Documentos — Villas del Palmar';
$description = 'Reglamento, escritura constitutiva, políticas de áreas comunes y formatos de Villas del Palmar.';

$documentosPorCategoria = [];
foreach (CATEGORIAS_DOCUMENTO as $cat) {
    $documentosPorCategoria[$cat] = getDocumentos($cat);
}
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
      <h1>Reglamento y documentos</h1>
      <p class="page-banner-lead">Reglamento interno, escritura constitutiva, políticas de áreas comunes y formatos.</p>
    </div>
  </section>

  <section class="detail-sections">
    <?php if ($usuario['tipo'] === 'mesa'): ?>
      <p style="text-align: right; margin-bottom: 16px;">
        <a href="/panel/documentos/nuevo" class="btn btn-primary">+ Nuevo documento</a>
      </p>
    <?php endif; ?>

    <?php foreach (CATEGORIAS_DOCUMENTO as $cat): ?>
      <div class="documentos-grupo" data-reveal>
        <h2><?= htmlspecialchars(etiquetaCategoriaDocumento($cat)) ?></h2>

        <?php if (empty($documentosPorCategoria[$cat])): ?>
          <p class="placeholder-note">Todavía no hay documentos en esta categoría.</p>
        <?php else: ?>
          <ul class="documentos-lista">
            <?php foreach ($documentosPorCategoria[$cat] as $doc): ?>
              <li class="documento-item">
                <a href="/panel/documento?id=<?= (int) $doc['id'] ?>" class="documento-link">
                  📄 <span class="documento-titulo"><?= htmlspecialchars($doc['titulo']) ?></span>
                </a>
                <?php if (!empty($doc['descripcion'])): ?>
                  <p class="documento-descripcion"><?= nl2brSeguro($doc['descripcion']) ?></p>
                <?php endif; ?>
                <?php if ($usuario['tipo'] === 'mesa'): ?>
                  <span class="documento-acciones">
                    <a href="/panel/documentos/editar?id=<?= (int) $doc['id'] ?>" class="btn-editar">Editar</a>
                    <form action="/panel/documentos/eliminar?id=<?= (int) $doc['id'] ?>" method="POST"
                          onsubmit="return confirm('¿Eliminar este documento? Esto no se puede deshacer.');">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                      <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                  </span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
