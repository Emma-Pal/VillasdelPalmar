<?php
// Campos del formulario de "Integrantes del Comité" — compartidos por el
// modal "Nuevo integrante" y cada modal "Editar integrante" de
// /panel/directorio. Espera $miembro: [] para "nuevo", o la fila de
// comite_miembros para editar uno existente. Este directorio es público
// para todos los propietarios, a diferencia del de colaboradores.
//
// $miembro['titular_id'], cuando viene (desde el botón "+ Comité" de un
// titular en /panel/usuarios), vincula el alta con su titular de origen —
// ver comite-guardar.php. No se expone como campo editable, solo viaja
// oculto en el alta nueva.
?>
<?php if (!empty($miembro['titular_id'])): ?>
  <input type="hidden" name="titular_id" value="<?= (int) $miembro['titular_id'] ?>" />
<?php endif; ?>
<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Departamento *
    <select name="departamento" required>
      <?php foreach (DEPARTAMENTOS_COMITE as $d): ?>
        <option value="<?= htmlspecialchars($d) ?>" <?= ($miembro['departamento'] ?? '') === $d ? 'selected' : '' ?>><?= htmlspecialchars(etiquetaDepartamentoComite($d)) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label style="flex: 1;">
    Titular / Suplente *
    <select name="titular_suplente" required>
      <option value="titular" <?= ($miembro['titular_suplente'] ?? 'titular') === 'titular' ? 'selected' : '' ?>>Titular</option>
      <option value="suplente" <?= ($miembro['titular_suplente'] ?? '') === 'suplente' ? 'selected' : '' ?>>Suplente</option>
    </select>
  </label>
</div>

<label>
  Nombre completo *
  <input type="text" name="nombre" value="<?= htmlspecialchars($miembro['nombre'] ?? '') ?>" required />
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Número de villa *
    <input type="text" name="villa" inputmode="numeric" pattern="[0-9]+" title="Solo números" value="<?= htmlspecialchars($miembro['villa'] ?? '') ?>" required />
  </label>
  <label style="flex: 1;">
    Cargo *
    <input type="text" name="cargo" list="cargos-comite-sugeridos" value="<?= htmlspecialchars($miembro['cargo'] ?? '') ?>" placeholder="ej. Presidente" required />
  </label>
</div>

<p class="form-nota" style="margin-top: -4px;">Correo o teléfono — al menos uno de los dos es obligatorio.</p>

<label>
  Correo electrónico
  <input type="email" name="correo" value="<?= htmlspecialchars($miembro['correo'] ?? '') ?>" />
</label>

<label>
  Teléfono
  <input type="tel" name="telefono" value="<?= htmlspecialchars($miembro['telefono'] ?? '') ?>" />
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Periodo (inicio) <span class="candado-sugerido">sugerido</span>
    <input type="date" name="periodo_inicio" value="<?= htmlspecialchars($miembro['periodo_inicio'] ?? '') ?>" />
  </label>
  <label style="flex: 1;">
    Periodo (fin) <span class="candado-sugerido">sugerido</span>
    <input type="date" name="periodo_fin" value="<?= htmlspecialchars($miembro['periodo_fin'] ?? '') ?>" />
  </label>
</div>
