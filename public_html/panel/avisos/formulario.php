<?php
// Compartido por nueva.php y editar.php. Requiere que quien lo incluya ya
// haya hecho requireMesa().
//
// A partir del rediseño de oct. 2026 esta pantalla ya NO pregunta la
// categoría (financiero/mejora/aviso/"otra"): toda publicación creada aquí
// es categoría 'aviso', fija. La única excepción es cuando se llega desde
// /panel/asambleas (?categoria=convocatoria o =acta) — ahí la categoría
// viaja oculta en el formulario, sin selector visible, para no romper ese
// módulo. Al editar, la categoría existente de la publicación se conserva
// tal cual (tampoco se puede cambiar desde aquí).

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
    : 'Publicar un nuevo aviso para los propietarios o el comité.';

// Categoría oculta: se conserva al editar; al crear, solo se respeta
// convocatoria/acta si se llegó desde /panel/asambleas — cualquier otra
// cosa cae a 'aviso'.
if ($esEdicion) {
    $categoriaOculta = $publicacionEditada['categoria'];
} else {
    $categoriaOculta = in_array($_GET['categoria'] ?? '', CATEGORIAS_ASAMBLEA, true) ? $_GET['categoria'] : 'aviso';
}
$esAsamblea = in_array($categoriaOculta, CATEGORIAS_ASAMBLEA, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $categoria = trim($_POST['categoria'] ?? '') ?: 'aviso';
    $titulo = trim($_POST['titulo'] ?? '');
    $cuerpo = trim($_POST['cuerpo'] ?? '');
    $destacado = isset($_POST['destacado']);
    $publicado = isset($_POST['publicado']);
    $prioridad = in_array($_POST['prioridad'] ?? '', ['urgente', 'importante', 'informativo'], true)
        ? $_POST['prioridad']
        : 'informativo';
    $audiencia = in_array($_POST['audiencia'] ?? '', ['todos', 'comite'], true) ? $_POST['audiencia'] : 'todos';
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
        actualizarPublicacion($publicacionEditada['id'], $categoria, $titulo, $cuerpo, $destacado, $fechaEvento, $prioridad, $publicado, $audiencia);
        $idDestino = $publicacionEditada['id'];
    } else {
        // La fecha SIEMPRE es la de hoy, fijada aquí en el servidor — nunca
        // se confía en un valor que pudiera venir del formulario.
        $idDestino = crearPublicacion($usuario['id'], $categoria, $titulo, $cuerpo, date('Y-m-d'), $destacado, $fechaEvento, $prioridad, $publicado, $audiencia);
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
$prioridadActual = $esEdicion ? $publicacionEditada['prioridad'] : 'informativo';
$audienciaActual = $esEdicion ? $publicacionEditada['audiencia'] : 'todos';
$publicadoActual = $esEdicion ? (bool) $publicacionEditada['publicado'] : true;

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
      <h1><?= $esEdicion ? 'Editar publicación' : 'Capturar aviso' ?></h1>
      <?php if (!$esEdicion): ?>
        <p class="page-banner-lead">Se firmará como <?= htmlspecialchars($usuario['nombre']) ?> — <?= htmlspecialchars($usuario['cargo']) ?>.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card-grupo" style="max-width: 680px; margin: 0 auto;">

      <!-- OJO: esto va FUERA del <form> de abajo a propósito. Un <form>
           dentro de otro <form> es HTML inválido — el navegador los
           reorganiza de forma impredecible (duplica el campo _csrf, o corta
           el formulario principal antes de tiempo). -->
      <?php if ($esEdicion && !empty($publicacionEditada['archivos'])): ?>
        <div class="archivos-existentes" style="margin-bottom: 24px;">
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
        <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoriaOculta) ?>" />

        <div class="form-card" data-reveal>
          <span class="form-card-numero">1 · Contenido</span>

          <label>
            Título del aviso
            <input type="text" name="titulo" value="<?= $esEdicion ? htmlspecialchars($publicacionEditada['titulo']) : '' ?>" placeholder="ej. Suspensión programada de agua — Sección Palmas" required />
          </label>

          <?php if ($esAsamblea): ?>
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
          <?php endif; ?>

          <div class="campo-grupo">
            <span class="campo-grupo-label">Prioridad</span>
            <div class="pill-radio-group" role="radiogroup" aria-label="Prioridad">
              <label class="pill-radio">
                <input type="radio" name="prioridad" value="informativo" <?= $prioridadActual === 'informativo' ? 'checked' : '' ?> />
                <span>Informativo</span>
              </label>
              <label class="pill-radio">
                <input type="radio" name="prioridad" value="importante" <?= $prioridadActual === 'importante' ? 'checked' : '' ?> />
                <span>Importante</span>
              </label>
              <label class="pill-radio">
                <input type="radio" name="prioridad" value="urgente" <?= $prioridadActual === 'urgente' ? 'checked' : '' ?> />
                <span>Urgente</span>
              </label>
            </div>
          </div>

          <label>
            Texto del aviso
            <textarea name="cuerpo" rows="6" placeholder="Redacte el aviso: qué ocurre, a quién afecta, fecha y horario, qué debe hacer el propietario y a quién dirigirse." required><?= $esEdicion ? htmlspecialchars($publicacionEditada['cuerpo']) : '' ?></textarea>
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

          <div class="campo-grupo">
            <span class="campo-grupo-label">Archivos adjuntos</span>
            <label class="dropzone" id="dropzone">
              <input type="file" name="archivos[]" id="archivos-input" accept=".pdf,.jpg,.jpeg,.png" multiple class="dropzone-input" />
              <span class="dropzone-icon" aria-hidden="true">⬆</span>
              <span class="dropzone-text">
                Arrastra aquí circulares, planos o fotografías
                <small>PDF, JPG o PNG — <?= $esEdicion ? 'se agregan a los ya existentes' : 'hasta 5 archivos' ?></small>
              </span>
              <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Seleccionar</span>
            </label>
            <p class="dropzone-filenames" id="dropzone-filenames"></p>
          </div>
        </div>

        <div class="form-card" data-reveal>
          <span class="form-card-numero">2 · Destinatarios</span>

          <div class="campo-grupo">
            <span class="campo-grupo-label">Dirigido a</span>
            <div class="pill-radio-group" role="radiogroup" aria-label="Dirigido a">
              <label class="pill-radio pill-radio--grande">
                <input type="radio" name="audiencia" value="todos" <?= $audienciaActual === 'todos' ? 'checked' : '' ?> />
                <span>Todos los propietarios</span>
              </label>
              <label class="pill-radio pill-radio--grande">
                <input type="radio" name="audiencia" value="comite" <?= $audienciaActual === 'comite' ? 'checked' : '' ?> />
                <span>Solo Administración</span>
              </label>
            </div>
            <p class="form-nota">Si eliges "Solo Administración", los propietarios no podrán ver esta publicación.</p>
          </div>

          <label class="campo-checkbox">
            <input type="checkbox" name="destacado" <?= $esEdicion && !empty($publicacionEditada['destacado']) ? 'checked' : '' ?> />
            Marcar como destacado (aparece primero en Avisos y en el Panel)
          </label>

          <label class="campo-checkbox">
            <input type="checkbox" name="publicado" <?= $publicadoActual ? 'checked' : '' ?> />
            Publicar ahora (si lo desmarcas, se guarda como borrador — solo lo ve Administración)
          </label>
        </div>

        <button type="submit" class="btn btn-primary"><?= $esEdicion ? 'Guardar cambios' : 'Publicar' ?></button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
