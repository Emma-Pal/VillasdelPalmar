<?php
require_once __DIR__ . '/../../../../app/bootstrap.php';
requireMesa();

$title = 'Administrar galería — Villas del Palmar';
$description = 'Imágenes agregadas a la Galería de Villas del Palmar.';

$itemsPorCategoria = [];
foreach (CATEGORIAS_GALERIA as $cat) {
    $itemsPorCategoria[$cat] = getGaleriaItems($cat);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <a href="/panel/galeria" class="back-link">← Volver a Acerca de</a>
      <span class="eyebrow">Administración</span>
      <h1>Administrar galería</h1>
      <p class="page-banner-lead">Imágenes agregadas después de las que ya vienen fijas en cada categoría.</p>
    </div>
  </section>

  <section class="detail-sections">
    <?php foreach (CATEGORIAS_GALERIA as $cat): ?>
      <div class="documentos-grupo" data-reveal>
        <div class="section-heading-fila">
          <h2><?= htmlspecialchars(etiquetaCategoriaGaleria($cat)) ?></h2>
          <a href="/panel/galeria/admin/nuevo?categoria=<?= htmlspecialchars($cat) ?>" class="btn btn-primary">+ Agregar</a>
        </div>

        <?php if (empty($itemsPorCategoria[$cat])): ?>
          <p class="placeholder-note">Todavía no se ha agregado ninguna imagen extra en esta categoría.</p>
        <?php else: ?>
          <ul class="documentos-lista">
            <?php foreach ($itemsPorCategoria[$cat] as $item): ?>
              <li class="documento-item">
                <span class="documento-titulo"><?= htmlspecialchars($item['titulo']) ?></span>
                <p class="documento-descripcion"><?= htmlspecialchars($item['eyebrow']) ?></p>
                <span class="documento-acciones">
                  <a href="/panel/galeria/<?= htmlspecialchars($cat) ?>#item-<?= (int) $item['id'] ?>" class="btn-editar">Ver en la página</a>
                  <a href="/panel/galeria/admin/editar?id=<?= (int) $item['id'] ?>" class="btn-editar">Editar</a>
                  <form action="/panel/galeria/admin/eliminar?id=<?= (int) $item['id'] ?>" method="POST"
                        onsubmit="return confirm('¿Eliminar esta imagen? Esto no se puede deshacer.');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                  </form>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>

  <?php include __DIR__ . '/../../../partials/footer.php'; ?>

</body>
</html>
