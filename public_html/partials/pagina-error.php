<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/head.php'; ?>
</head>
<body>

  <?php if ($usuario): ?>
    <?php include __DIR__ . '/portal-header.php'; ?>
  <?php endif; ?>

  <section class="detail-sections" style="padding-top: <?= $usuario ? '152px' : '48px' ?>; min-height: <?= $usuario ? '60vh' : '100vh' ?>; align-items: center; justify-content: center;">
    <div class="detail-block" style="justify-content: center; text-align: center;">
      <div class="detail-text" style="flex: none; max-width: 480px;">
        <span class="eyebrow">Aviso</span>
        <h2><?= htmlspecialchars($mensaje) ?></h2>
        <p><a href="<?= $usuario ? '/panel' : '/login' ?>" class="btn btn-primary"><?= $usuario ? 'Volver al panel' : 'Ir a iniciar sesión' ?></a></p>
      </div>
    </div>
  </section>

  <?php if ($usuario): ?>
    <?php include __DIR__ . '/footer.php'; ?>
  <?php endif; ?>

</body>
</html>
