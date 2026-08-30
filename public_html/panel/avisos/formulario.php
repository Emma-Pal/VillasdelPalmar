<?php
// Compartido por nueva.php y editar.php (equivalente a aviso-form.ejs, que
// en la versión Node también era una sola plantilla para ambos casos).
// Requiere que quien lo incluya ya haya hecho requireMesa().

$esEdicion = isset($_GET['id']);
$publicacionEditada = null;

if ($esEdicion) {
    $publicacionEditada = getPublicacionPorId((int) $_GET['id']);
    if (!$publicacionEditada) {
        header('Location: /panel/avisos');
        exit;
    }
}

$title = ($esEdicion ? 'Editar publicación' : 'Nueva publicación') . ' — Villas del Palmar';
$description = $esEdicion
    ? 'Editar una publicación existente.'
    : 'Publicar un nuevo aviso, mejora o estado financiero.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $categoria = in_array($_POST['categoria'] ?? '', CATEGORIAS_VALIDAS, true) ? $_POST['categoria'] : 'aviso';
    $titulo = trim($_POST['titulo'] ?? '');
    $cuerpo = trim($_POST['cuerpo'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    try {
        $archivosSubidos = procesarArchivosSubidos();
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }

    if ($esEdicion) {
        actualizarPublicacion($publicacionEditada['id'], $categoria, $titulo, $cuerpo, $fecha);
        $idDestino = $publicacionEditada['id'];
    } else {
        $idDestino = crearPublicacion($usuario['id'], $categoria, $titulo, $cuerpo, $fecha);
    }

    // Los archivos nuevos se agregan a los que ya tenía (no los reemplazan);
    // para quitar uno existente hay un botón "Quitar" por archivo, aparte.
    foreach ($archivosSubidos as $archivo) {
        agregarArchivo($idDestino, $archivo['archivo'], $archivo['archivo_nombre_original']);
    }

    header('Location: /panel/avisos');
    exit;
}

$accionFormulario = $esEdicion ? '/panel/avisos/editar?id=' . (int) $publicacionEditada['id'] : '/panel/avisos/nueva';
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
      <a href="/panel/avisos" class="back-link">← Volver a avisos</a>
      <span class="eyebrow">Mesa directiva</span>
      <h1><?= $esEdicion ? 'Editar publicación' : 'Nueva publicación' ?></h1>
      <?php if (!$esEdicion): ?>
        <p class="page-banner-lead">Se firmará como <?= htmlspecialchars($usuario['nombre']) ?> — <?= htmlspecialchars($usuario['cargo']) ?>.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 640px; margin: 0 auto;">

      <!-- OJO: esto va FUERA del <form> de abajo a propósito. Un <form>
           dentro de otro <form> es HTML inválido — el navegador los
           reorganiza de forma impredecible (duplica el campo _csrf, o corta
           el formulario principal antes de tiempo). -->
      <?php if ($esEdicion && !empty($publicacionEditada['archivos'])): ?>
        <div class="archivos-existentes">
          <span class="archivos-existentes-titulo">Archivos ya adjuntos</span>
          <?php foreach ($publicacionEditada['archivos'] as $archivo): ?>
            <div class="archivo-existente">
              <a href="/panel/archivo?id=<?= (int) $archivo['id'] ?>">📎 <?= htmlspecialchars($archivo['archivo_nombre_original']) ?></a>
              <form action="/panel/avisos/archivo-eliminar?pub=<?= (int) $publicacionEditada['id'] ?>&archivo=<?= (int) $archivo['id'] ?>" method="POST"
                    onsubmit="return confirm('¿Quitar este archivo de la publicación?');">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
                <button type="submit" class="btn-eliminar">Quitar</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
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
            <option value="financiero" <?= $esEdicion && $publicacionEditada['categoria'] === 'financiero' ? 'selected' : '' ?>>Estado financiero</option>
            <option value="mejora" <?= $esEdicion && $publicacionEditada['categoria'] === 'mejora' ? 'selected' : '' ?>>Mejora</option>
            <option value="aviso" <?= $esEdicion && $publicacionEditada['categoria'] === 'aviso' ? 'selected' : '' ?>>Aviso general</option>
          </select>
        </label>

        <label>
          Título
          <input type="text" name="titulo" value="<?= $esEdicion ? htmlspecialchars($publicacionEditada['titulo']) : '' ?>" placeholder="ej. Estado financiero — agosto 2026" required />
        </label>

        <label>
          Fecha
          <input type="date" name="fecha" value="<?= $esEdicion ? htmlspecialchars($publicacionEditada['fecha']) : date('Y-m-d') ?>" required />
        </label>

        <label>
          Contenido
          <textarea name="cuerpo" rows="6" placeholder="Escribe el contenido de la publicación..." required><?= $esEdicion ? htmlspecialchars($publicacionEditada['cuerpo']) : '' ?></textarea>
        </label>

        <label>
          <?= $esEdicion ? 'Agregar más archivos (opcional — PDF o imagen)' : 'Archivos adjuntos (opcional — PDF o imagen, hasta 5)' ?>
          <input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png" multiple />
        </label>

        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Publicar' ?></button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
