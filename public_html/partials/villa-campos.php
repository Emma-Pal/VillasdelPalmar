<?php
// Campos del formulario de "Alta de villa" — compartidos por el modal
// "Alta de villa" y cada modal "Editar villa" de /panel/usuarios. Espera
// $villa: [] para "nueva" (con 'titulares' => [] implícito), o la fila de
// usuarios (tipo='propietario', con 'titulares' ya cargado por
// getVillaPorId()) para editar una existente.
//
// El titular 1 siempre es "Propietario" (fijo, no se puede cambiar desde
// aquí) — a partir del titular 2 se elige Propietario o Copropietario. Los
// bloques de titular 2+ se capturan con campos planos indexados
// (titular_nombre_2, titular_nombre_3, ...) en vez de name="titulares[][...]"
// para poder reusar procesarArchivoSubido() tal cual (espera un $_FILES[$campo]
// simple, no una matriz anidada) — villa-guardar.php simplemente recorre los
// índices que de verdad llegaron en el POST.
$esEdicionVilla = !empty($villa);
$titularesExistentes = $villa['titulares'] ?? [];
$titular1 = $titularesExistentes[0] ?? [];
$titularesAdicionales = array_slice($titularesExistentes, 1);

// Igual que $archivoActual en colaborador-campos.php: "Actual: Ver" sin
// mostrar el nombre guardado, con lightbox si es imagen o pestaña nueva si
// es PDF. $tipo decide a qué proxy apunta (villa o titular).
$archivoVillaActual = function (string $campo, string $etiqueta) use ($villa) {
    $nombre = $villa[$campo . '_archivo_nombre_original'] ?? null;
    $id = $villa['id'] ?? null;
    if (!$nombre || !$id) return '';
    $href = '/panel/usuarios/villa-archivo?id=' . (int) $id . '&campo=' . $campo;
    if (esImagen($nombre)) {
        return '<p class="form-nota">Actual: <button type="button" class="btn-editar" data-lightbox-src="' . htmlspecialchars($href) . '" data-lightbox-nombre="' . htmlspecialchars($etiqueta) . '">Ver</button></p>';
    }
    return '<p class="form-nota">Actual: <a href="' . htmlspecialchars($href) . '" target="_blank" rel="noopener" class="btn-editar">Ver</a></p>';
};

$archivoTitularActual = function (array $t, string $campo, string $etiqueta) {
    $nombre = $t[$campo . '_archivo_nombre_original'] ?? null;
    $id = $t['id'] ?? null;
    if (!$nombre || !$id) return '';
    $href = '/panel/usuarios/titular-archivo?id=' . (int) $id . '&campo=' . $campo;
    if (esImagen($nombre)) {
        return '<p class="form-nota">Actual: <button type="button" class="btn-editar" data-lightbox-src="' . htmlspecialchars($href) . '" data-lightbox-nombre="' . htmlspecialchars($etiqueta) . '">Ver</button></p>';
    }
    return '<p class="form-nota">Actual: <a href="' . htmlspecialchars($href) . '" target="_blank" rel="noopener" class="btn-editar">Ver</a></p>';
};

