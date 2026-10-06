<?php
// OJO: esta página muestra a los integrantes del Comité (comite_miembros —
// un cargo dentro del condominio, ej. Presidente, Tesorero), que NO es lo
// mismo que una cuenta de Administración (usuarios.tipo='mesa', el acceso
// al panel de gestión). Antes esta página sí mostraba las cuentas de
// Administración con getMesa() — se corrigió porque Emmanuel aclaró que son
// dos cosas separadas: alguien puede ser del Comité sin tener cuenta de
// Administración, y viceversa. La fuente de verdad real de "quién está en
// el Comité" es /panel/directorio (pestaña "Integrantes del Comité"); esta
// página es solo un acceso directo de solo lectura a esos mismos datos.
require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth();

$title = 'Comité — Villas del Palmar';
$description = 'Integrantes del comité de Villas del Palmar.';
$miembros = getComiteMiembros();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <span class="eyebrow">Quién es quién</span>
      <h1>Comité</h1>
      <p class="page-banner-lead">Integrantes actuales y su cargo. Ver todo el detalle (periodo, titular/suplente) en <a href="/panel/directorio?tab=comite" style="color: inherit; text-decoration: underline;">Directorio</a>.</p>
    </div>
  </section>

  <section class="detail-sections">
    <?php if (empty($miembros)): ?>
      <p class="placeholder-note">Todavía no hay integrantes del Comité capturados.</p>
    <?php else: ?>
      <div class="amenities-grid" data-reveal>
        <?php foreach ($miembros as $m): ?>
          <div class="amenity-card">
            <div class="amenity-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <h3><?= htmlspecialchars($m['nombre']) ?></h3>
            <p><?= htmlspecialchars($m['cargo']) ?> · <?= htmlspecialchars(etiquetaDepartamentoComite($m['departamento'])) ?></p>
            <p class="form-nota" style="margin-top: 4px;"><?= $m['titular_suplente'] === 'suplente' ? 'Suplente' : 'Titular' ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
