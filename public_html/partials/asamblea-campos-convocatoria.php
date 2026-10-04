<?php
// Campos del formulario de convocatoria — compartidos por el modal "Nueva
// convocatoria" y cada modal "Editar convocatoria" de /panel/asambleas.
// Espera $conv: [] para "nueva" (valores vacíos/default), o la fila de
// publicaciones (con 'archivos' ya cargado) para editar una existente.
$tipoActual = $conv['tipo_asamblea'] ?? 'ordinaria';
$documentoActual = $conv['archivos'][0] ?? null;
// MySQL regresa la hora como "HH:MM:SS" — el campo de captura solo usa HH:MM.
$horaActual = !empty($conv['hora_evento']) ? substr($conv['hora_evento'], 0, 5) : '';
?>
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

<label>
  Título de la convocatoria *
  <input type="text" name="titulo" value="<?= htmlspecialchars($conv['titulo'] ?? '') ?>" placeholder="ej. Asamblea General Ordinaria de Propietarios" required />
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Fecha *
    <input type="date" name="fecha_evento" value="<?= htmlspecialchars($conv['fecha_evento'] ?? '') ?>" required />
  </label>
  <label style="flex: 1;">
    Hora (1.ª convocatoria) *
    <input
      type="text"
      name="hora_evento"
      class="hora-input"
      value="<?= htmlspecialchars($horaActual) ?>"
      inputmode="numeric"
      pattern="^([01]\d|2[0-3]):[0-5]\d$"
      placeholder="HH:MM"
      maxlength="5"
      title="Formato de 24 horas, ej. 14:30"
      required
    />
    <span class="campo-advertencia" hidden>Solo números — usa formato HH:MM en 24 horas (ej. 14:30).</span>
  </label>
</div>

<label>
  Lugar *
  <input type="text" name="lugar_evento" value="<?= htmlspecialchars($conv['lugar_evento'] ?? '') ?>" placeholder="ej. Casa club / salón de usos múltiples" required />
</label>

<div class="campo-grupo">
  <span class="campo-grupo-label">Documento de convocatoria (PDF) <?= $documentoActual ? '' : '*' ?></span>
  <label class="dropzone">
    <input type="file" name="archivos[]" accept=".pdf" class="dropzone-input" <?= $documentoActual ? '' : 'required' ?> />
    <span class="dropzone-icon" aria-hidden="true">⬆</span>
    <span class="dropzone-text">
      Arrastra el PDF firmado o haz clic para elegirlo
      <small>Incluye orden del día. Máximo 10 MB.<?= $documentoActual ? ' Si no subes uno nuevo, se conserva el actual.' : '' ?></small>
    </span>
    <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Seleccionar</span>
  </label>
  <p class="dropzone-filenames"></p>
  <?php if ($documentoActual): ?>
    <p class="form-nota">Documento actual: <a href="/panel/archivo?id=<?= (int) $documentoActual['id'] ?>" target="_blank" rel="noopener" class="btn-editar">Ver</a></p>
  <?php endif; ?>
</div>

<label>
  Mensaje para los propietarios (opcional)
  <textarea name="cuerpo" rows="3" placeholder="Breve invitación que aparecerá junto a la convocatoria"><?= htmlspecialchars($conv['cuerpo'] ?? '') ?></textarea>
</label>
