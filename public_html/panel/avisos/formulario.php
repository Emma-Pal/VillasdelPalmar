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

    $categoriaPost = trim($_POST['categoria'] ?? '');
    $categoriasFijas = array_merge(CATEGORIAS_BASE, CATEGORIAS_ASAMBLEA);
    if ($categoriaPost === '__otra__') {
        $categoria = trim($_POST['categoria_otra'] ?? '');
        if ($categoria === '') {
            $categoria = 'aviso'; // no escribió nada en "Otra" — cae a un valor seguro
        }
        $categoria = mb_substr($categoria, 0, 50); // límite de la columna en la base de datos
    } elseif (in_array($categoriaPost, $categoriasFijas, true)) {
        $categoria = $categoriaPost;
    } else {
        $categoria = 'aviso';
    }

    $titulo = trim($_POST['titulo'] ?? '');
    $cuerpo = trim($_POST['cuerpo'] ?? '');
    $destacado = isset($_POST['destacado']);
    $publicado = isset($_POST['publicado']);
    $prioridad = in_array($_POST['prioridad'] ?? '', ['urgente', 'importante', 'informativo'], true)
        ? $_POST['prioridad']
        : 'informativo';
    $fechaEvento = ($categoria === 'convocatoria' && !empty($_POST['fecha_evento'])) ? $_POST['fecha_evento'] : null;

    try {
        $archivosSubidos = procesarArchivosSubidos();
    } catch (RuntimeException $e) {
        http_response_code(400);
        renderError('No se pudo completar — Villas del Palmar', 'Error al subir el archivo.', $e->getMessage());
        exit;
    }

    if ($esEdicion) {
        // Sin fecha: la fecha editorial no se puede tocar al editar, solo se
        // registra que hubo una edición (actualizarPublicacion pone editado_en).
        actualizarPublicacion($publicacionEditada['id'], $categoria, $titulo, $cuerpo, $destacado, $fechaEvento, $prioridad, $publicado);
        $idDestino = $publicacionEditada['id'];
    } else {
        // La fecha SIEMPRE es la de hoy, fijada aquí en el servidor — nunca
        // se confía en un valor que pudiera venir del formulario.
        $idDestino = crearPublicacion($usuario['id'], $categoria, $titulo, $cuerpo, date('Y-m-d'), $destacado, $fechaEvento, $prioridad, $publicado);
    }

    // Los archivos nuevos se agregan a los que ya tenía (no los reemplazan);
    // para quitar uno existente hay un botón "Quitar" por archivo, aparte.
    foreach ($archivosSubidos as $archivo) {
        agregarArchivo($idDestino, $archivo['archivo'], $archivo['archivo_nombre_original']);
    }

    // Una convocatoria/acta se crea desde /panel/asambleas — al guardar hay
    // que regresar ahí (con ancla a su sección), no al feed general de
    // avisos, donde de todos modos no se mostraría (ver CATEGORIAS_ASAMBLEA).
    if (in_array($categoria, CATEGORIAS_ASAMBLEA, true)) {
        header('Location: /panel/asambleas#' . ($categoria === 'convocatoria' ? 'convocatorias' : 'actas'));
    } else {
        header('Location: /panel/avisos');
    }
    exit;
}

$accionFormulario = $esEdicion ? '/panel/avisos/editar?id=' . (int) $publicacionEditada['id'] : '/panel/avisos/nueva';

// ¿La publicación (al editar) tiene una categoría que no es ninguna de las
// de fábrica (ni las 3 de avisos, ni convocatoria/acta)? Entonces el select
// debe abrir directo en "Otra", con el campo de texto ya visible.
$categoriasFijas = array_merge(CATEGORIAS_BASE, CATEGORIAS_ASAMBLEA);
$categoriaEsLibre = $esEdicion && !in_array($publicacionEditada['categoria'], $categoriasFijas, true);

// Categorías libres ya usadas antes (para sugerirlas con autocompletar y
// evitar que se creen variantes del mismo nombre por error de dedo).
$categoriasLibresExistentes = array_diff(getCategoriasUsadas(), $categoriasFijas);

// Solo aplica al crear (no al editar): permite llegar con la categoría ya
// preseleccionada, ej. desde el botón "+ Nueva convocatoria" en /panel/asambleas.
$categoriaPreseleccionada = !$esEdicion ? ($_GET['categoria'] ?? null) : null;

