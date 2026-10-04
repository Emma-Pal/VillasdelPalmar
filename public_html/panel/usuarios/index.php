<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

$title = 'Usuarios — Villas del Palmar';
$description = 'Villas y departamentos con sus titulares, y cuentas de Administración.';

$buscar = trim($_GET['buscar'] ?? '');
$villas = getVillas($buscar !== '' ? $buscar : null);
$administradores = getMesa();

// Para cada villa: si hay más recámaras físicas que registradas, o falta
// algún documento obligatorio (escritura siempre; relación de
// copropietarios solo si hay más de un titular), la fila se resalta para
// que Administración le dé seguimiento.
foreach ($villas as &$v) {
    $totalTitulares = count($v['titulares']);
    $v['_recamarasDiferencia'] = $v['recamaras_registradas'] !== null && $v['recamaras_fisicas'] !== null
        && (int) $v['recamaras_registradas'] !== (int) $v['recamaras_fisicas'];
    $v['_relacionRequerida'] = $totalTitulares > 1;
    $v['_escrituraFaltante'] = empty($v['escritura_archivo']);
    $v['_relacionFaltante'] = $v['_relacionRequerida'] && empty($v['relacion_copropietarios_archivo']);
    $v['_requiereRevision'] = $v['_recamarasDiferencia'] || $v['_escrituraFaltante'] || $v['_relacionFaltante'];
}
unset($v);
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
      <h1>Usuarios</h1>
      <p class="page-banner-lead">Villas y departamentos con sus titulares, y cuentas de Administración.</p>
    </div>
  </section>

  <section class="detail-sections">

    <div class="section-heading-fila">
      <div>
        <h2 style="margin-bottom: 4px;">Villas y departamentos</h2>
        <p class="form-nota" style="margin: 0;"><?= count($villas) ?> inmueble<?= count($villas) === 1 ? '' : 's' ?> dado<?= count($villas) === 1 ? '' : 's' ?> de alta.</p>
      </div>
    </div>

    <div class="section-heading-fila">
      <form method="GET" action="/panel/usuarios" class="buscador-wrap" style="flex: 1; min-width: 240px;">
        <span class="buscador-icono" aria-hidden="true">🔍</span>
        <input type="search" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar villa o nombre..." />
      </form>
      <button type="button" class="btn btn-primary" data-modal-open="modal-nueva-villa">+ Alta de villa</button>
    </div>

    <?php if (empty($villas)): ?>
      <p class="placeholder-note">No hay villas que coincidan.</p>
    <?php else: ?>
      <div class="tabla-wrap" data-reveal>
        <table class="tabla-pagos">
          <thead>
            <tr>
              <th>Villa</th>
              <th>Titulares</th>
              <th>Contacto</th>
              <th>Recámaras reg./fís.</th>
              <th>Capacidad</th>
              <th>Documentos</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($villas as $v): $titulares = $v['titulares']; $titular1 = $titulares[0] ?? null; ?>
              <tr class="<?= $v['_requiereRevision'] ? 'tabla-fila-alerta' : '' ?>">
                <td><span class="tabla-aviso-titulo">Villa <?= htmlspecialchars($v['villa']) ?></span></td>
                <td>
                  <?php if ($titular1): ?>
                    <span class="villa-titulares-nombres">
                      <?php foreach ($titulares as $t): ?>
                        <span><?= htmlspecialchars($t['nombre']) ?></span>
                      <?php endforeach; ?>
                    </span>
                    <span class="badge <?= count($titulares) > 1 ? 'badge--doc-reglamento' : '' ?>" style="margin-top: 4px; display: inline-block;">
                      <?= count($titulares) > 1 ? (count($titulares) - 1) . ' copropietario' . (count($titulares) > 2 ? 's' : '') : 'Propietario único' ?>
                    </span>
                  <?php else: ?>
                    <span class="form-nota">Sin titulares capturados</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php $hayContacto = false; foreach ($titulares as $t): if ($t['telefono'] || $t['correo']): $hayContacto = true; ?>
                    <span class="villa-contacto-linea"><?= htmlspecialchars($t['telefono'] ?: '—') ?> · <?= htmlspecialchars($t['correo'] ?: '—') ?></span>
                  <?php endif; endforeach; if (!$hayContacto): ?><span class="form-nota">—</span><?php endif; ?>
                </td>
                <td>
                  <?= (int) $v['recamaras_registradas'] ?>/<?= (int) $v['recamaras_fisicas'] ?>
                  <?php if ($v['_recamarasDiferencia']): ?><br /><span class="badge badge--alerta">Diferencia</span><?php endif; ?>
                </td>
                <td><?= (int) $v['capacidad_ocupacion'] ?> ocupantes</td>
                <td>
                  <span class="badge <?= $v['_escrituraFaltante'] ? 'badge--alerta' : 'badge--ok' ?>"><?= $v['_escrituraFaltante'] ? '⚠ Escritura pendiente' : '✓ Escritura' ?></span><br />
                  <?php if (!$v['_relacionRequerida']): ?>
                    <span class="form-nota">Relación: no aplica</span>
                  <?php elseif ($v['_relacionFaltante']): ?>
                    <span class="badge badge--alerta">⚠ Relación pendiente</span>
                  <?php else: ?>
                    <span class="badge badge--ok">✓ Relación de copropietarios</span>
                  <?php endif; ?>
                </td>
                <td class="tabla-acciones">
                  <button type="button" class="btn-editar" data-modal-open="modal-editar-villa-<?= (int) $v['id'] ?>">Editar</button>
                  <form action="/panel/usuarios/villa-eliminar?id=<?= (int) $v['id'] ?>" method="POST"
                        onsubmit="return confirm('¿Eliminar la villa <?= htmlspecialchars($v['villa']) ?> y todos sus titulares? Esto no se puede deshacer.');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="form-nota" style="margin-top: 10px;">Las filas marcadas indican una diferencia entre recámaras registradas y físicas, o un documento pendiente.</p>
    <?php endif; ?>

    <hr class="form-separador" style="margin: 40px 0;" />

    <div class="section-heading-fila">
      <h2 style="margin: 0;">Administradores</h2>
      <button type="button" class="btn btn-primary" data-modal-open="modal-nuevo-administrador">+ Nuevo administrador</button>
    </div>

    <div class="tabla-wrap" data-reveal>
      <table class="tabla-pagos">
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Usuario</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($administradores as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a['nombre']) ?></td>
              <td><?= htmlspecialchars($a['cargo'] ?: '—') ?></td>
              <td><?= htmlspecialchars($a['usuario']) ?></td>
              <td class="tabla-acciones">
                <button type="button" class="btn-editar" data-modal-open="modal-editar-administrador-<?= (int) $a['id'] ?>">Editar</button>
                <?php if ((int) $a['id'] !== (int) $usuario['id']): ?>
                  <form action="/panel/usuarios/eliminar?id=<?= (int) $a['id'] ?>" method="POST"
                        onsubmit="return confirm('¿Eliminar la cuenta de <?= htmlspecialchars(str_replace("'", '', $a['nombre'])) ?>? Esto no se puede deshacer.');">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                    <button type="submit" class="btn-eliminar">Eliminar</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- ===== Modal: Nuevo administrador ===== -->
  <div class="aviso-modal-overlay" id="modal-nuevo-administrador" hidden>
    <div class="aviso-modal">
      <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
      <div class="form-card">
        <span class="eyebrow">Usuarios · Administradores</span>
        <h2>Nuevo administrador</h2>
        <p class="form-nota" style="margin-bottom: 16px;">Cuenta con privilegios de administrador (acceso a todo el panel de gestión).</p>
        <form action="/panel/usuarios/administrador-guardar" method="POST" class="contact-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <?php $admin = []; include __DIR__ . '/../../partials/administrador-campos.php'; ?>
          <div class="modal-botones">
            <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
            <button type="submit" class="btn btn-primary">Crear administrador</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ===== Modales: Editar administrador (uno por administrador) ===== -->
  <?php foreach ($administradores as $a): ?>
    <div class="aviso-modal-overlay" id="modal-editar-administrador-<?= (int) $a['id'] ?>" hidden>
      <div class="aviso-modal">
        <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
        <div class="form-card">
          <span class="eyebrow">Usuarios · Administradores</span>
          <h2>Editar administrador</h2>
          <form action="/panel/usuarios/administrador-guardar" method="POST" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>" />
            <?php $admin = $a; include __DIR__ . '/../../partials/administrador-campos.php'; ?>
            <div class="modal-botones">
              <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- ===== Modal: Alta de villa ===== -->
  <div class="aviso-modal-overlay" id="modal-nueva-villa" hidden>
    <div class="aviso-modal aviso-modal--ancho">
      <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
      <div class="form-card">
        <span class="eyebrow">Usuarios · Alta de villa o departamento</span>
        <h2>Alta de villa</h2>
        <p class="form-nota" style="margin-bottom: 16px;">Los campos con * son obligatorios.</p>
        <form action="/panel/usuarios/villa-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <?php $villa = []; include __DIR__ . '/../../partials/villa-campos.php'; ?>
          <div class="modal-botones">
            <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar villa</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ===== Modales: Editar villa (uno por villa) ===== -->
  <?php foreach ($villas as $v): ?>
    <div class="aviso-modal-overlay" id="modal-editar-villa-<?= (int) $v['id'] ?>" hidden>
      <div class="aviso-modal aviso-modal--ancho">
        <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
        <div class="form-card">
          <span class="eyebrow">Usuarios · Editar villa</span>
          <h2>Villa <?= htmlspecialchars($v['villa']) ?></h2>
          <p class="form-nota" style="margin-bottom: 16px;">Los campos con * son obligatorios.</p>
          <form action="/panel/usuarios/villa-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <input type="hidden" name="id" value="<?= (int) $v['id'] ?>" />
            <?php $villa = $v; include __DIR__ . '/../../partials/villa-campos.php'; ?>
            <div class="modal-botones">
              <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- ===== Lightbox: ver un documento sin salir de la página ===== -->
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
