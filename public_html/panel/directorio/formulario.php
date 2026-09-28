<?php
// Compartido por nuevo.php y editar.php.

$esEdicion = isset($_GET['id']);
$contactoEditado = null;

if ($esEdicion) {
    $contactoEditado = getContactoPorId((int) $_GET['id']);
    if (!$contactoEditado) {
        header('Location: /panel/directorio');
        exit;
    }
}

$title = ($esEdicion ? 'Editar contacto' : 'Nuevo contacto') . ' — Villas del Palmar';
$description = 'Directorio de contactos de Villas del Palmar.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $categoria = in_array($_POST['categoria'] ?? '', CATEGORIAS_CONTACTO, true) ? $_POST['categoria'] : 'administracion';
    $nombre = trim($_POST['nombre'] ?? '');
    $puesto = trim($_POST['puesto'] ?? '') ?: null;
    $telefono = trim($_POST['telefono'] ?? '') ?: null;
    $correo = trim($_POST['correo'] ?? '') ?: null;
    $notas = trim($_POST['notas'] ?? '') ?: null;

    if ($esEdicion) {
        actualizarContacto($contactoEditado['id'], $categoria, $nombre, $puesto, $telefono, $correo, $notas);
    } else {
        crearContacto($categoria, $nombre, $puesto, $telefono, $correo, $notas);
    }
    header('Location: /panel/directorio#categoria-' . $categoria);
    exit;
}

$datos = $esEdicion ? $contactoEditado : ['categoria' => 'administracion', 'nombre' => '', 'puesto' => '', 'telefono' => '', 'correo' => '', 'notas' => ''];
$accionFormulario = $esEdicion ? '/panel/directorio/editar?id=' . (int) $contactoEditado['id'] : '/panel/directorio/nuevo';
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
      <a href="/panel/directorio" class="back-link">← Volver al directorio</a>
      <span class="eyebrow">Administración</span>
      <h1><?= $esEdicion ? 'Editar contacto' : 'Nuevo contacto' ?></h1>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 560px; margin: 0 auto;">
      <form action="<?= htmlspecialchars($accionFormulario) ?>" method="POST" class="contact-form">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

        <label>
          Categoría
          <select name="categoria" required>
            <?php foreach (CATEGORIAS_CONTACTO as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $datos['categoria'] === $cat ? 'selected' : '' ?>>
                <?= htmlspecialchars(etiquetaCategoriaContacto($cat)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Nombre
          <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required />
        </label>

        <label>
          Puesto o especialidad (opcional)
          <input type="text" name="puesto" value="<?= htmlspecialchars($datos['puesto'] ?? '') ?>" placeholder="ej. Plomero, Eléctrico" />
        </label>

        <label>
          Teléfono (opcional)
          <input type="text" name="telefono" value="<?= htmlspecialchars($datos['telefono'] ?? '') ?>" />
        </label>

        <label>
          Correo (opcional)
          <input type="email" name="correo" value="<?= htmlspecialchars($datos['correo'] ?? '') ?>" />
        </label>

        <label>
          Notas (opcional)
          <textarea name="notas" rows="3"><?= htmlspecialchars($datos['notas'] ?? '') ?></textarea>
        </label>

        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Agregar contacto' ?></button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
