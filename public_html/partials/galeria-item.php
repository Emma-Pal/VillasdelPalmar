<?php
// Renderiza UN item de galería agregado desde el panel de administración,
// con el mismo formato ".detail-block" que los bloques fijos ya existentes
// en cada página de detalle (alberca.php, areas-verdes.php, departamentos.php).
// Espera $item (fila de galeria_items) y $esReverse (bool, para alternar el
// lado de la imagen igual que los bloques originales).
?>
<div class="detail-block<?= $esReverse ? ' is-reverse' : '' ?>" id="item-<?= (int) $item['id'] ?>" data-reveal>
  <div class="detail-media">
    <img src="/panel/galeria-imagen?id=<?= (int) $item['id'] ?>" alt="<?= htmlspecialchars($item['titulo']) ?>" />
  </div>
  <div class="detail-text">
    <span class="eyebrow"><?= htmlspecialchars($item['eyebrow']) ?></span>
    <h2><?= htmlspecialchars($item['titulo']) ?></h2>
    <p><?= nl2brSeguro($item['descripcion']) ?></p>
    <?php
    $caracteristicas = array_filter(array_map('trim', explode("\n", (string) $item['caracteristicas'])));
    ?>
    <?php if (!empty($caracteristicas)): ?>
      <ul class="feature-list">
        <?php foreach ($caracteristicas as $c): ?>
          <li><?= htmlspecialchars($c) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <?php if ($usuario['tipo'] === 'mesa'): ?>
      <p class="galeria-item-acciones">
        <a href="/panel/galeria/admin/editar?id=<?= (int) $item['id'] ?>" class="btn-editar">Editar</a>
        <form action="/panel/galeria/admin/eliminar?id=<?= (int) $item['id'] ?>" method="POST"
              onsubmit="return confirm('¿Eliminar esta imagen? Esto no se puede deshacer.');">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <button type="submit" class="btn-eliminar">Eliminar</button>
        </form>
      </p>
    <?php endif; ?>
  </div>
</div>
