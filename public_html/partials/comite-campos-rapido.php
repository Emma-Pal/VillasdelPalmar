<?php
// Versión mínima de comite-campos.php, solo para el botón "+ Comité" de un
// titular en /panel/usuarios: todo lo que ya se conoce de ese titular
// (nombre, villa, correo, teléfono) viaja oculto, sin pedirlo de nuevo, y
// de momento solo hay un Comité (no varios departamentos) — así que lo
// único que de verdad hace falta capturar aquí es el cargo. Si después
// Administración quiere afinar departamento, titular/suplente o periodo,
// lo edita desde /panel/directorio como cualquier otro integrante. Espera
// $miembro con 'titular_id', 'nombre', 'villa', 'correo', 'telefono'.
?>
<input type="hidden" name="titular_id" value="<?= (int) $miembro['titular_id'] ?>" />
<input type="hidden" name="departamento" value="comite_administracion" />
<input type="hidden" name="titular_suplente" value="titular" />
<input type="hidden" name="nombre" value="<?= htmlspecialchars($miembro['nombre']) ?>" />
<input type="hidden" name="villa" value="<?= htmlspecialchars($miembro['villa']) ?>" />
<input type="hidden" name="correo" value="<?= htmlspecialchars($miembro['correo']) ?>" />
<input type="hidden" name="telefono" value="<?= htmlspecialchars($miembro['telefono']) ?>" />

<label>
  Cargo en el Comité *
  <input type="text" name="cargo" list="cargos-comite-sugeridos" placeholder="ej. Presidente" required autofocus />
</label>