// Imprime un bloque de titular completo. $indice es el número usado en los
// name="..." (titular_nombre_2, etc.); $bloqueado=true es exclusivo del
// titular 1 (sin botón de quitar, "Carácter" fijo en Propietario).
$bloqueTitular = function (int $indice, array $t, bool $bloqueado) use ($archivoTitularActual) {
    $caracterActual = $t['caracter'] ?? 'copropietario';
    ?>
    <div class="bloque-repetible">
      <div class="bloque-repetible-encabezado">
        <span data-repetible-numero>Titular <?= $indice ?></span>
        <?php if ($bloqueado): ?>
          <span class="badge badge--ok">Propietario</span>
          <input type="hidden" name="titular_caracter_<?= $indice ?>" value="propietario" />
        <?php else: ?>
          <button type="button" class="bloque-repetible-quitar" data-repetible-quitar aria-label="Quitar este titular">🗑</button>
        <?php endif; ?>
      </div>

      <?php if (!empty($t['id'])): ?><input type="hidden" name="titular_id_<?= $indice ?>" value="<?= (int) $t['id'] ?>" /><?php endif; ?>

      <label>
        Nombre completo *
        <input type="text" name="titular_nombre_<?= $indice ?>" value="<?= htmlspecialchars($t['nombre'] ?? '') ?>" <?= $bloqueado ? 'required' : '' ?> />
      </label>

      <?php if (!$bloqueado): ?>
        <label>
          Carácter *
          <select name="titular_caracter_<?= $indice ?>">
            <option value="propietario" <?= $caracterActual === 'propietario' ? 'selected' : '' ?>>Propietario</option>
            <option value="copropietario" <?= $caracterActual === 'copropietario' ? 'selected' : '' ?>>Copropietario</option>
          </select>
        </label>
      <?php endif; ?>

      <label>
        CURP <?= $bloqueado ? '*' : '' ?>
        <input type="text" name="titular_curp_<?= $indice ?>" value="<?= htmlspecialchars($t['curp'] ?? '') ?>" maxlength="18" placeholder="18 caracteres" <?= $bloqueado ? 'required' : '' ?> />
      </label>

      <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <div class="campo-grupo" style="flex: 1; min-width: 200px;">
          <span class="campo-grupo-label">INE — frente <?= $bloqueado ? '*' : '' ?></span>
          <label class="dropzone">
            <input type="file" name="titular_ine_frente_<?= $indice ?>" accept=".pdf,.jpg,.jpeg,.png" class="dropzone-input" <?= $bloqueado && empty($t['id']) ? 'required' : '' ?> />
            <span class="dropzone-icon" aria-hidden="true">⬆</span>
            <span class="dropzone-text">Arrastra o elige el archivo<small>Imagen o PDF · máx. 10 MB</small></span>
            <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Elegir archivo</span>
          </label>
          <p class="dropzone-filenames"></p>
          <?= $archivoTitularActual($t, 'ine_frente', 'INE (frente) — Titular ' . $indice) ?>
        </div>
        <div class="campo-grupo" style="flex: 1; min-width: 200px;">
          <span class="campo-grupo-label">INE — reverso</span>
          <label class="dropzone">
            <input type="file" name="titular_ine_reverso_<?= $indice ?>" accept=".pdf,.jpg,.jpeg,.png" class="dropzone-input" />
            <span class="dropzone-icon" aria-hidden="true">⬆</span>
            <span class="dropzone-text">Arrastra o elige el archivo<small>Imagen o PDF · máx. 10 MB</small></span>
            <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Elegir archivo</span>
          </label>
          <p class="dropzone-filenames"></p>
          <?= $archivoTitularActual($t, 'ine_reverso', 'INE (reverso) — Titular ' . $indice) ?>
        </div>
      </div>

      <?php if ($bloqueado): ?><p class="form-nota" style="margin-top: -4px;">Teléfono o correo — al menos uno de los dos es obligatorio, para poder contactar al propietario.</p><?php endif; ?>
      <div style="display: flex; gap: 16px;">
        <label style="flex: 1;">
          Teléfono<?= $bloqueado ? '' : ' (opcional)' ?>
          <input type="text" name="titular_telefono_<?= $indice ?>" value="<?= htmlspecialchars($t['telefono'] ?? '') ?>" inputmode="numeric" placeholder="10 dígitos" maxlength="10" />
        </label>
        <label style="flex: 1;">
          Correo electrónico<?= $bloqueado ? '' : ' (opcional)' ?>
          <input type="email" name="titular_correo_<?= $indice ?>" value="<?= htmlspecialchars($t['correo'] ?? '') ?>" />
        </label>
      </div>
    </div>
    <?php
};
?>
<div class="form-card" data-reveal>
  <span class="form-card-numero">1 · Villa y acceso</span>

  <label>
    Villa núm. *
    <input type="text" name="villa_numero" id="villa-numero-<?= $villa['id'] ?? 'nueva' ?>" class="solo-digitos" value="<?= htmlspecialchars($villa['villa'] ?? '') ?>" inputmode="numeric" pattern="[0-9]+" maxlength="10" required />
    <span class="campo-advertencia" hidden>Solo se permiten números, sin letras.</span>
  </label>

  <label>
    <?= $esEdicionVilla ? 'Nueva contraseña (dejar en blanco para no cambiarla)' : 'Contraseña asignada *' ?>
    <span class="campo-password-generar">
      <input type="text" name="password_villa" id="password-villa-<?= $villa['id'] ?? 'nueva' ?>" minlength="8" <?= $esEdicionVilla ? '' : 'required' ?> />
      <button type="button" class="btn btn-ghost-light" data-generar-password data-password-target="password-villa-<?= $villa['id'] ?? 'nueva' ?>" data-villa-target="villa-numero-<?= $villa['id'] ?? 'nueva' ?>">Generar</button>
    </span>
  </label>
  <p class="form-nota">Contraseña inicial: el propietario la cambia en su primer ingreso.</p>
