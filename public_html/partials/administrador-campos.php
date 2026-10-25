<?php
// Campos del formulario de "Administradores" (cuentas tipo='mesa') —
// compartidos por el modal "Nuevo administrador" y cada modal "Editar
// administrador" de /panel/usuarios. Espera $admin: [] para "nuevo", o la
// fila de usuarios para editar uno existente.
//
// Ya no se pide "Cargo": un administrador no necesariamente forma parte del
// Consejo/mesa directiva — son cuentas de trabajadores de confianza para
// ayudar a mantener la página, así que un puesto tipo "Tesorero" no aplica
// aquí (el columna usuarios.cargo sigue existiendo, pero ya no se le escribe
// nada desde este formulario — ver administrador-guardar.php y mi-cuenta.php).
//
// $mostrarPermisoGestionar (bool, opcional): solo index.php lo manda en
// true cuando quien ve este modal es el superadmin Y está editando a OTRO
// administrador (nunca al crear uno, nunca al editarse a sí mismo) — ahí se
// muestra el checkbox para otorgarle/quitarle el permiso de gestionar
// admins. $permisoGestionarActual es su estado actual, para dejar el
// checkbox ya marcado si ya lo tiene.
$esEdicionAdmin = !empty($admin);
$mostrarPermisoGestionar = $mostrarPermisoGestionar ?? false;
$permisoGestionarActual = $permisoGestionarActual ?? false;
?>
<label>
  Nombre completo *
  <input type="text" name="nombre" value="<?= htmlspecialchars($admin['nombre'] ?? '') ?>" required />
</label>

<label>
  Usuario (login) *
  <input type="text" name="usuario" value="<?= htmlspecialchars($admin['usuario'] ?? '') ?>" required />
</label>

<hr class="form-separador" />

<label>
  <?= $esEdicionAdmin ? 'Nueva contraseña (dejar en blanco para no cambiarla, mínimo 8 caracteres)' : 'Contraseña *' ?>
  <input type="password" name="password" minlength="8" autocomplete="new-password" <?= $esEdicionAdmin ? '' : 'required' ?> />
</label>

<label>
  Confirmar <?= $esEdicionAdmin ? 'nueva contraseña' : 'contraseña' ?><?= $esEdicionAdmin ? '' : ' *' ?>
  <input type="password" name="passwordConfirmar" minlength="8" autocomplete="new-password" <?= $esEdicionAdmin ? '' : 'required' ?> />
</label>

<?php if ($mostrarPermisoGestionar): ?>
  <hr class="form-separador" />
  <label class="campo-checkbox">
    <input type="checkbox" name="permiso_gestionar_admins" value="1" <?= $permisoGestionarActual ? 'checked' : '' ?> />
    Puede dar de alta o eliminar otras cuentas de administrador
  </label>
  <p class="form-nota">Lo mismo que puede hacer el superadmin en ese sentido — no incluye editar la cuenta de otro administrador, eso se queda exclusivo del superadmin.</p>
<?php endif; ?>
