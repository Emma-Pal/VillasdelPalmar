<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa(); // el expediente completo nunca lo ve un propietario, ni con la URL directa

$col = getColaboradorPorId((int) ($_GET['id'] ?? 0));
if (!$col) {
    header('Location: /panel/directorio');
    exit;
}

$title = $col['nombre'] . ' — Expediente — Villas del Palmar';
$description = 'Expediente de personal de Villas del Palmar.';

// Para el contador "N/N documentos cargados" del encabezado.
$camposDocumento = ['ine_archivo', 'curp_archivo', 'rfc_archivo', 'nss_archivo', 'domicilio_archivo', 'contrato_archivo'];
$documentosCargados = count(array_filter($camposDocumento, fn($c) => !empty($col[$c])));

// Pinta un botón/link "Ver" (sin mostrar el nombre del archivo guardado),
// o "—" si no se subió nada. Una imagen (jpg/png) abre en el visor flotante
// ya usado en Avisos (data-lightbox-src — ver main.js); un PDF abre en una
// pestaña nueva, que es como ya se ve en el resto del sitio.
function filaDocumento(array $col, string $campo, string $etiquetaCorta): void
{
    $nombre = $col[$campo . '_nombre_original'] ?? null;
    if (!$nombre) {
        echo '<span class="form-nota">—</span>';
        return;
    }
    $href = '/panel/colaborador-archivo?id=' . (int) $col['id'] . '&campo=' . explode('_', $campo)[0];
    if (esImagen($nombre)) {
        echo '<button type="button" class="btn-editar" data-lightbox-src="' . htmlspecialchars($href) . '" data-lightbox-nombre="' . htmlspecialchars($etiquetaCorta) . '">Ver</button>';
    } else {
        echo '<a href="' . htmlspecialchars($href) . '" target="_blank" rel="noopener" class="btn-editar">Ver</a>';
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
      <span class="eyebrow">Administración</span>
      <h1>Directorio</h1>
      <p class="page-banner-lead">Personal del condominio e integrantes del Comité.</p>
    </div>
  </section>

  <section class="detail-sections">
    <div class="section-heading-fila">
      <a href="/panel/directorio" class="btn-editar">← Volver al directorio</a>
      <span class="publicacion-acciones">
        <button type="button" class="btn btn-ghost-light" data-modal-open="modal-editar-colaborador-<?= (int) $col['id'] ?>">Editar ficha</button>
        <a href="/panel/directorio/colaborador-expediente?id=<?= (int) $col['id'] ?>" class="btn btn-primary">Descargar expediente</a>
      </span>
    </div>

    <div class="ficha-encabezado">
      <div class="ficha-avatar">
        <?php if (!empty($col['foto'])): ?>
          <img src="/panel/colaborador-archivo?id=<?= (int) $col['id'] ?>&campo=foto" alt="" />
        <?php else: ?>
          <?= htmlspecialchars(iniciales($col['nombre'])) ?>
        <?php endif; ?>
      </div>
      <div class="ficha-encabezado-info">
        <h2><?= htmlspecialchars($col['nombre']) ?></h2>
        <p class="convocatoria-meta">
          <?= htmlspecialchars($col['puesto']) ?> · <?= htmlspecialchars(etiquetaAreaColaborador($col['area'])) ?>
          <?= !empty($col['turno_horario']) ? ' · ' . htmlspecialchars($col['turno_horario']) : '' ?>
        </p>
        <div class="convocatoria-tags" style="margin-top: 8px;">
          <span class="badge <?= $col['estatus'] === 'activo' ? 'badge--ok' : 'badge--alerta' ?>"><?= $col['estatus'] === 'activo' ? 'Activo' : 'Baja' ?></span>
          <span class="badge badge--doc-reglamento">Ingreso <?= htmlspecialchars(date('d/m/Y', strtotime($col['fecha_ingreso']))) ?></span>
          <?php if (!empty($col['tipo_contrato'])): ?><span class="badge badge--doc-politica">Contrato <?= htmlspecialchars($col['tipo_contrato']) ?></span><?php endif; ?>
        </div>
      </div>
      <div class="ficha-expediente-contador">
        <span class="fecha-bloque-dia"><?= $documentosCargados ?>/<?= count($camposDocumento) ?></span>
        <span class="fecha-bloque-anio">documentos cargados</span>
      </div>
    </div>

    <p class="candado-aviso">🔒 Información confidencial. Los datos de esta página solo los consulta la Administración.</p>

    <div class="form-card" data-reveal>
      <span class="form-card-numero">Identificación oficial</span>
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 12px;">
        <div><span class="campo-grupo-label">CURP</span><p><?= htmlspecialchars($col['curp_numero'] ?: '—') ?></p><?php filaDocumento($col, 'curp_archivo', 'Documento CURP'); ?></div>
        <div><span class="campo-grupo-label">RFC</span><p><?= htmlspecialchars($col['rfc_numero'] ?: '—') ?></p><?php filaDocumento($col, 'rfc_archivo', 'Constancia RFC'); ?></div>
        <div><span class="campo-grupo-label">NSS (IMSS)</span><p><?= htmlspecialchars($col['nss_numero'] ?: '—') ?></p><?php filaDocumento($col, 'nss_archivo', 'Comprobante NSS'); ?></div>
        <div><span class="campo-grupo-label">INE</span><?php filaDocumento($col, 'ine_archivo', 'INE'); ?></div>
      </div>
    </div>

    <div class="form-card" data-reveal>
      <span class="form-card-numero">Datos personales</span>
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 12px;">
        <div><span class="campo-grupo-label">Fecha de nacimiento</span><p><?= !empty($col['fecha_nacimiento']) ? htmlspecialchars(date('d/m/Y', strtotime($col['fecha_nacimiento']))) : '—' ?></p></div>
        <div><span class="campo-grupo-label">Lugar de nacimiento</span><p><?= htmlspecialchars($col['lugar_nacimiento'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Nacionalidad</span><p><?= htmlspecialchars($col['nacionalidad'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Estado civil</span><p><?= htmlspecialchars($col['estado_civil'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Teléfono</span><p><?= htmlspecialchars($col['telefono'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Correo electrónico</span><p><?= htmlspecialchars($col['correo'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Domicilio</span><p><?= htmlspecialchars($col['domicilio'] ?: '—') ?></p><?php filaDocumento($col, 'domicilio_archivo', 'Comprobante'); ?></div>
      </div>
    </div>

    <div class="form-card" data-reveal>
      <span class="form-card-numero">Datos laborales</span>
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 12px;">
        <div><span class="campo-grupo-label">Puesto</span><p><?= htmlspecialchars($col['puesto']) ?></p></div>
        <div><span class="campo-grupo-label">Área / Departamento</span><p><?= htmlspecialchars(etiquetaAreaColaborador($col['area'])) ?></p></div>
        <div><span class="campo-grupo-label">Fecha de ingreso</span><p><?= htmlspecialchars(date('d/m/Y', strtotime($col['fecha_ingreso']))) ?></p></div>
        <div><span class="campo-grupo-label">Turno / horario</span><p><?= htmlspecialchars($col['turno_horario'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Tipo de contrato</span><p><?= htmlspecialchars($col['tipo_contrato'] ?: '—') ?></p><?php filaDocumento($col, 'contrato_archivo', 'Contrato firmado'); ?></div>
      </div>
    </div>

    <div class="form-card" data-reveal>
      <span class="form-card-numero">Contacto de emergencia</span>
      <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-top: 12px;">
        <div><span class="campo-grupo-label">Nombre</span><p><?= htmlspecialchars($col['emergencia_nombre'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Parentesco</span><p><?= htmlspecialchars($col['emergencia_parentesco'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Teléfono 1</span><p><?= htmlspecialchars($col['emergencia_telefono1'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Teléfono 2</span><p><?= htmlspecialchars($col['emergencia_telefono2'] ?: '—') ?></p></div>
        <div><span class="campo-grupo-label">Domicilio</span><p><?= htmlspecialchars($col['emergencia_domicilio'] ?: '—') ?></p></div>
      </div>
    </div>
  </section>

  <!-- ===== Modal: Editar colaborador ===== -->
  <div class="aviso-modal-overlay" id="modal-editar-colaborador-<?= (int) $col['id'] ?>" hidden>
    <div class="aviso-modal">
      <button type="button" class="aviso-modal-close" data-modal-close aria-label="Cerrar">&times;</button>
      <div class="form-card">
        <span class="eyebrow">Directorio · Personal del condominio</span>
        <h2>Editar colaborador</h2>
        <form action="/panel/directorio/colaborador-guardar" method="POST" enctype="multipart/form-data" class="contact-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <input type="hidden" name="id" value="<?= (int) $col['id'] ?>" />
          <?php include __DIR__ . '/../../partials/colaborador-campos.php'; ?>
          <div class="modal-botones">
            <button type="button" class="btn btn-ghost-light" data-modal-close>Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ===== Lightbox: ver una imagen del expediente sin salir de la página ===== -->
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
