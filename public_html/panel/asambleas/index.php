<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Asambleas — Villas del Palmar';
$description = 'Convocatorias y actas firmadas de las asambleas de propietarios.';

$esMesa = $usuario['tipo'] === 'mesa';

// ===== Convocatorias: filtro por tipo (pills Todas/Ordinarias/Extraordinarias) =====
$tipoConvocatoriaFiltro = in_array($_GET['tipo_convocatoria'] ?? '', ['ordinaria', 'extraordinaria'], true)
    ? $_GET['tipo_convocatoria']
    : null;

$convocatorias = getPublicacionesAsamblea('convocatoria');
if ($tipoConvocatoriaFiltro) {
    $convocatorias = array_values(array_filter(
        $convocatorias,
        fn($c) => $c['tipo_asamblea'] === $tipoConvocatoriaFiltro
    ));
}

// ===== Actas: filtros por año y tipo =====
$actasTodas = getPublicacionesAsamblea('acta');
$aniosDisponibles = array_values(array_unique(array_filter(array_map(
    fn($a) => $a['anio_asamblea'],
    $actasTodas
))));
rsort($aniosDisponibles);

$anioFiltro = (int) ($_GET['anio'] ?? 0);
$tipoActaFiltro = in_array($_GET['tipo_acta'] ?? '', ['ordinaria', 'extraordinaria'], true) ? $_GET['tipo_acta'] : null;

$actas = array_values(array_filter($actasTodas, function ($a) use ($anioFiltro, $tipoActaFiltro) {
    if ($anioFiltro && (int) $a['anio_asamblea'] !== $anioFiltro) return false;
    if ($tipoActaFiltro && $a['tipo_asamblea'] !== $tipoActaFiltro) return false;
    return true;
}));

