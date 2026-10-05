<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Registro de estancia — Villas del Palmar';
$description = 'Registra ante la Administración cuándo llegas a tu villa o departamento, quiénes te acompañan y con qué vehículos y mascotas.';

$esMesa = $usuario['tipo'] === 'mesa';
$villaPropia = null;
$villas = [];
$proximas = [];
$historial = [];
$estanciasMesa = [];
$estatusFiltro = null;

if ($esMesa) {
    $villas = getVillas();
    $estatusValidos = ['en_revision', 'confirmado', 'concluido'];
    $estatusFiltro = in_array($_GET['estatus'] ?? '', $estatusValidos, true) ? $_GET['estatus'] : null;
    $estanciasMesa = getEstancias($estatusFiltro);
} else {
    $villaPropia = getVillaPorId($usuario['id']);
    $estancias = getEstanciasDeVilla($usuario['id']);
    foreach ($estancias as $e) {
        if ($e['estatus'] === 'concluido') {
            $historial[] = $e;
        } else {
            $proximas[] = $e;
        }
    }
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
      <span class="eyebrow">Propietarios</span>
      <h1>Registro de estancia</h1>
      <p class="page-banner-lead">Registra ante la Administración cuándo llegas a tu villa o departamento, quiénes te acompañan y con qué vehículos y mascotas, para preparar tu acceso.</p>
    </div>
  </section>

  <section class="detail-sections">

    <div class="form-card" data-reveal>
      <span class="form-card-numero">Regla de ocupación</span>
      <p style="margin: 10px 0 18px;">
        La capacidad de tu villa se mide en <strong>equivalentes de adulto</strong>. Cada adulto cuenta 1 y cada menor de edad cuenta ½, porque 1 adulto equivale a 2 menores. Los infantes de 3 años o menos no cuentan. Siempre debe haber al menos un adulto responsable. Lo que rebase la capacidad se registra como excedente, en adultos o menores, con la misma equivalencia.
      </p>
      <span class="campo-grupo-label">Cuota por excedente · por noche</span>
      <p style="margin: 6px 0 0;">
        Adulto excedente <strong>$<?= number_format(CUOTA_ADULTO_EXCEDENTE, 2) ?></strong> &nbsp;·&nbsp;
        Menor excedente <strong>$<?= number_format(CUOTA_MENOR_EXCEDENTE, 2) ?></strong>
      </p>
    </div>

    <div class="section-heading-fila" style="margin-top: 32px;">
      <h2 style="margin: 0;"><?= $esMesa ? 'Registros de estancia' : 'Mis próximas estancias' ?></h2>
      <button type="button" class="btn btn-primary" data-modal-open="modal-nuevo-registro-estancia">+ Nuevo registro de estancia</button>
    </div>

    <?php if ($esMesa): ?>
      <div class="categoria-tabs" data-reveal>
        <a href="/panel/registro-estancia" class="categoria-tab <?= !$estatusFiltro ? 'is-active' : '' ?>">Todas</a>
        <a href="/panel/registro-estancia?estatus=en_revision" class="categoria-tab <?= $estatusFiltro === 'en_revision' ? 'is-active' : '' ?>">En revisión</a>
        <a href="/panel/registro-estancia?estatus=confirmado" class="categoria-tab <?= $estatusFiltro === 'confirmado' ? 'is-active' : '' ?>">Confirmadas</a>
        <a href="/panel/registro-estancia?estatus=concluido" class="categoria-tab <?= $estatusFiltro === 'concluido' ? 'is-active' : '' ?>">Concluidas</a>
      </div>

      <?php if (empty($estanciasMesa)): ?>
        <p class="placeholder-note">No hay registros en esta vista.</p>
      <?php else: ?>
        <div class="tabla-wrap" data-reveal>
          <table class="tabla-pagos">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Inmueble</th>
                <th>Responsable</th>
                <th>Llegada — Salida</th>
                <th>Ocupantes</th>
                <th>Estatus</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($estanciasMesa as $e): ?>
                <tr>
                  <td><?= htmlspecialchars(folioEstancia($e['id'])) ?></td>
                  <td>Villa <?= htmlspecialchars($e['villa_numero']) ?></td>
                  <td><?= htmlspecialchars($e['responsable_nombre']) ?></td>
                  <td><?= htmlspecialchars(date('d/m', strtotime($e['fecha_llegada']))) ?> – <?= htmlspecialchars(date('d/m/Y', strtotime($e['fecha_salida']))) ?></td>
                  <td><?= (int) $e['adultos'] + (int) $e['menores'] + (int) $e['infantes'] ?> personas</td>
                  <td><span class="estatus-pill estatus-pill--<?= htmlspecialchars($e['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($e['estatus'])) ?></span></td>
                  <td class="tabla-acciones"><a href="/panel/registro-estancia/detalle?id=<?= (int) $e['id'] ?>" class="btn-editar">Ver detalle</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p class="form-nota" style="margin-top: -8px;">Registros enviados y su estado de confirmación.</p>

      <?php if (empty($proximas)): ?>
        <p class="placeholder-note">No tienes estancias próximas registradas.</p>
      <?php else: ?>
        <div class="estancia-card-lista" data-reveal>
          <?php foreach ($proximas as $e): ?>
            <div class="estancia-card">
              <div class="estancia-card-encabezado">
                <div class="convocatoria-tags" style="margin: 0;">
                  <span class="estatus-pill estatus-pill--<?= htmlspecialchars($e['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($e['estatus'])) ?></span>
                  <span class="badge">Folio <?= htmlspecialchars(folioEstancia($e['id'])) ?></span>
                </div>
                <a href="/panel/registro-estancia/detalle?id=<?= (int) $e['id'] ?>" class="btn btn-ghost-light">Ver detalle</a>
              </div>
              <h3 style="margin: 10px 0 2px;">Villa <?= htmlspecialchars($villaPropia['villa'] ?? '') ?></h3>
              <p class="convocatoria-meta">Responsable: <?= htmlspecialchars($e['responsable_nombre']) ?></p>

              <div class="estancia-card-datos">
                <div>
                  <span class="campo-grupo-label">Llegada</span>
                  <p style="margin: 2px 0 0;"><?= htmlspecialchars(date('d/m/Y', strtotime($e['fecha_llegada']))) ?> · <?= htmlspecialchars(substr($e['hora_llegada'], 0, 5)) ?></p>
                </div>
                <div>
                  <span class="campo-grupo-label">Salida</span>
                  <p style="margin: 2px 0 0;"><?= htmlspecialchars(date('d/m/Y', strtotime($e['fecha_salida']))) ?> · <?= htmlspecialchars(substr($e['hora_salida'], 0, 5)) ?></p>
                </div>
                <div>
                  <span class="campo-grupo-label">Vehículo</span>
                  <p style="margin: 2px 0 0;"><?= !empty($e['vehiculos']) ? htmlspecialchars($e['vehiculos'][0]['placas']) : 'Sin vehículo' ?></p>
                </div>
              </div>

              <div class="convocatoria-tags" style="margin-top: 12px;">
                <span class="badge"><?= (int) $e['adultos'] ?> adultos</span>
                <span class="badge"><?= (int) $e['menores'] ?> menores de edad</span>
                <span class="badge"><?= (int) $e['infantes'] ?> infante(s) (3 años o menos)</span>
                <?php if (!empty($e['mascota_tipo'])): ?><span class="badge"><?= (int) $e['mascota_cantidad'] ?> mascota · <?= htmlspecialchars(ucfirst($e['mascota_tipo'])) ?></span><?php endif; ?>
                <?php if ($e['reglamento_aceptado']): ?><span class="badge badge--ok">✓ Reglamento aceptado</span><?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <h2 style="margin-top: 40px;">Historial</h2>
      <p class="form-nota" style="margin-top: -8px;">Estancias anteriores registradas.</p>

      <?php if (empty($historial)): ?>
        <p class="placeholder-note">Todavía no hay estancias concluidas.</p>
      <?php else: ?>
        <div class="tabla-wrap" data-reveal>
          <table class="tabla-pagos">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Llegada – Salida</th>
                <th>Ocupantes</th>
                <th>Estatus</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($historial as $e): ?>
                <tr>
                  <td><?= htmlspecialchars(folioEstancia($e['id'])) ?></td>
                  <td><?= htmlspecialchars(date('d/m', strtotime($e['fecha_llegada']))) ?> – <?= htmlspecialchars(date('d/m/Y', strtotime($e['fecha_salida']))) ?></td>
                  <td><?= (int) $e['adultos'] + (int) $e['menores'] + (int) $e['infantes'] ?> personas<?php if (!empty($e['mascota_tipo'])): ?> · 1 mascota<?php endif; ?></td>
                  <td><span class="estatus-pill estatus-pill--concluido">Concluida</span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </section>

  <!-- ===== Modal: Nuevo registro de estancia ===== -->
  <div class="aviso-modal-overlay" id="modal-nuevo-registro-estancia" hidden>
    <div class="aviso-modal aviso-modal--ancho">
      <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
      <div class="form-card">
        <span class="eyebrow">Propietarios · Registro de estancia</span>
        <h2>Nuevo registro de estancia</h2>
        <p class="form-nota" style="margin-bottom: 16px;">Los campos con * son obligatorios.</p>
        <form action="/panel/registro-estancia/guardar" method="POST" enctype="multipart/form-data" class="contact-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <?php include __DIR__ . '/../../partials/estancia-campos.php'; ?>
          <div class="modal-botones">
            <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
            <button type="submit" class="btn btn-primary">Enviar registro</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
