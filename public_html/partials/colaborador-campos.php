<?php
// Campos del formulario de "Personal del condominio" — compartidos por el
// modal "Nuevo colaborador" y cada modal "Editar colaborador" de
// /panel/directorio. Espera $col: [] para "nuevo", o la fila de
// colaboradores para editar uno existente. A propósito NO incluye salario
// (dato sensible que no hace falta en este directorio).
$esEdicionCol = !empty($col);

// Para cada documento: si ya existe, el <input file> deja de ser
// obligatorio y se muestra un link "Ver" al archivo actual.
$documentoActual = function (string $campo) use ($col) {
    $nombre = $col[$campo . '_archivo_nombre_original'] ?? null;
    $id = $col['id'] ?? null;
    if (!$nombre || !$id) return null;
    return ['nombre' => $nombre, 'href' => '/panel/colaborador-archivo?id=' . (int) $id . '&campo=' . explode('_', $campo)[0]];
};
?>
<span class="form-card-numero">1 · Datos generales</span>

<label>
  Nombre completo *
  <input type="text" name="nombre" value="<?= htmlspecialchars($col['nombre'] ?? '') ?>" required />
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Puesto *
    <input type="text" name="puesto" value="<?= htmlspecialchars($col['puesto'] ?? '') ?>" placeholder="ej. Velador" required />
  </label>
  <label style="flex: 1;">
    Área / Departamento *
    <select name="area" required>
      <?php foreach (AREAS_COLABORADOR as $a): ?>
        <option value="<?= htmlspecialchars($a) ?>" <?= ($col['area'] ?? '') === $a ? 'selected' : '' ?>><?= htmlspecialchars(etiquetaAreaColaborador($a)) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
</div>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Fecha de ingreso *
    <input type="date" name="fecha_ingreso" value="<?= htmlspecialchars($col['fecha_ingreso'] ?? '') ?>" required />
  </label>
  <label style="flex: 1;">
    Fotografía (opcional)
    <input type="file" name="foto" accept=".jpg,.jpeg,.png" />
  </label>
</div>

<hr class="form-separador" />
<span class="form-card-numero">2 · Identificación oficial <span class="candado-comite">🔒 Solo comité</span></span>

<label>
  INE (frente y reverso) <?= $esEdicionCol ? '' : '*' ?>
  <input type="file" name="ine_archivo" accept=".pdf,.jpg,.jpeg,.png" <?= $esEdicionCol ? '' : 'required' ?> />
  <?php if ($doc = $documentoActual('ine')): ?><span class="form-nota">Actual: <a href="<?= htmlspecialchars($doc['href']) ?>" target="_blank" rel="noopener">📎 <?= htmlspecialchars($doc['nombre']) ?></a></span><?php endif; ?>
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Número de CURP *
    <input type="text" name="curp_numero" value="<?= htmlspecialchars($col['curp_numero'] ?? '') ?>" maxlength="18" style="text-transform: uppercase;" required />
  </label>
  <label style="flex: 1;">
    Documento CURP <?= $esEdicionCol ? '' : '*' ?>
    <input type="file" name="curp_archivo" accept=".pdf,.jpg,.jpeg,.png" <?= $esEdicionCol ? '' : 'required' ?> />
    <?php if ($doc = $documentoActual('curp')): ?><span class="form-nota">Actual: <a href="<?= htmlspecialchars($doc['href']) ?>" target="_blank" rel="noopener">📎 Ver</a></span><?php endif; ?>
  </label>
</div>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    RFC *
    <input type="text" name="rfc_numero" value="<?= htmlspecialchars($col['rfc_numero'] ?? '') ?>" maxlength="13" style="text-transform: uppercase;" required />
  </label>
  <label style="flex: 1;">
    Constancia de situación fiscal (RFC) <?= $esEdicionCol ? '' : '*' ?>
    <input type="file" name="rfc_archivo" accept=".pdf,.jpg,.jpeg,.png" <?= $esEdicionCol ? '' : 'required' ?> />
    <?php if ($doc = $documentoActual('rfc')): ?><span class="form-nota">Actual: <a href="<?= htmlspecialchars($doc['href']) ?>" target="_blank" rel="noopener">📎 Ver</a></span><?php endif; ?>
  </label>
</div>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    NSS (IMSS) *
    <input type="text" name="nss_numero" value="<?= htmlspecialchars($col['nss_numero'] ?? '') ?>" maxlength="20" required />
  </label>
  <label style="flex: 1;">
    Comprobante NSS / alta IMSS <?= $esEdicionCol ? '' : '*' ?>
    <input type="file" name="nss_archivo" accept=".pdf,.jpg,.jpeg,.png" <?= $esEdicionCol ? '' : 'required' ?> />
    <?php if ($doc = $documentoActual('nss')): ?><span class="form-nota">Actual: <a href="<?= htmlspecialchars($doc['href']) ?>" target="_blank" rel="noopener">📎 Ver</a></span><?php endif; ?>
  </label>
</div>