// Marca "selected" en modo edición (categoría actual) o en modo creación
// cuando se llegó con ?categoria= en la URL. Se calcula aquí (no solo más
// abajo) porque también decide a dónde apunta el link "Volver a...".
$categoriaSeleccionada = $esEdicion ? $publicacionEditada['categoria'] : $categoriaPreseleccionada;
$esAsamblea = in_array($categoriaSeleccionada, CATEGORIAS_ASAMBLEA, true);
$volverHref = $esAsamblea ? '/panel/asambleas' : '/panel/avisos';
$volverTexto = $esAsamblea ? '← Volver a asambleas' : '← Volver a avisos';
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
      <a href="<?= htmlspecialchars($volverHref) ?>" class="back-link"><?= htmlspecialchars($volverTexto) ?></a>
      <span class="eyebrow">Comité</span>
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
          <select name="categoria" id="categoria-select" required>
            <option value="financiero" <?= $categoriaSeleccionada === 'financiero' ? 'selected' : '' ?>>Estado financiero</option>
            <option value="mejora" <?= $categoriaSeleccionada === 'mejora' ? 'selected' : '' ?>>Mejora</option>
            <option value="aviso" <?= $categoriaSeleccionada === 'aviso' ? 'selected' : '' ?>>Aviso general</option>
            <option value="convocatoria" <?= $categoriaSeleccionada === 'convocatoria' ? 'selected' : '' ?>>Convocatoria (asamblea)</option>
            <option value="acta" <?= $categoriaSeleccionada === 'acta' ? 'selected' : '' ?>>Acta (asamblea)</option>
            <option value="__otra__" <?= $categoriaEsLibre ? 'selected' : '' ?>>Otra (especificar)</option>
          </select>
        </label>

        <label id="campo-fecha-evento">
          Fecha de la asamblea
          <span class="fecha-wrap">
            <input
              type="date"
              name="fecha_evento"
              class="fecha-real"
              value="<?= $esEdicion && !empty($publicacionEditada['fecha_evento']) ? htmlspecialchars($publicacionEditada['fecha_evento']) : '' ?>"
            />
            <span class="fecha-texto" data-placeholder="Selecciona la fecha de la asamblea"></span>
            <span class="fecha-wrap-icono" aria-hidden="true">📅</span>
          </span>
        </label>

        <label id="campo-categoria-otra">
          Especifica la categoría
          <input
            type="text"
            name="categoria_otra"
            list="categorias-libres-existentes"
            maxlength="50"
            value="<?= $categoriaEsLibre ? htmlspecialchars($publicacionEditada['categoria']) : '' ?>"
            placeholder="ej. Reglamento interno"
          />
          <datalist id="categorias-libres-existentes">
            <?php foreach ($categoriasLibresExistentes as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>"></option>
            <?php endforeach; ?>
          </datalist>
        </label>

        <label>
          Prioridad
          <select name="prioridad" required>
            <?php $prioridadActual = $esEdicion ? $publicacionEditada['prioridad'] : 'informativo'; ?>
            <option value="urgente" <?= $prioridadActual === 'urgente' ? 'selected' : '' ?>>Urgente</option>
            <option value="importante" <?= $prioridadActual === 'importante' ? 'selected' : '' ?>>Importante</option>
            <option value="informativo" <?= $prioridadActual === 'informativo' ? 'selected' : '' ?>>Informativo</option>
          </select>
        </label>

        <label>
          Título
          <input type="text" name="titulo" value="<?= $esEdicion ? htmlspecialchars($publicacionEditada['titulo']) : '' ?>" placeholder="ej. Estado financiero — agosto 2026" required />
        </label>

        <?php if ($esEdicion): ?>
          <p class="form-nota">
            Fecha de publicación: <strong><?= htmlspecialchars($publicacionEditada['fecha']) ?></strong> (no se puede cambiar).
          </p>
        <?php else: ?>
          <p class="form-nota">
            Se publicará con la fecha de hoy: <strong><?= date('d/m/Y') ?></strong>.
          </p>
        <?php endif; ?>

        <label>
          Contenido
          <textarea name="cuerpo" rows="6" placeholder="Escribe el contenido de la publicación..." required><?= $esEdicion ? htmlspecialchars($publicacionEditada['cuerpo']) : '' ?></textarea>
        </label>

        <label>
          <?= $esEdicion ? 'Agregar más archivos (opcional — PDF o imagen)' : 'Archivos adjuntos (opcional — PDF o imagen, hasta 5)' ?>
          <input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png" multiple />
        </label>

        <label class="campo-checkbox">
          <input type="checkbox" name="destacado" <?= $esEdicion && !empty($publicacionEditada['destacado']) ? 'checked' : '' ?> />
          Marcar como destacado (aparece primero en Avisos y en el Panel)
        </label>

        <label class="campo-checkbox">
          <?php $publicadoActual = $esEdicion ? (bool) $publicacionEditada['publicado'] : true; ?>
          <input type="checkbox" name="publicado" <?= $publicadoActual ? 'checked' : '' ?> />
          Publicar ahora (si lo desmarcas, se guarda como borrador — solo lo ve el comité)
        </label>

        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Publicar' ?></button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
