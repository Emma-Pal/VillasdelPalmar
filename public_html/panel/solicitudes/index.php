<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Solicitudes y reportes — Villas del Palmar';
$description = 'Quejas, fallas o sugerencias de los propietarios de Villas del Palmar.';

$esMesa = $usuario['tipo'] === 'mesa';
$estatusActual = null;
$solicitudes = [];

if ($esMesa) {
    $estatusValidos = ['pendiente', 'en_progreso', 'resuelto'];
    $estatusActual = in_array($_GET['estatus'] ?? '', $estatusValidos, true) ? $_GET['estatus'] : null;
    $solicitudes = getSolicitudes($estatusActual);
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
      <div class="dashboard-grid" data-reveal>
        <div class="dashboard-card">
          <span class="eyebrow">Reportar algo</span>
          <h2>Levantar una solicitud</h2>
          <p>Reporta una queja, una falla en las instalaciones o una sugerencia. Al enviarla te damos un folio para dar seguimiento.</p>
          <a href="/panel/solicitudes/nueva" class="btn btn-primary">Levantar una solicitud</a>
        </div>
        <div class="dashboard-card">
          <span class="eyebrow">Dar seguimiento</span>
          <h2>Consultar un folio</h2>
          <p>Si ya levantaste una solicitud, aquí puedes ver su estatus con el folio que se te dio.</p>
          <a href="/panel/solicitudes/consultar" class="btn btn-ghost-light">Consultar un folio</a>
        </div>
      </div>
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
                  <td><?= htmlspecialchars(folioSolicitud($s['id'])) ?></td>
                  <td><?= htmlspecialchars(etiquetaTipoSolicitud($s['tipo'])) ?></td>
                  <td><?= htmlspecialchars($s['asunto']) ?></td>
                  <td><span class="estatus-pill estatus-pill--<?= htmlspecialchars($s['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($s['estatus'])) ?></span></td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($s['creado_en']))) ?></td>
                  <td class="tabla-acciones">
                    <a href="/panel/solicitudes/detalle?id=<?= (int) $s['id'] ?>" class="btn-editar">Ver</a>
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