<hr class="form-separador" />
<span class="form-card-numero">3 · Datos personales <span class="candado-comite">🔒 Solo comité</span></span>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Fecha de nacimiento
    <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($col['fecha_nacimiento'] ?? '') ?>" />
  </label>
  <label style="flex: 1;">
    Lugar de nacimiento
    <input type="text" name="lugar_nacimiento" value="<?= htmlspecialchars($col['lugar_nacimiento'] ?? '') ?>" placeholder="ej. Manzanillo, Colima" />
  </label>
</div>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Nacionalidad
    <input type="text" name="nacionalidad" value="<?= htmlspecialchars($col['nacionalidad'] ?? ($esEdicionCol ? '' : 'Mexicana')) ?>" />
  </label>
  <label style="flex: 1;">
    Estado civil
    <select name="estado_civil">
      <?php $estadosCiviles = ['' => 'Selecciona...', 'Soltero/a' => 'Soltero/a', 'Casado/a' => 'Casado/a', 'Unión libre' => 'Unión libre', 'Divorciado/a' => 'Divorciado/a', 'Viudo/a' => 'Viudo/a']; ?>
      <?php foreach ($estadosCiviles as $valor => $etiqueta): ?>
        <option value="<?= htmlspecialchars($valor) ?>" <?= ($col['estado_civil'] ?? '') === $valor ? 'selected' : '' ?>><?= htmlspecialchars($etiqueta) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
</div>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Teléfono
    <input type="tel" name="telefono" value="<?= htmlspecialchars($col['telefono'] ?? '') ?>" />
  </label>
  <label style="flex: 1;">
    Correo electrónico
    <input type="email" name="correo" value="<?= htmlspecialchars($col['correo'] ?? '') ?>" />
  </label>
</div>

<label>
  Domicilio
  <input type="text" name="domicilio" value="<?= htmlspecialchars($col['domicilio'] ?? '') ?>" placeholder="Calle, colonia, ciudad" />
</label>

<label>
  Comprobante de domicilio
  <input type="file" name="domicilio_archivo" accept=".pdf,.jpg,.jpeg,.png" />
  <?php if ($doc = $documentoActual('domicilio')): ?><span class="form-nota">Actual: <a href="<?= htmlspecialchars($doc['href']) ?>" target="_blank" rel="noopener">📎 <?= htmlspecialchars($doc['nombre']) ?></a></span><?php endif; ?>
</label>

<hr class="form-separador" />
<span class="form-card-numero">4 · Datos laborales y contrato <span class="candado-comite">🔒 Solo comité</span></span>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Tipo de contrato
    <select name="tipo_contrato">
      <?php $tiposContrato = ['' => 'Selecciona...', 'Indeterminado' => 'Indeterminado', 'Determinado' => 'Determinado', 'Por obra' => 'Por obra', 'Honorarios' => 'Honorarios']; ?>
      <?php foreach ($tiposContrato as $valor => $etiqueta): ?>
        <option value="<?= htmlspecialchars($valor) ?>" <?= ($col['tipo_contrato'] ?? '') === $valor ? 'selected' : '' ?>><?= htmlspecialchars($etiqueta) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label style="flex: 1;">
    Turno / horario (opcional)
    <input type="text" name="turno_horario" value="<?= htmlspecialchars($col['turno_horario'] ?? '') ?>" placeholder="ej. Nocturno 22:00–06:00" />
  </label>
</div>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Contrato laboral firmado <?= $esEdicionCol ? '' : '*' ?>
    <input type="file" name="contrato_archivo" accept=".pdf,.jpg,.jpeg,.png" <?= $esEdicionCol ? '' : 'required' ?> />
    <?php if ($doc = $documentoActual('contrato')): ?><span class="form-nota">Actual: <a href="<?= htmlspecialchars($doc['href']) ?>" target="_blank" rel="noopener">📎 <?= htmlspecialchars($doc['nombre']) ?></a></span><?php endif; ?>
  </label>
  <label style="flex: 1;">
    Estatus
    <select name="estatus">
      <option value="activo" <?= ($col['estatus'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
      <option value="baja" <?= ($col['estatus'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
    </select>
  </label>
</div>

<hr class="form-separador" />
<span class="form-card-numero">5 · Contacto de emergencia <span class="candado-comite">🔒 Solo comité</span></span>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Nombre
    <input type="text" name="emergencia_nombre" value="<?= htmlspecialchars($col['emergencia_nombre'] ?? '') ?>" />
  </label>
  <label style="flex: 1;">
    Parentesco (opcional)
    <input type="text" name="emergencia_parentesco" list="parentescos-sugeridos" value="<?= htmlspecialchars($col['emergencia_parentesco'] ?? '') ?>" />
  </label>
</div>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Teléfono 1
    <input type="tel" name="emergencia_telefono1" value="<?= htmlspecialchars($col['emergencia_telefono1'] ?? '') ?>" />
  </label>
  <label style="flex: 1;">
    Teléfono 2 (opcional)
    <input type="tel" name="emergencia_telefono2" value="<?= htmlspecialchars($col['emergencia_telefono2'] ?? '') ?>" />
  </label>
</div>

<label>
  Domicilio
  <input type="text" name="emergencia_domicilio" value="<?= htmlspecialchars($col['emergencia_domicilio'] ?? '') ?>" />
</label>
