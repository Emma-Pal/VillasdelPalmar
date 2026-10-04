<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Directorio — Villas del Palmar';
$description = 'Personal del condominio e integrantes del Comité.';

$esMesa = $usuario['tipo'] === 'mesa';
$tab = ($_GET['tab'] ?? 'personal') === 'comite' ? 'comite' : 'personal';

// ===== Personal del condominio =====
$areaFiltro = in_array($_GET['area'] ?? '', AREAS_COLABORADOR, true) ? $_GET['area'] : null;
$estatusFiltro = ($_GET['estatus'] ?? 'activo') === 'baja' ? 'baja' : 'activo';
$buscarPersonal = trim($_GET['buscar'] ?? '');
$colaboradores = getColaboradores($areaFiltro, $estatusFiltro, $buscarPersonal !== '' ? $buscarPersonal : null);
$totalPersonal = count(getColaboradores(null, null, null));

// ===== Integrantes del Comité =====
$departamentoFiltro = in_array($_GET['departamento'] ?? '', DEPARTAMENTOS_COMITE, true) ? $_GET['departamento'] : null;
$buscarComite = trim($_GET['buscar_comite'] ?? '');
$miembros = getComiteMiembros($departamentoFiltro, $buscarComite !== '' ? $buscarComite : null);
$totalComite = count(getComiteMiembros());
$cargosSugeridos = array_unique(getCargosComiteSugeridos());
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
      <h1>Directorio</h1>
      <p class="page-banner-lead">Personal del condominio e integrantes del Comité.</p>
    </div>
  </section>

  <section class="detail-sections">
    <div class="categoria-tabs">
      <a href="/panel/directorio?tab=personal" class="categoria-tab <?= $tab === 'personal' ? 'is-active' : '' ?>">Personal del condominio <span class="tab-contador"><?= $totalPersonal ?></span></a>
      <a href="/panel/directorio?tab=comite" class="categoria-tab <?= $tab === 'comite' ? 'is-active' : '' ?>">Integrantes del Comité <span class="tab-contador"><?= $totalComite ?></span></a>
    </div>

    <?php if ($tab === 'personal'): ?>

      <div class="section-heading-fila">
        <form method="GET" action="/panel/directorio" class="buscador-wrap" style="flex: 1; min-width: 240px;">
          <input type="hidden" name="tab" value="personal" />
          <?php if ($areaFiltro): ?><input type="hidden" name="area" value="<?= htmlspecialchars($areaFiltro) ?>" /><?php endif; ?>
          <?php if ($estatusFiltro === 'baja'): ?><input type="hidden" name="estatus" value="baja" /><?php endif; ?>
          <span class="buscador-icono" aria-hidden="true">🔍</span>
          <input type="search" name="buscar" value="<?= htmlspecialchars($buscarPersonal) ?>" placeholder="Buscar por nombre, puesto o área..." />
        </form>
        <?php if ($esMesa): ?>
          <button type="button" class="btn btn-primary" data-modal-open="modal-nuevo-colaborador">+ Nuevo colaborador</button>
        <?php endif; ?>
      </div>

      <div class="section-heading-fila">
        <div class="categoria-tabs" style="margin-bottom: 0;">
          <a href="/panel/directorio?tab=personal<?= $estatusFiltro === 'baja' ? '&estatus=baja' : '' ?>" class="categoria-tab <?= !$areaFiltro ? 'is-active' : '' ?>">Todas las áreas</a>
          <?php foreach (AREAS_COLABORADOR as $a): ?>
            <a href="/panel/directorio?tab=personal&area=<?= urlencode($a) . ($estatusFiltro === 'baja' ? '&estatus=baja' : '') ?>" class="categoria-tab <?= $areaFiltro === $a ? 'is-active' : '' ?>"><?= htmlspecialchars(etiquetaAreaColaborador($a)) ?></a>
          <?php endforeach; ?>
        </div>
        <div class="categoria-tabs" style="margin-bottom: 0;">
          <a href="/panel/directorio?tab=personal<?= $areaFiltro ? '&area=' . urlencode($areaFiltro) : '' ?>" class="categoria-tab <?= $estatusFiltro === 'activo' ? 'is-active' : '' ?>">Activos</a>
          <a href="/panel/directorio?tab=personal&estatus=baja<?= $areaFiltro ? '&area=' . urlencode($areaFiltro) : '' ?>" class="categoria-tab <?= $estatusFiltro === 'baja' ? 'is-active' : '' ?>">Bajas</a>
        </div>
      </div>

      <?php if ($esMesa): ?>
        <p class="candado-aviso">🔒 Vista de Comité y Administración. Los propietarios solo ven nombre, puesto, área y fecha de ingreso. El teléfono, la ficha completa y los documentos están restringidos.</p>
      <?php endif; ?>

      <?php if (empty($colaboradores)): ?>
        <p class="placeholder-note">No hay colaboradores que coincidan.</p>
      <?php else: ?>
        <div class="tabla-wrap">
          <table class="tabla-pagos">
            <thead>
              <tr>
                <th>Colaborador</th>
                <th>Área / Departamento</th>
                <th>Fecha de ingreso</th>
                <?php if ($esMesa): ?><th>Teléfono 🔒</th><?php endif; ?>
                <th>Estatus</th>
                <?php if ($esMesa): ?><th></th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($colaboradores as $c): ?>
                <tr>
                  <td>
                    <span class="directorio-persona">
                      <span class="directorio-avatar"><?= htmlspecialchars(iniciales($c['nombre'])) ?></span>
                      <span>
                        <span class="tabla-aviso-titulo"><?= htmlspecialchars($c['nombre']) ?></span>
                        <span class="tabla-subtexto"><?= htmlspecialchars($c['puesto']) ?></span>
                      </span>
                    </span>
                  </td>
                  <td><span class="badge badge--doc-reglamento"><?= htmlspecialchars(etiquetaAreaColaborador($c['area'])) ?></span></td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['fecha_ingreso']))) ?></td>
                  <?php if ($esMesa): ?><td><?= htmlspecialchars($c['telefono'] ?: '—') ?></td><?php endif; ?>
                  <td><span class="badge <?= $c['estatus'] === 'activo' ? 'badge--ok' : 'badge--alerta' ?>"><?= $c['estatus'] === 'activo' ? 'Activo' : 'Baja' ?></span></td>
                  <?php if ($esMesa): ?>
                    <td class="tabla-acciones">
                      <a href="/panel/directorio/ficha?id=<?= (int) $c['id'] ?>" class="btn-editar">Ver ficha</a>
                      <form action="/panel/directorio/colaborador-eliminar?id=<?= (int) $c['id'] ?>" method="POST"
                            onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars(str_replace("'", '', $c['nombre'])) ?> del directorio? Esto no se puede deshacer.');">
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

    <?php else: ?>

      <div class="section-heading-fila" id="comite">
        <form method="GET" action="/panel/directorio" class="buscador-wrap" style="flex: 1; min-width: 240px;">
          <input type="hidden" name="tab" value="comite" />
          <?php if ($departamentoFiltro): ?><input type="hidden" name="departamento" value="<?= htmlspecialchars($departamentoFiltro) ?>" /><?php endif; ?>
          <span class="buscador-icono" aria-hidden="true">🔍</span>
          <input type="search" name="buscar_comite" value="<?= htmlspecialchars($buscarComite) ?>" placeholder="Buscar integrante..." />
        </form>
        <?php if ($esMesa): ?>
          <button type="button" class="btn btn-primary" data-modal-open="modal-nuevo-comite">+ Nuevo integrante</button>
        <?php endif; ?>
      </div>

      <div class="categoria-tabs">
        <a href="/panel/directorio?tab=comite" class="categoria-tab <?= !$departamentoFiltro ? 'is-active' : '' ?>">Todos</a>
        <?php foreach (DEPARTAMENTOS_COMITE as $d): ?>
          <a href="/panel/directorio?tab=comite&departamento=<?= urlencode($d) ?>" class="categoria-tab <?= $departamentoFiltro === $d ? 'is-active' : '' ?>"><?= htmlspecialchars(etiquetaDepartamentoComite($d)) ?></a>
        <?php endforeach; ?>
      </div>

      <p class="candado-aviso candado-aviso--publico">Departamento, cargo, villa y nombre son visibles para todos los propietarios. Correo y teléfono son solo para Comité y Administración.</p>

      <?php if (empty($miembros)): ?>
        <p class="placeholder-note">No hay integrantes que coincidan.</p>
      <?php else: ?>
        <div class="tabla-wrap">
          <table class="tabla-pagos">
            <thead>
              <tr>
                <th>Departamento</th>
                <th>Titular / Suplente</th>
                <th>Nombre</th>
                <th>Villa</th>
                <th>Cargo</th>
                <?php if ($esMesa): ?><th>Correo electrónico 🔒</th><th>Teléfono 🔒</th><?php endif; ?>
                <?php if ($esMesa): ?><th></th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($miembros as $m): ?>
                <tr>
                  <td><span class="badge badge--doc-reglamento"><?= htmlspecialchars(etiquetaDepartamentoComite($m['departamento'])) ?></span></td>
                  <td><?= $m['titular_suplente'] === 'titular' ? 'Titular' : 'Suplente' ?></td>
                  <td>
                    <span class="directorio-persona">
                      <span class="directorio-avatar"><?= htmlspecialchars(iniciales($m['nombre'])) ?></span>
                      <span class="tabla-aviso-titulo"><?= htmlspecialchars($m['nombre']) ?></span>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($m['villa'] ?: '—') ?></td>
                  <td><?= htmlspecialchars($m['cargo']) ?></td>
                  <?php if ($esMesa): ?>
                    <td><?php if (!empty($m['correo'])): ?><a href="mailto:<?= htmlspecialchars($m['correo']) ?>"><?= htmlspecialchars($m['correo']) ?></a><?php else: ?>—<?php endif; ?></td>
                    <td><?= htmlspecialchars($m['telefono'] ?: '—') ?></td>
                  <?php endif; ?>
                  <?php if ($esMesa): ?>
                    <td class="tabla-acciones">
                      <button type="button" class="btn-editar" data-modal-open="modal-editar-comite-<?= (int) $m['id'] ?>">Editar</button>
                      <form action="/panel/directorio/comite-eliminar?id=<?= (int) $m['id'] ?>" method="POST"
                            onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars(str_replace("'", '', $m['nombre'])) ?> del directorio del Comité?');">
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

    <?php endif; ?>
  </section>

  <?php if ($esMesa): ?>
    <!-- ===== Modal: Nuevo colaborador ===== -->
    <div class="aviso-modal-overlay" id="modal-nuevo-colaborador" hidden>
      <div class="aviso-modal">
        <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
        <div class="form-card">
          <span class="eyebrow">Directorio · Personal del condominio</span>
          <h2>Nuevo colaborador</h2>
          <form action="/panel/directorio/colaborador-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <?php $col = []; include __DIR__ . '/../../partials/colaborador-campos.php'; ?>
            <div class="modal-botones">
              <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar colaborador</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ===== Modal: Nuevo integrante del Comité ===== -->
    <div class="aviso-modal-overlay" id="modal-nuevo-comite" hidden>
      <div class="aviso-modal">
        <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
        <div class="form-card">
          <span class="eyebrow">Directorio · Integrantes del Comité</span>
          <h2>Nuevo integrante</h2>
          <form action="/panel/directorio/comite-guardar" method="POST" class="contact-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
            <?php $miembro = []; include __DIR__ . '/../../partials/comite-campos.php'; ?>
            <div class="modal-botones">
              <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar integrante</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ===== Modales: Editar integrante del Comité (uno por integrante) ===== -->
    <?php foreach ($miembros as $m): ?>
      <div class="aviso-modal-overlay" id="modal-editar-comite-<?= (int) $m['id'] ?>" hidden>
        <div class="aviso-modal">
          <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
          <div class="form-card">
            <span class="eyebrow">Directorio · Integrantes del Comité</span>
            <h2>Editar integrante</h2>
            <form action="/panel/directorio/comite-guardar" method="POST" class="contact-form">
              <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>" />
              <?php $miembro = $m; include __DIR__ . '/../../partials/comite-campos.php'; ?>
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

  <datalist id="cargos-comite-sugeridos">
    <?php foreach ($cargosSugeridos as $c): ?>
      <option value="<?= htmlspecialchars($c) ?>"></option>
    <?php endforeach; ?>
  </datalist>
  <datalist id="parentescos-sugeridos">
    <?php foreach (['Esposa', 'Esposo', 'Madre', 'Padre', 'Hijo/a', 'Hermano/a'] as $p): ?>
      <option value="<?= htmlspecialchars($p) ?>"></option>
    <?php endforeach; ?>
  </datalist>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