$mesesCortos = ['', 'ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

// Para que las pestañas de Convocatorias no se lleven de encuentro los
// filtros de Actas (y viceversa, vía el hidden de más abajo) al cambiar uno
// de los dos grupos de filtros independientes de esta misma página.
$sufijoActas = '';
if ($anioFiltro) $sufijoActas .= '&anio=' . $anioFiltro;
if ($tipoActaFiltro) $sufijoActas .= '&tipo_acta=' . urlencode($tipoActaFiltro);
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
      <h1>Asambleas</h1>
      <p class="page-banner-lead">Convocatorias y actas firmadas de las asambleas de propietarios.</p>
    </div>
  </section>

  <section class="detail-sections">

    <!-- ===== Convocatorias ===== -->
    <div class="asambleas-seccion" id="convocatorias" data-reveal>
      <div class="section-heading-fila">
        <div>
          <h2>Convocatorias</h2>
          <p class="seccion-subtexto">Invitaciones a asamblea con su documento oficial adjunto.</p>
        </div>
        <?php if ($esMesa): ?>
          <button type="button" class="btn btn-primary" data-modal-open="modal-nueva-convocatoria">+ Nueva convocatoria</button>
        <?php endif; ?>
      </div>

      <div class="categoria-tabs" style="margin-bottom: 24px;">
        <a href="/panel/asambleas?<?= substr($sufijoActas, 1) ?>#convocatorias" class="categoria-tab <?= !$tipoConvocatoriaFiltro ? 'is-active' : '' ?>">Todas</a>
        <a href="/panel/asambleas?tipo_convocatoria=ordinaria<?= $sufijoActas ?>#convocatorias" class="categoria-tab <?= $tipoConvocatoriaFiltro === 'ordinaria' ? 'is-active' : '' ?>">Ordinarias</a>
        <a href="/panel/asambleas?tipo_convocatoria=extraordinaria<?= $sufijoActas ?>#convocatorias" class="categoria-tab <?= $tipoConvocatoriaFiltro === 'extraordinaria' ? 'is-active' : '' ?>">Extraordinarias</a>
      </div>

      <?php if (empty($convocatorias)): ?>
        <p class="placeholder-note">Todavía no hay convocatorias.</p>
      <?php else: ?>
        <div class="convocatorias-lista">
          <?php foreach ($convocatorias as $c): ?>
            <?php
            $esProxima = !empty($c['fecha_evento']) && $c['fecha_evento'] >= date('Y-m-d');
            $mesCorto = !empty($c['fecha_evento']) ? $mesesCortos[(int) date('n', strtotime($c['fecha_evento']))] : '—';
            $diaNum = !empty($c['fecha_evento']) ? date('d', strtotime($c['fecha_evento'])) : '--';
            $anioNum = !empty($c['fecha_evento']) ? date('Y', strtotime($c['fecha_evento'])) : '----';
            $documento = $c['archivos'][0] ?? null;
            ?>
            <div class="convocatoria-fila">
              <div class="fecha-bloque">
                <span class="fecha-bloque-mes"><?= htmlspecialchars($mesCorto) ?></span>
                <span class="fecha-bloque-dia"><?= htmlspecialchars($diaNum) ?></span>
                <span class="fecha-bloque-anio"><?= htmlspecialchars($anioNum) ?></span>
              </div>
              <div class="convocatoria-info">
                <div class="convocatoria-tags">
                  <span class="badge badge--tipo-<?= htmlspecialchars($c['tipo_asamblea'] ?? 'ordinaria') ?>"><?= htmlspecialchars(etiquetaTipoAsamblea($c['tipo_asamblea'])) ?></span>
                  <span class="badge <?= $esProxima ? 'badge--estado-proxima' : 'badge--estado-celebrada' ?>"><?= $esProxima ? 'Próxima' : 'Celebrada' ?></span>
                </div>
                <h3><?= htmlspecialchars($c['titulo']) ?></h3>
                <p class="convocatoria-meta">
                  <?= !empty($c['hora_evento']) ? htmlspecialchars(date('h:i a', strtotime($c['hora_evento']))) . ' · ' : '' ?>
                  <?= htmlspecialchars($c['lugar_evento'] ?? '') ?> · Publicada <?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha']))) ?> por <?= htmlspecialchars($c['autor_nombre']) ?>
                </p>
                <?php if (!empty($c['cuerpo'])): ?>
                  <p class="convocatoria-mensaje"><?= nl2brSeguro($c['cuerpo']) ?></p>
                <?php endif; ?>
                <?php if ($esMesa): ?>
                  <p class="publicacion-acciones" style="margin-top: 10px;">
                    <button type="button" class="btn-editar" data-modal-open="modal-editar-convocatoria-<?= (int) $c['id'] ?>">Editar</button>
                    <form action="/panel/avisos/eliminar?id=<?= (int) $c['id'] ?>" method="POST"
                          onsubmit="return confirm('¿Eliminar esta convocatoria? Esto no se puede deshacer.');">
                      <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                      <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                  </p>
                <?php endif; ?>
              </div>
              <?php if ($documento): ?>
                <a href="/panel/archivo?id=<?= (int) $documento['id'] ?>" class="btn btn-ghost-light convocatoria-pdf-btn">⬇ Ver convocatoria (PDF)</a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ===== Actas firmadas ===== -->
    <div class="asambleas-seccion" id="actas" data-reveal>
      <div class="section-heading-fila">
        <div>
          <h2>Actas firmadas</h2>
          <p class="seccion-subtexto">Documentos de cada asamblea, organizados por año y tipo.</p>
        </div>
        <?php if ($esMesa): ?>
          <button type="button" class="btn btn-primary" data-modal-open="modal-nueva-acta">+ Nueva acta</button>
        <?php endif; ?>
      </div>

      <form method="GET" action="/panel/asambleas#actas" class="asambleas-filtros">
        <input type="hidden" name="tipo_convocatoria" value="<?= htmlspecialchars((string) $tipoConvocatoriaFiltro) ?>" />
        <label>
          Año
          <select name="anio" onchange="this.form.submit();">
            <option value="">Todos</option>
            <?php foreach ($aniosDisponibles as $a): ?>
              <option value="<?= (int) $a ?>" <?= $anioFiltro === (int) $a ? 'selected' : '' ?>><?= (int) $a ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>
          Tipo de asamblea
          <select name="tipo_acta" onchange="this.form.submit();">
            <option value="">Todas</option>
            <option value="ordinaria" <?= $tipoActaFiltro === 'ordinaria' ? 'selected' : '' ?>>Ordinarias</option>
            <option value="extraordinaria" <?= $tipoActaFiltro === 'extraordinaria' ? 'selected' : '' ?>>Extraordinarias</option>
          </select>
        </label>
      </form>

      <?php if (empty($actas)): ?>
        <p class="placeholder-note">Todavía no hay actas.</p>
      <?php else: ?>
        <div class="tabla-wrap">
          <table class="tabla-pagos">
            <thead>
              <tr>
                <th>Año</th>
                <th>Concepto del documento</th>
                <th>Tipo</th>
                <th>Publicado</th>
                <th></th>
                <?php if ($esMesa): ?><th></th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($actas as $a): ?>
                <?php $documento = $a['archivos'][0] ?? null; ?>
                <tr>
                  <td><?= (int) $a['anio_asamblea'] ?></td>
                  <td><?= htmlspecialchars($a['titulo']) ?></td>
                  <td><span class="badge badge--tipo-<?= htmlspecialchars($a['tipo_asamblea'] ?? 'ordinaria') ?>"><?= htmlspecialchars(etiquetaTipoAsamblea($a['tipo_asamblea'])) ?></span></td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($a['fecha']))) ?></td>
                  <td>
                    <?php if ($documento): ?>
                      <a href="/panel/archivo?id=<?= (int) $documento['id'] ?>">Descargar PDF</a>
                    <?php endif; ?>
                  </td>
                  <?php if ($esMesa): ?>
                    <td class="tabla-acciones">
                      <button type="button" class="btn-editar" data-modal-open="modal-editar-acta-<?= (int) $a['id'] ?>">Editar</button>
                      <form action="/panel/avisos/eliminar?id=<?= (int) $a['id'] ?>" method="POST"
                            onsubmit="return confirm('¿Eliminar esta acta? Esto no se puede deshacer.');">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                      </form>
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

  </section>

  <?php if ($esMesa): ?>
    <!-- ===== Modal: Nueva convocatoria ===== -->
    <div class="aviso-modal-overlay" id="modal-nueva-convocatoria" hidden>
      <div class="aviso-modal">
        <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
        <div class="form-card">
          <span class="eyebrow">Asambleas · Convocatorias</span>
          <h2>Nueva convocatoria</h2>
          <form action="/panel/asambleas/convocatoria-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <?php $conv = []; include __DIR__ . '/../../partials/asamblea-campos-convocatoria.php'; ?>
            <div class="modal-botones">
              <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-primary">Publicar convocatoria</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ===== Modal: Nueva acta ===== -->
    <div class="aviso-modal-overlay" id="modal-nueva-acta" hidden>
      <div class="aviso-modal">
        <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
        <div class="form-card">
          <span class="eyebrow">Asambleas · Actas firmadas</span>
          <h2>Nueva acta</h2>
          <form action="/panel/asambleas/acta-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <?php $acta = []; include __DIR__ . '/../../partials/asamblea-campos-acta.php'; ?>
            <div class="modal-botones">
              <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-primary">Publicar acta</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ===== Modales: Editar convocatoria (uno por convocatoria) ===== -->
    <?php foreach ($convocatorias as $c): ?>
      <div class="aviso-modal-overlay" id="modal-editar-convocatoria-<?= (int) $c['id'] ?>" hidden>
        <div class="aviso-modal">
          <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
          <div class="form-card">
            <span class="eyebrow">Asambleas · Convocatorias</span>
            <h2>Editar convocatoria</h2>
            <form action="/panel/asambleas/convocatoria-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
              <input type="hidden" name="id" value="<?= (int) $c['id'] ?>" />
              <?php $conv = $c; include __DIR__ . '/../../partials/asamblea-campos-convocatoria.php'; ?>
              <div class="modal-botones">
                <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- ===== Modales: Editar acta (uno por acta) ===== -->
    <?php foreach ($actas as $a): ?>
      <div class="aviso-modal-overlay" id="modal-editar-acta-<?= (int) $a['id'] ?>" hidden>
        <div class="aviso-modal">
          <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
          <div class="form-card">
            <span class="eyebrow">Asambleas · Actas firmadas</span>
            <h2>Editar acta</h2>
            <form action="/panel/asambleas/acta-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
              <input type="hidden" name="id" value="<?= (int) $a['id'] ?>" />
              <?php $acta = $a; include __DIR__ . '/../../partials/asamblea-campos-acta.php'; ?>
              <div class="modal-botones">
                <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

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
