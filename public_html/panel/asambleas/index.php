<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Asambleas y notas — Villas del Palmar';
$description = 'Convocatorias, actas firmadas y acuerdos con seguimiento de Villas del Palmar.';

$convocatorias = getPublicacionesAsamblea('convocatoria');
$actas = getPublicacionesAsamblea('acta');
$acuerdos = getAcuerdos();
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
      <span class="eyebrow">Comité</span>
      <h1>Asambleas y notas</h1>
      <p class="page-banner-lead">Convocatorias, actas firmadas y acuerdos con seguimiento.</p>
    </div>
  </section>

  <section class="detail-sections">

    <div class="asambleas-seccion" data-reveal>
      <div class="section-heading-fila">
        <h2>Convocatorias</h2>
        <?php if ($usuario['tipo'] === 'mesa'): ?>
          <a href="/panel/avisos/nueva?categoria=convocatoria" class="btn btn-primary">+ Nueva convocatoria</a>
        <?php endif; ?>
      </div>
      <?php if (empty($convocatorias)): ?>
        <p class="placeholder-note">Todavía no hay convocatorias.</p>
      <?php else: ?>
        <div class="publicaciones-list">
          <?php foreach ($convocatorias as $pub): ?>
            <?php include __DIR__ . '/../../partials/tarjeta-publicacion.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="asambleas-seccion" data-reveal>
      <div class="section-heading-fila">
        <h2>Actas firmadas</h2>
        <?php if ($usuario['tipo'] === 'mesa'): ?>
          <a href="/panel/avisos/nueva?categoria=acta" class="btn btn-primary">+ Nueva acta</a>
        <?php endif; ?>
      </div>
      <?php if (empty($actas)): ?>
        <p class="placeholder-note">Todavía no hay actas.</p>
      <?php else: ?>
        <div class="publicaciones-list">
          <?php foreach ($actas as $pub): ?>
            <?php include __DIR__ . '/../../partials/tarjeta-publicacion.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="asambleas-seccion" data-reveal>
      <div class="section-heading-fila">
        <h2>Acuerdos y seguimiento</h2>
        <?php if ($usuario['tipo'] === 'mesa'): ?>
          <a href="/panel/asambleas/acuerdos/nuevo" class="btn btn-primary">+ Nuevo acuerdo</a>
        <?php endif; ?>
      </div>
      <?php if (empty($acuerdos)): ?>
        <p class="placeholder-note">Todavía no hay acuerdos registrados.</p>
      <?php else: ?>
        <div class="acuerdos-lista">
          <?php foreach ($acuerdos as $a): ?>
            <div class="acuerdo-card acuerdo-card--<?= htmlspecialchars($a['estatus']) ?>">
              <span class="estatus-pill estatus-pill--<?= htmlspecialchars($a['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($a['estatus'])) ?></span>
              <p class="acuerdo-descripcion"><?= nl2brSeguro($a['descripcion']) ?></p>
              <footer>
                <span>
                  <?= htmlspecialchars($a['autor_nombre']) ?>
                  <?php if (!empty($a['fecha_limite'])): ?>
                    · Fecha límite: <?= htmlspecialchars(date('d/m/Y', strtotime($a['fecha_limite']))) ?>
                  <?php endif; ?>
                </span>
                <?php if ($usuario['tipo'] === 'mesa'): ?>
                  <span class="publicacion-acciones">
                    <a href="/panel/asambleas/acuerdos/editar?id=<?= (int) $a['id'] ?>" class="btn-editar">Editar</a>
                    <form action="/panel/asambleas/acuerdos/eliminar?id=<?= (int) $a['id'] ?>" method="POST"
                          onsubmit="return confirm('¿Eliminar este acuerdo?');">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                      <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                  </span>
                <?php endif; ?>
              </footer>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </section>

  <!-- ===== Lightbox: mismo que /panel/avisos, por si una convocatoria/acta trae imágenes ===== -->
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