</div>

<div class="form-card" data-reveal data-repetible-wrap data-repetible-prefijo="Titular">
  <span class="form-card-numero">2 · Titulares de la villa</span>
  <p class="form-nota">Agrega a cada propietario o copropietario. Sus documentos solo los consulta la Administración.</p>

  <div data-repetible-lista>
    <?php $bloqueTitular(1, $titular1, true); ?>
    <?php foreach ($titularesAdicionales as $i => $t): $bloqueTitular($i + 2, $t, false); ?><?php endforeach; ?>
  </div>

  <template data-repetible-template>
    <?php $bloqueTitular(0, [], false); ?>
  </template>

  <button type="button" class="btn btn-ghost-light" data-repetible-agregar>+ Agregar titular</button>
</div>

<div class="form-card" data-reveal>
  <span class="form-card-numero">3 · Recámaras y capacidad</span>

  <div style="display: flex; gap: 16px; flex-wrap: wrap;">
    <label style="flex: 1; min-width: 140px;">
      Recámaras registradas *
      <input type="number" name="recamaras_registradas" min="0" max="20" data-recamaras-registradas value="<?= htmlspecialchars((string) ($villa['recamaras_registradas'] ?? '')) ?>" required />
    </label>
    <label style="flex: 1; min-width: 140px;">
      Recámaras físicas *
      <input type="number" name="recamaras_fisicas" min="0" max="20" data-recamaras-fisicas value="<?= htmlspecialchars((string) ($villa['recamaras_fisicas'] ?? '')) ?>" required />
    </label>
  </div>
  <p class="candado-aviso" data-recamaras-advertencia hidden>⚠ Hay más recámaras físicas que registradas. Esta villa queda marcada para revisión de Administración.</p>

  <div class="campo-grupo">
    <span class="campo-grupo-label">Capacidad de ocupación</span>
    <p style="margin: 0;"><strong data-capacidad-texto><?= capacidadPorRecamaras((int) ($villa['recamaras_registradas'] ?? 0)) ?> ocupantes</strong></p>
    <input type="hidden" name="capacidad_ocupacion" data-capacidad-valor value="<?= capacidadPorRecamaras((int) ($villa['recamaras_registradas'] ?? 0)) ?>" />
  </div>
  <p class="form-nota">Se calcula según las recámaras registradas: estudio (0) = 3 personas, 1 recámara = 4, 2 recámaras = 7, 3 recámaras = 10.</p>
</div>

<div class="form-card" data-reveal>
  <span class="form-card-numero">4 · Documentos del inmueble</span>

  <div class="campo-grupo">
    <span class="campo-grupo-label">Escritura del inmueble (PDF) <?= $esEdicionVilla && !empty($villa['escritura_archivo']) ? '' : '*' ?></span>
    <label class="dropzone">
      <input type="file" name="escritura" accept=".pdf" class="dropzone-input" <?= $esEdicionVilla && !empty($villa['escritura_archivo']) ? '' : 'required' ?> />
      <span class="dropzone-icon" aria-hidden="true">⬆</span>
      <span class="dropzone-text">Arrastra la escritura o haz clic para elegirla<small>Escritura completa, escaneada. Máximo 10 MB.<?= $esEdicionVilla && !empty($villa['escritura_archivo']) ? ' Si no subes una nueva, se conserva la actual.' : '' ?></small></span>
      <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Seleccionar</span>
    </label>
    <p class="dropzone-filenames"></p>
    <?= $archivoVillaActual('escritura', 'Escritura del inmueble') ?>
  </div>

  <div class="campo-grupo">
    <span class="campo-grupo-label">Relación de copropietarios (PDF)</span>
    <label class="dropzone">
      <input type="file" name="relacion_copropietarios" accept=".pdf" class="dropzone-input" />
      <span class="dropzone-icon" aria-hidden="true">⬆</span>
      <span class="dropzone-text">Obligatoria si hay más de un titular<small>Máximo 10 MB.<?= $esEdicionVilla && !empty($villa['relacion_copropietarios_archivo']) ? ' Si no subes una nueva, se conserva la actual.' : '' ?></small></span>
      <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Seleccionar</span>
    </label>
    <p class="dropzone-filenames"></p>
    <?= $archivoVillaActual('relacion_copropietarios', 'Relación de copropietarios') ?>
  </div>

  <p class="form-nota">Los datos personales y documentos de los titulares se usan únicamente para la administración del condominio.</p>
</div>
