<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$esMesa = $usuario['tipo'] === 'mesa';
$estancia = getEstanciaPorId((int) ($_GET['id'] ?? 0));

if (!$estancia || (!$esMesa && (int) $estancia['usuario_id'] !== (int) $usuario['id'])) {
    header('Location: /panel/registro-estancia');
    exit;
}

$title = folioEstancia($estancia['id']) . ' — Villas del Palmar';
$description = 'Detalle de un registro de estancia de Villas del Palmar.';

if ($esMesa && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();
    $estatusValidos = ['en_revision', 'confirmado', 'concluido'];
    $estatus = in_array($_POST['estatus'] ?? '', $estatusValidos, true) ? $_POST['estatus'] : $estancia['estatus'];
    actualizarEstatusEstancia($estancia['id'], $estatus);
    header('Location: /panel/registro-estancia/detalle?id=' . $estancia['id']);
    exit;
}

$hrefIdentificacion = '/panel/registro-estancia/responsable-archivo?id=' . (int) $estancia['id'];
$esImagenIdentificacion = esImagen($estancia['responsable_id_archivo_nombre_original']);
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
      <a href="/panel/registro-estancia" class="back-link">← Volver a registro de estancia</a>
      <span class="eyebrow">Villa <?= htmlspecialchars($estancia['villa_numero']) ?></span>
      <h1><?= htmlspecialchars(folioEstancia($estancia['id'])) ?></h1>
      <span class="estatus-pill estatus-pill--<?= htmlspecialchars($estancia['estatus']) ?>"><?= htmlspecialchars(etiquetaEstatus($estancia['estatus'])) ?></span>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card-grupo" style="max-width: 680px; margin: 0 auto;">

      <div class="form-card" data-reveal>
        <span class="form-card-numero">Inmueble y fechas</span>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 12px;">
          <div><span class="campo-grupo-label">Llegada</span><p><?= htmlspecialchars(date('d/m/Y', strtotime($estancia['fecha_llegada']))) ?> · <?= htmlspecialchars(substr($estancia['hora_llegada'], 0, 5)) ?></p></div>
          <div><span class="campo-grupo-label">Salida</span><p><?= htmlspecialchars(date('d/m/Y', strtotime($estancia['fecha_salida']))) ?> · <?= htmlspecialchars(substr($estancia['hora_salida'], 0, 5)) ?></p></div>
        </div>
      </div>

      <div class="form-card" data-reveal>
        <span class="form-card-numero">Responsable de la estancia</span>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 12px;">
          <div><span class="campo-grupo-label">Nombre</span><p><?= htmlspecialchars($estancia['responsable_nombre']) ?></p></div>
          <div><span class="campo-grupo-label">Teléfono</span><p><?= htmlspecialchars($estancia['responsable_telefono'] ?: '—') ?></p></div>
          <div>
            <span class="campo-grupo-label">Identificación</span><br />
            <?php if ($esImagenIdentificacion): ?>
              <button type="button" class="btn-editar" data-lightbox-src="<?= htmlspecialchars($hrefIdentificacion) ?>" data-lightbox-nombre="Identificación del responsable">Ver</button>
            <?php else: ?>
              <a href="<?= htmlspecialchars($hrefIdentificacion) ?>" target="_blank" rel="noopener" class="btn-editar">Ver</a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="form-card" data-reveal>
        <span class="form-card-numero">Ocupantes</span>
        <p style="margin: 10px 0;">
          <?= (int) $estancia['adultos'] ?> adultos · <?= (int) $estancia['menores'] ?> menores de edad · <?= (int) $estancia['infantes'] ?> infante(s) ·
          Ocupación: <?= rtrim(rtrim((string) $estancia['ocupacion_equivalente'], '0'), '.') ?> de <?= (int) $estancia['capacidad_villa'] ?> equivalentes
        </p>
        <?php if (!empty($estancia['acompanantes'])): ?>
          <span class="campo-grupo-label">Acompañantes</span>
          <ul style="margin: 8px 0 0; padding-left: 20px;">
            <?php foreach ($estancia['acompanantes'] as $a): ?>
              <li><?= htmlspecialchars($a['nombre']) ?> — <?= $a['tipo'] === 'adulto' ? 'Adulto' : ($a['tipo'] === 'menor' ? 'Menor de edad' : 'Infante') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if ((int) $estancia['adultos_excedentes'] > 0 || (int) $estancia['menores_excedentes'] > 0): ?>
          <p class="candado-aviso" style="margin-top: 14px;">
            ⚠ Excedente: <?= (int) $estancia['adultos_excedentes'] ?> adulto(s), <?= (int) $estancia['menores_excedentes'] ?> menor(es). Cuota: $<?= number_format((float) $estancia['cuota_excedente'], 2) ?> MXN.
            <?= $estancia['excedente_aceptado'] ? '✓ Aceptada por el responsable.' : '— aún no aceptada.' ?>
          </p>
        <?php endif; ?>
      </div>

      <div class="form-card" data-reveal>
        <span class="form-card-numero">Vehículos y mascotas</span>
        <?php if (!empty($estancia['vehiculos'])): ?>
          <ul style="margin: 10px 0 0; padding-left: 20px;">
            <?php foreach ($estancia['vehiculos'] as $v): ?>
              <li><?= htmlspecialchars($v['placas']) ?> · <?= htmlspecialchars(ucfirst($v['tipo'])) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p style="margin: 10px 0 0;">Sin vehículo.</p>
        <?php endif; ?>
        <p style="margin: 10px 0 0;">
          <?php if (!empty($estancia['mascota_tipo'])): ?>
            <?= (int) $estancia['mascota_cantidad'] ?> × <?= htmlspecialchars(ucfirst($estancia['mascota_tipo'])) ?><?= $estancia['mascota_raza_tamano'] ? ' (' . htmlspecialchars($estancia['mascota_raza_tamano']) . ')' : '' ?>
          <?php else: ?>
            Sin mascota.
          <?php endif; ?>
        </p>
      </div>

      <?php if (!empty($estancia['comentarios'])): ?>
        <div class="form-card" data-reveal>
          <span class="form-card-numero">Comentarios para la Administración</span>
          <p style="margin: 10px 0 0;"><?= nl2brSeguro($estancia['comentarios']) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($esMesa): ?>
        <div class="form-card" data-reveal>
          <span class="form-card-numero">Estatus</span>
          <form action="/panel/registro-estancia/detalle?id=<?= (int) $estancia['id'] ?>" method="POST" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <label>
              Estatus del registro
              <select name="estatus" required>
                <option value="en_revision" <?= $estancia['estatus'] === 'en_revision' ? 'selected' : '' ?>>En revisión</option>
                <option value="confirmado" <?= $estancia['estatus'] === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                <option value="concluido" <?= $estancia['estatus'] === 'concluido' ? 'selected' : '' ?>>Concluida</option>
              </select>
            </label>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </form>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- ===== Lightbox: ver la identificación sin salir de la página ===== -->
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
