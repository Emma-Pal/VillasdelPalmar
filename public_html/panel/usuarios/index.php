<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireMesa();

$title = 'Usuarios — Villas del Palmar';
$description = 'Administración de cuentas de propietarios y mesa directiva.';
$usuarios = getUsuarios();
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
      <span class="eyebrow">Mesa directiva</span>
      <h1>Usuarios</h1>
      <p class="page-banner-lead">Cuentas de propietarios y mesa directiva, y sus privilegios.</p>
    </div>
  </section>

  <section class="detail-sections">
    <p style="text-align: right; margin-bottom: 16px;">
      <a href="/panel/usuarios/nuevo" class="btn btn-primary">+ Nuevo usuario</a>
    </p>

    <div class="tabla-wrap" data-reveal>
      <table class="tabla-pagos">
        <thead>
          <tr>
            <th>Tipo</th>
            <th>Nombre</th>
            <th>Cargo</th>
            <th>Usuario</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td>
                <span class="badge <?= $u['tipo'] === 'mesa' ? 'badge--ok' : '' ?>">
                  <?= $u['tipo'] === 'mesa' ? 'Mesa directiva' : 'Propietario' ?>
                </span>
              </td>
              <td><?= htmlspecialchars($u['nombre']) ?></td>
              <td><?= htmlspecialchars($u['cargo'] ?: '—') ?></td>
              <td><?= htmlspecialchars($u['usuario']) ?></td>
              <td class="tabla-acciones">
                <a href="/panel/usuarios/editar?id=<?= (int) $u['id'] ?>" class="btn-editar">Editar</a>
                <?php if ((int) $u['id'] !== (int) $usuario['id']): ?>
                  <form action="/panel/usuarios/eliminar?id=<?= (int) $u['id'] ?>" method="POST"
                        onsubmit="return confirm('¿Eliminar la cuenta de <?= htmlspecialchars(str_replace("'", '', $u['nombre'])) ?>? Esto no se puede deshacer.');">
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

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
