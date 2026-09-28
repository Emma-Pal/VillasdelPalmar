<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Directorio — Villas del Palmar';
$description = 'Contactos de administración, seguridad, mantenimiento, emergencias y proveedores autorizados.';

$contactosPorCategoria = [];
foreach (CATEGORIAS_CONTACTO as $cat) {
    $contactosPorCategoria[$cat] = getContactos($cat);
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
      <h1>Servicios y directorio</h1>
      <p class="page-banner-lead">Contactos de administración, seguridad, mantenimiento, emergencias y proveedores autorizados.</p>
    </div>
  </section>

  <section class="detail-sections">
    <?php if ($usuario['tipo'] === 'mesa'): ?>
      <p style="text-align: right; margin-bottom: 16px;">
        <a href="/panel/directorio/nuevo" class="btn btn-primary">+ Nuevo contacto</a>
      </p>
    <?php endif; ?>

    <?php foreach (CATEGORIAS_CONTACTO as $cat): ?>
      <div class="directorio-grupo" id="categoria-<?= htmlspecialchars($cat) ?>" data-reveal>
        <h2><?= htmlspecialchars(etiquetaCategoriaContacto($cat)) ?></h2>

        <?php if (empty($contactosPorCategoria[$cat])): ?>
          <p class="placeholder-note">Todavía no hay contactos en esta categoría.</p>
        <?php else: ?>
          <div class="directorio-grid">
            <?php foreach ($contactosPorCategoria[$cat] as $c): ?>
              <div class="directorio-card">
                <h3><?= htmlspecialchars($c['nombre']) ?></h3>
                <?php if (!empty($c['puesto'])): ?>
                  <p class="directorio-puesto"><?= htmlspecialchars($c['puesto']) ?></p>
                <?php endif; ?>
                <?php if (!empty($c['telefono'])): ?>
                  <p><a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $c['telefono'])) ?>">📞 <?= htmlspecialchars($c['telefono']) ?></a></p>
                <?php endif; ?>
                <?php if (!empty($c['correo'])): ?>
                  <p><a href="mailto:<?= htmlspecialchars($c['correo']) ?>">✉️ <?= htmlspecialchars($c['correo']) ?></a></p>
                <?php endif; ?>
                <?php if (!empty($c['notas'])): ?>
                  <p class="directorio-notas"><?= nl2brSeguro($c['notas']) ?></p>
                <?php endif; ?>
                <?php if ($usuario['tipo'] === 'mesa'): ?>
                  <footer class="directorio-acciones">
                    <a href="/panel/directorio/editar?id=<?= (int) $c['id'] ?>" class="btn-editar">Editar</a>
                    <form action="/panel/directorio/eliminar?id=<?= (int) $c['id'] ?>" method="POST"
                          onsubmit="return confirm('¿Eliminar este contacto?');">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                      <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                  </footer>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
