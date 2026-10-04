<?php
// Campos del formulario de "Administradores" (cuentas tipo='mesa') —
// compartidos por el modal "Nuevo administrador" y cada modal "Editar
// administrador" de /panel/usuarios. Espera $admin: [] para "nuevo", o la
// fila de usuarios para editar uno existente.
$esEdicionAdmin = !empty($admin);
?>
<label>
  Nombre completo *
  <input type="text" name="nombre" value="<?= htmlspecialchars($admin['nombre'] ?? '') ?>" required />
</label>

<div style="display: flex; gap: 16px;">
  <label style="flex: 1;">
    Cargo *
    <input type="text" name="cargo" value="<?= htmlspecialchars($admin['cargo'] ?? '') ?>" placeholder="ej. Tesorero" required />
  </label>
  <label style="flex: 1;">
    Usuario (login) *
    <input type="text" name="usuario" value="<?= htmlspecialchars($admin['usuario'] ?? '') ?>" required />
  </label>
</div>

<hr class="form-separador" />

<label>
  <?= $esEdicionAdmin ? 'Nueva contraseña (dejar en blanco para no cambiarla, mínimo 8 caracteres)' : 'Contraseña *' ?>
  <input type="password" name="password" minlength="8" autocomplete="new-password" <?= $esEdicionAdmin ? '' : 'required' ?> />
</label>

<label>
  Confirmar <?= $esEdicionAdmin ? 'nueva contraseña' : 'contraseña' ?><?= $esEdicionAdmin ? '' : ' *' ?>
  <input type="password" name="passwordConfirmar" minlength="8" autocomplete="new-password" <?= $esEdicionAdmin ? '' : 'required' ?> />
</label>
