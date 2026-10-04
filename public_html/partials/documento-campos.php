<?php
// Campos del formulario de documento — compartidos por el modal "Nuevo
// documento" y cada modal "Editar documento" de /panel/documentos. Espera
// $doc: [] para "nuevo" (valores vacíos/default, con la opción de
// "Reemplaza una versión anterior"), o la fila de documentos para editar
// una existente (sin esa opción — editar modifica el registro tal cual).
// $nombresSugeridos viene de index.php (getNombresDocumentosUsados() +
// algunos nombres comunes de ejemplo).
$esEdicionCampos = !empty($doc);
$categoriaActualDoc = $doc['categoria'] ?? 'reglamento';
?>
<label>
  Nombre del documento *
  <input type="text" name="titulo" list="nombres-documentos-sugeridos" value="<?= htmlspecialchars($doc['titulo'] ?? '') ?>" placeholder="ej. Reglamento de Uso de Alberca" required />
</label>
<p class="form-nota" style="margin-top: -10px;">Elige uno sugerido o escribe el nombre que necesites.</p>

<label>
  Categoría *
  <select name="categoria" required>
    <?php foreach (CATEGORIAS_DOCUMENTO as $cat): ?>
      <option value="<?= htmlspecialchars($cat) ?>" <?= $categoriaActualDoc === $cat ? 'selected' : '' ?>><?= htmlspecialchars(etiquetaCategoriaDocumento($cat)) ?></option>
    <?php endforeach; ?>
  </select>
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Fecha del documento *
    <input type="date" name="fecha_documento" value="<?= htmlspecialchars($doc['fecha_documento'] ?? date('Y-m-d')) ?>" required />
  </label>
  <label style="flex: 1;">
    Vigente a partir de
    <input type="date" name="vigente_desde" value="<?= htmlspecialchars($doc['vigente_desde'] ?? '') ?>" />
  </label>
</div>

<label>
  Descripción breve (opcional)
  <textarea name="descripcion" rows="3" placeholder="Qué cambia, a quién aplica, puntos clave..."><?= htmlspecialchars($doc['descripcion'] ?? '') ?></textarea>
</label>

<div class="campo-grupo">
  <span class="campo-grupo-label">Archivo (PDF) <?= $esEdicionCampos ? '' : '*' ?></span>
  <label class="dropzone">
    <input type="file" name="archivos[]" accept=".pdf" class="dropzone-input" <?= $esEdicionCampos ? '' : 'required' ?> />
    <span class="dropzone-icon" aria-hidden="true">⬆</span>
    <span class="dropzone-text">
      Arrastra el PDF o haz clic para elegirlo
      <small>Máximo 10 MB.<?= $esEdicionCampos ? ' Si no subes uno nuevo, se conserva el actual.' : '' ?></small>
    </span>
    <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Seleccionar</span>
  </label>
  <p class="dropzone-filenames"></p>
  <?php if ($esEdicionCampos): ?>
    <p class="form-nota">Archivo actual: <a href="/panel/documento?id=<?= (int) $doc['id'] ?>" target="_blank" rel="noopener" class="btn-editar">Ver</a></p>
  <?php endif; ?>
</div>

<?php if (!$esEdicionCampos): ?>
  <label class="campo-checkbox">
    <input type="checkbox" name="reemplaza" />
    <span>Reemplaza una versión anterior. El documento previo con el mismo nombre se moverá a "Versiones anteriores".</span>
  </label>
<?php endif; ?>
