<?php
// Campos del formulario de acta — compartidos por el modal "Nueva acta" y
// cada modal "Editar acta" de /panel/asambleas. Espera $acta: [] para
// "nueva", o la fila de publicaciones (con 'archivos' ya cargado) para
// editar una existente. $conceptosFijos viene de acta-guardar.php / aquí
// mismo se repite la lista para que index.php no tenga que importarla.
$conceptosFijos = ['Acta de asamblea', 'Lista de asistencia y quórum', 'Orden del día firmado'];
$conceptoActual = $acta['titulo'] ?? '';
$conceptoEsLibre = $conceptoActual !== '' && !in_array($conceptoActual, $conceptosFijos, true);
$tipoActual = $acta['tipo_asamblea'] ?? 'ordinaria';
$anioActual = (int) ($acta['anio_asamblea'] ?? date('Y'));
$documentoActual = $acta['archivos'][0] ?? null;
?>
<label>
  Concepto del documento *
  <select name="concepto" class="campo-concepto-select" required>
    <option value="" <?= $conceptoActual === '' ? 'selected' : '' ?>>Selecciona...</option>
    <?php foreach ($conceptosFijos as $c): ?>
      <option value="<?= htmlspecialchars($c) ?>" <?= $conceptoActual === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
    <?php endforeach; ?>
    <option value="__otro__" <?= $conceptoEsLibre ? 'selected' : '' ?>>Otro (especificar)</option>
  </select>
</label>

<label class="campo-concepto-otro" <?= $conceptoEsLibre ? '' : 'hidden' ?>>
  Especifica el concepto
  <input type="text" name="concepto_otro" value="<?= $conceptoEsLibre ? htmlspecialchars($conceptoActual) : '' ?>" maxlength="255" />
</label>

<label>
  Descripción del concepto
  <input type="text" name="descripcion" value="<?= htmlspecialchars($acta['cuerpo'] ?? '') ?>" placeholder="Ej. Acta de Asamblea General Ordinaria, aprobación de presupuesto" />
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Año al que corresponde *
    <select name="anio_asamblea" required>
      <?php for ($y = (int) date('Y') + 1; $y >= (int) date('Y') - 10; $y--): ?>
        <option value="<?= $y ?>" <?= $anioActual === $y ? 'selected' : '' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
  </label>
  <label style="flex: 1;">
    Fecha de la asamblea
    <input type="date" name="fecha_evento" value="<?= htmlspecialchars($acta['fecha_evento'] ?? '') ?>" />
  </label>
</div>

<div class="campo-grupo">
  <span class="campo-grupo-label">Tipo de asamblea *</span>
  <div class="pill-radio-group" role="radiogroup" aria-label="Tipo de asamblea">
    <label class="pill-radio">
      <input type="radio" name="tipo_asamblea" value="ordinaria" <?= $tipoActual === 'ordinaria' ? 'checked' : '' ?> required />
      <span>Ordinaria</span>
    </label>
    <label class="pill-radio">
      <input type="radio" name="tipo_asamblea" value="extraordinaria" <?= $tipoActual === 'extraordinaria' ? 'checked' : '' ?> />
      <span>Extraordinaria</span>
    </label>
  </div>
</div>

<div class="campo-grupo">
  <span class="campo-grupo-label">Documento anexo (PDF firmado) <?= $documentoActual ? '' : '*' ?></span>
  <label class="dropzone">
    <input type="file" name="archivos[]" accept=".pdf" class="dropzone-input" <?= $documentoActual ? '' : 'required' ?> />
    <span class="dropzone-icon" aria-hidden="true">⬆</span>
    <span class="dropzone-text">
      Arrastra el PDF o haz clic para elegirlo
      <small>Versión escaneada con firmas. Máximo 10 MB.<?= $documentoActual ? ' Si no subes uno nuevo, se conserva el actual.' : '' ?></small>
    </span>
    <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Seleccionar</span>
  </label>
  <p class="dropzone-filenames"></p>
  <?php if ($documentoActual): ?>
    <p class="form-nota">Documento actual: <a href="/panel/archivo?id=<?= (int) $documentoActual['id'] ?>">📎 <?= htmlspecialchars($documentoActual['archivo_nombre_original']) ?></a></p>
  <?php endif; ?>
</div>
