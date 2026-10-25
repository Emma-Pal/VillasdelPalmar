<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Solicitudes y reportes — Villas del Palmar';
$description = 'Quejas, fallas o sugerencias de los propietarios de Villas del Palmar.';

$esMesa = $usuario['tipo'] === 'mesa';
$estatusActual = null;
$solicitudes = [];
$misSolicitudes = [];

if ($esMesa) {
    $estatusValidos = ['pendiente', 'en_progreso', 'resuelto'];
    $estatusActual = in_array($_GET['estatus'] ?? '', $estatusValidos, true) ? $_GET['estatus'] : null;
    $solicitudes = getSolicitudes($estatusActual);
} else {
    $misSolicitudes = getSolicitudesDeAutor($usuario['id']);
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
      <h1>Solicitudes y reportes</h1>
      <p class="page-banner-lead">Quejas, fallas o sugerencias, con folio de seguimiento.</p>
    </div>
  </section>

  <section class="detail-sections">

    <?php if (!$esMesa): ?>
      <div class="section-heading-fila">
        <h2 style="margin: 0;">Mis solicitudes</h2>
        <a href="/panel/solicitudes/nueva" class="btn btn-primary">+ Levantar una solicitud</a>
      </div>

      <?php if (empty($misSolicitudes)): ?>
        <p class="placeholder-note">Todavía no has levantado ninguna solicitud.</p>
      <?php else: ?>
        <div class="estancia-card-lista" data-reveal>
          <?php foreach ($misSolicitudes as $s): ?>
            <div class="estancia-card">
              <div class="estancia-card-encabezado">
                <div class="convocatoria-tags" style="margin: 0;">
                  <span class="estatus-pill estatus-pill--<?= htmlspecialchars($s['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($s['estatus'])) ?></span>
                  <span class="badge">Folio <?= htmlspecialchars(folioSolicitud($s['id'], $s['folio_token'])) ?></span>
                  <span class="badge"><?= htmlspecialchars(etiquetaTipoSolicitud($s['tipo'])) ?></span>
                </div>
                <span class="convocatoria-meta"><?= htmlspecialchars(date('d/m/Y', strtotime($s['creado_en']))) ?></span>
              </div>

              <h3 style="margin: 10px 0 4px;"><?= htmlspecialchars($s['asunto']) ?></h3>
              <?php if (!empty($s['ubicacion'])): ?><p class="convocatoria-meta">📍 <?= htmlspecialchars($s['ubicacion']) ?></p><?php endif; ?>
              <p style="margin: 10px 0;"><?= nl2brSeguro($s['descripcion']) ?></p>

              <?php if (!empty($s['respuesta'])): ?>
                <p class="candado-aviso candado-aviso--publico" style="display: block;"><strong>Respuesta de Administración:</strong><br /><?= nl2brSeguro($s['respuesta']) ?></p>
              <?php else: ?>
                <p class="form-nota">Todavía no hay una respuesta registrada.</p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="categoria-tabs" data-reveal>
        <a href="/panel/solicitudes" class="categoria-tab <?= !$estatusActual ? 'is-active' : '' ?>">Todas</a>
        <a href="/panel/solicitudes?estatus=pendiente" class="categoria-tab <?= $estatusActual === 'pendiente' ? 'is-active' : '' ?>">Pendientes</a>
        <a href="/panel/solicitudes?estatus=en_progreso" class="categoria-tab <?= $estatusActual === 'en_progreso' ? 'is-active' : '' ?>">En progreso</a>
        <a href="/panel/solicitudes?estatus=resuelto" class="categoria-tab <?= $estatusActual === 'resuelto' ? 'is-active' : '' ?>">Resueltas</a>
      </div>

      <?php if (empty($solicitudes)): ?>
        <p class="placeholder-note">No hay solicitudes en esta vista.</p>
      <?php else: ?>
        <div class="tabla-wrap" data-reveal>
          <table class="tabla-pagos">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Tipo</th>
                <th>Asunto</th>
                <th>Estatus</th>
                <th>Fecha</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($solicitudes as $s): ?>
                <tr>
                  <td><?= htmlspecialchars(folioSolicitud($s['id'], $s['folio_token'])) ?></td>
                  <td><?= htmlspecialchars(etiquetaTipoSolicitud($s['tipo'])) ?></td>
                  <td><?= htmlspecialchars($s['asunto']) ?></td>
                  <td><span class="estatus-pill estatus-pill--<?= htmlspecialchars($s['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($s['estatus'])) ?></span></td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($s['creado_en']))) ?></td>
                  <td class="tabla-acciones">
                    <div class="tabla-acciones-fila">
                      <a href="/panel/solicitudes/detalle?id=<?= (int) $s['id'] ?>" class="btn-editar">Ver</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
