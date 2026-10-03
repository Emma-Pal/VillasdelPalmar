<?php
// Tarjeta de una publicación (aviso). Usada por el modal de cada fila en
// panel/avisos/index.php — recibe $pub (con ['archivos'] ya cargado) y usa
// las variables globales $usuario/$csrfToken que ya pone app/bootstrap.php
// en cada página. $pub['esNueva'] es opcional (solo avisos/index.php la
// calcula). Ya no muestra la categoría: desde el rediseño de oct. 2026 todo
// lo capturado aquí es categoría 'aviso' fija, así que ese badge siempre
// hubiera dicho lo mismo en cada tarjeta.
?>
<article class="publicacion-card" id="aviso-<?= (int) $pub['id'] ?>">
  <?php if (!empty($pub['destacado'])): ?>
    <span class="publicacion-destacado">★ Destacado</span>
  <?php endif; ?>
  <?php if (!empty($pub['esNueva'])): ?>
    <span class="publicacion-nueva">Nuevo</span>
  <?php endif; ?>
  <h3><?= htmlspecialchars($pub['titulo']) ?></h3>
  <?php if (!empty($pub['fecha_evento'])): ?>
    <p class="publicacion-fecha-evento">📅 Fecha de la asamblea: <strong><?= htmlspecialchars(date('d/m/Y', strtotime($pub['fecha_evento']))) ?></strong></p>
  <?php endif; ?>
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
          <img src="/panel/archivo?id=<?= (int) $archivo['id'] ?>" alt="<?= htmlspecialchars($archivo['archivo_nombre_original']) ?>" class="publicacion-imagen" loading="lazy" />
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
  <?php if (!empty($pub['editado_en'])): ?>
    <p class="publicacion-editada">Editado el <?= htmlspecialchars(date('d/m/Y', strtotime($pub['editado_en']))) ?></p>
  <?php endif; ?>
</article>
