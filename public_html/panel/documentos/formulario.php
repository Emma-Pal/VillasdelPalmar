<?php
// Compartido por nuevo.php y editar.php (mismo patrón que avisos/usuarios).

$esEdicion = isset($_GET['id']);
$documentoEditado = null;
$error = null;

if ($esEdicion) {
    $documentoEditado = getDocumentoPorId((int) $_GET['id']);
    if (!$documentoEditado) {
        header('Location: /panel/documentos');
        exit;
    }
}

$title = ($esEdicion ? 'Editar documento' : 'Nuevo documento') . ' — Villas del Palmar';
$description = 'Administración de documentos de Villas del Palmar.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $categoria = in_array($_POST['categoria'] ?? '', CATEGORIAS_DOCUMENTO, true) ? $_POST['categoria'] : 'reglamento';
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    try {
        $archivosSubidos = procesarArchivosSubidos();
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }

    if (!$esEdicion && empty($archivosSubidos)) {
        $error = 'Selecciona un archivo para el documento.';
    }

    if ($error === null) {
        if ($esEdicion) {
            $archivoViejo = $documentoEditado['archivo'];
            if (!empty($archivosSubidos)) {
                actualizarDocumento(
                    $documentoEditado['id'],
                    $categoria,
                    $titulo,
                    $descripcion,
                    $archivosSubidos[0]['archivo'],
                    $archivosSubidos[0]['archivo_nombre_original']
                );
                @unlink(rutaArchivoFisico($archivoViejo));
            } else {
                actualizarDocumento($documentoEditado['id'], $categoria, $titulo, $descripcion);
            }
        } else {
            crearDocumento($usuario['id'], $categoria, $titulo, $descripcion, $archivosSubidos[0]['archivo'], $archivosSubidos[0]['archivo_nombre_original']);
        }
        header('Location: /panel/documentos#categoria-' . $categoria);
        exit;
    }
}

$accionFormulario = $esEdicion ? '/panel/documentos/editar?id=' . (int) $documentoEditado['id'] : '/panel/documentos/nuevo';
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
      <a href="/panel/documentos" class="back-link">← Volver a documentos</a>
      <span class="eyebrow">Administración</span>
      <h1><?= $esEdicion ? 'Editar documento' : 'Nuevo documento' ?></h1>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 560px; margin: 0 auto;">
      <?php if ($error): ?>
        <p class="login-error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <?php if ($esEdicion): ?>
        <p class="form-nota">Archivo actual: <strong><?= htmlspecialchars($documentoEditado['archivo_nombre_original']) ?></strong>. Solo sube uno nuevo si quieres reemplazarlo.</p>
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
            <?php foreach (CATEGORIAS_DOCUMENTO as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $esEdicion && $documentoEditado['categoria'] === $cat ? 'selected' : '' ?>>
                <?= htmlspecialchars(etiquetaCategoriaDocumento($cat)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>

        <label>
          Título
          <input type="text" name="titulo" value="<?= $esEdicion ? htmlspecialchars($documentoEditado['titulo']) : '' ?>" placeholder="ej. Reglamento interno 2026" required />
        </label>

        <label>
          Descripción (opcional)
          <textarea name="descripcion" rows="3"><?= $esEdicion ? htmlspecialchars($documentoEditado['descripcion'] ?? '') : '' ?></textarea>
        </label>

        <label>
          <?= $esEdicion ? 'Reemplazar archivo (opcional — PDF o imagen)' : 'Archivo (PDF o imagen)' ?>
          <input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png" <?= $esEdicion ? '' : 'required' ?> />
        </label>

        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Subir documento' ?></button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
