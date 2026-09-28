<?php
// Compartido por nuevo.php y editar.php (mismo patrón que documentos).

$esEdicion = isset($_GET['id']);
$itemEditado = null;
$error = null;

if ($esEdicion) {
    $itemEditado = getGaleriaItemPorId((int) $_GET['id']);
    if (!$itemEditado) {
        header('Location: /panel/galeria/admin');
        exit;
    }
}

$title = ($esEdicion ? 'Editar imagen' : 'Agregar imagen') . ' — Villas del Palmar';
$description = 'Administración de la galería de Villas del Palmar.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $categoria = in_array($_POST['categoria'] ?? '', CATEGORIAS_GALERIA, true) ? $_POST['categoria'] : 'alberca';
    $eyebrow = trim($_POST['eyebrow'] ?? '');
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $caracteristicas = trim($_POST['caracteristicas'] ?? '') ?: null;

    try {
        $archivosSubidos = procesarArchivosSubidos();
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir la imagen.', $e->getMessage());
        exit;
    }

    if (!$esEdicion && empty($archivosSubidos)) {
        $error = 'Selecciona una imagen.';
    }

    if ($error === null) {
        if ($esEdicion) {
            $idDestino = $itemEditado['id'];
            $archivoViejo = $itemEditado['archivo'];
            if (!empty($archivosSubidos)) {
                actualizarGaleriaItem(
                    $idDestino,
                    $categoria,
                    $eyebrow,
                    $titulo,
                    $descripcion,
                    $caracteristicas,
                    $archivosSubidos[0]['archivo'],
                    $archivosSubidos[0]['archivo_nombre_original']
                );
                @unlink(rutaArchivoFisico($archivoViejo));
            } else {
                actualizarGaleriaItem($idDestino, $categoria, $eyebrow, $titulo, $descripcion, $caracteristicas);
            }
        } else {
            $idDestino = crearGaleriaItem(
                $usuario['id'],
                $categoria,
                $eyebrow,
                $titulo,
                $descripcion,
                $caracteristicas,
                $archivosSubidos[0]['archivo'],
                $archivosSubidos[0]['archivo_nombre_original']
            );
        }
        header('Location: /panel/galeria/' . $categoria . '#item-' . $idDestino);
        exit;
    }
}

$categoriaPreseleccionada = !$esEdicion ? ($_GET['categoria'] ?? 'alberca') : null;
$categoriaActual = $esEdicion ? $itemEditado['categoria'] : $categoriaPreseleccionada;
$accionFormulario = $esEdicion ? '/panel/galeria/admin/editar?id=' . (int) $itemEditado['id'] : '/panel/galeria/admin/nuevo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <a href="/panel/galeria/admin" class="back-link">← Volver a administrar galería</a>
      <span class="eyebrow">Administración</span>
      <h1><?= $esEdicion ? 'Editar imagen' : 'Agregar imagen' ?></h1>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 560px; margin: 0 auto;">
      <?php if ($error): ?>
        <p class="login-error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <?php if ($esEdicion): ?>
        <p class="form-nota">Imagen actual: <strong><?= htmlspecialchars($itemEditado['archivo_nombre_original']) ?></strong>. Solo sube una nueva si quieres reemplazarla.</p>
      <?php endif; ?>

      <form
        action="<?= htmlspecialchars($accionFormulario) ?>"
        method="POST"
        enctype="multipart/form-data"
        class="contact-form"
      >
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

        <label>
          Categoría
          <select name="categoria" required>
            <?php foreach (CATEGORIAS_GALERIA as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $categoriaActual === $cat ? 'selected' : '' ?>>
                <?= htmlspecialchars(etiquetaCategoriaGaleria($cat)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Texto pequeño de arriba (ej. "Con vista")
          <input type="text" name="eyebrow" value="<?= $esEdicion ? htmlspecialchars($itemEditado['eyebrow']) : '' ?>" placeholder="ej. Con vista" required />
        </label>

        <label>
          Título
          <input type="text" name="titulo" value="<?= $esEdicion ? htmlspecialchars($itemEditado['titulo']) : '' ?>" placeholder="ej. Terraza panorámica" required />
        </label>

        <label>
          Descripción
          <textarea name="descripcion" rows="4" required><?= $esEdicion ? htmlspecialchars($itemEditado['descripcion']) : '' ?></textarea>
        </label>

        <label>
          Características (opcional — una por línea, aparecen como lista)
          <textarea name="caracteristicas" rows="3" placeholder="ej.&#10;Vista panorámica&#10;Ideal para el atardecer"><?= $esEdicion ? htmlspecialchars($itemEditado['caracteristicas'] ?? '') : '' ?></textarea>
        </label>

        <label>
          <?= $esEdicion ? 'Reemplazar imagen (opcional)' : 'Imagen (JPG o PNG)' ?>
          <input type="file" name="archivos[]" accept=".jpg,.jpeg,.png" <?= $esEdicion ? '' : 'required' ?> />
        </label>

        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Agregar' ?></button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../../partials/footer.php'; ?>

</body>
</html>
