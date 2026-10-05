<?php
// Campos del formulario de "Nuevo registro de estancia" — usado por el
// único modal de /panel/registro-estancia (no hay "editar", solo alta; la
// mesa cambia el estatus desde detalle.php). Espera:
// - $esMesa: bool
// - $villaPropia: la villa del usuario logueado (ya con getVillaPorId()),
//   null si es mesa (la mesa elige villa en un <select>).
// - $villas: array de getVillas(), solo se usa si $esMesa.
$capacidadPropia = $villaPropia ? capacidadPorRecamaras((int) $villaPropia['recamaras_registradas']) : 0;

// Igual que los bloques repetibles de villa-campos.php: nombre + tipo,
// name="acompanante_nombre_N" / "acompanante_tipo_N" — índices planos, no
// "acompanantes[][...]", mismo criterio en todo el sitio.
$bloqueAcompanante = function (int $indice) {
    ?>
    <div class="bloque-repetible bloque-repetible--compacto">
      <div class="bloque-repetible-encabezado">
        <span data-repetible-numero>Acompañante <?= $indice ?></span>
        <button type="button" class="bloque-repetible-quitar" data-repetible-quitar aria-label="Quitar este acompañante">🗑</button>
      </div>
      <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <label style="flex: 2; min-width: 180px;">
          Nombre completo
          <input type="text" name="acompanante_nombre_<?= $indice ?>" placeholder="Nombre y apellidos" />
        </label>
        <label style="flex: 1; min-width: 140px;">
          Tipo
          <select name="acompanante_tipo_<?= $indice ?>">
            <option value="adulto">Adulto</option>
            <option value="menor">Menor de edad</option>
            <option value="infante">Infante (≤3 años)</option>
          </select>
        </label>
      </div>
    </div>
    <?php
};

$bloqueVehiculo = function (int $indice) {
    ?>
    <div class="bloque-repetible bloque-repetible--compacto">
      <div class="bloque-repetible-encabezado">
        <span data-repetible-numero>Vehículo <?= $indice ?></span>
        <?php if ($indice > 1): ?><button type="button" class="bloque-repetible-quitar" data-repetible-quitar aria-label="Quitar este vehículo">🗑</button><?php endif; ?>
      </div>
      <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <label style="flex: 1; min-width: 160px;">
          Placas <?= $indice === 1 ? '*' : '' ?>
          <input type="text" name="vehiculo_placas_<?= $indice ?>" placeholder="ej. ABC-123-D" <?= $indice === 1 ? 'required' : '' ?> />
        </label>
        <label style="flex: 1; min-width: 160px;">
          Tipo de vehículo <?= $indice === 1 ? '*' : '' ?>
          <select name="vehiculo_tipo_<?= $indice ?>" <?= $indice === 1 ? 'required' : '' ?>>
            <option value="automovil">Automóvil</option>
            <option value="camioneta">Camioneta</option>
            <option value="motocicleta">Motocicleta</option>
            <option value="otro">Otro</option>
          </select>
        </label>
      </div>
    </div>
    <?php
};
?>
<div class="form-card" data-reveal>
  <span class="form-card-numero">1 · Inmueble y fechas</span>

  <?php if ($esMesa): ?>
    <label>
      Villa o departamento *
      <select name="villa_usuario_id" data-villa-select required>
        <option value="">Selecciona...</option>
        <?php foreach ($villas as $v): ?>
          <option value="<?= (int) $v['id'] ?>" data-capacidad="<?= capacidadPorRecamaras((int) $v['recamaras_registradas']) ?>">
            Villa <?= htmlspecialchars($v['villa']) ?> — <?= htmlspecialchars($v['titulares'][0]['nombre'] ?? $v['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  <?php else: ?>
    <label>
      Villa o departamento
      <input type="text" value="Villa <?= htmlspecialchars($villaPropia['villa'] ?? '') ?> — <?= htmlspecialchars($villaPropia['nombre'] ?? '') ?>" disabled />
    </label>
    <input type="hidden" name="villa_usuario_id" value="<?= (int) $usuario['id'] ?>" data-villa-capacidad-fija="<?= $capacidadPropia ?>" />
  <?php endif; ?>

  <div style="display: flex; gap: 16px; flex-wrap: wrap;">
    <label style="flex: 1; min-width: 160px;">
      Fecha de llegada *
      <input type="date" name="fecha_llegada" data-fecha-llegada required />
    </label>
    <label style="flex: 1; min-width: 140px;">
      Hora estimada de llegada *
      <input type="text" name="hora_llegada" class="hora-input" inputmode="numeric" pattern="^([01]\d|2[0-3]):[0-5]\d$" placeholder="HH:MM" maxlength="5" required />
      <span class="campo-advertencia" hidden>Solo números — usa formato HH:MM en 24 horas.</span>
    </label>
  </div>
  <div style="display: flex; gap: 16px; flex-wrap: wrap;">
    <label style="flex: 1; min-width: 160px;">
      Fecha de salida *
      <input type="date" name="fecha_salida" data-fecha-salida required />
    </label>
    <label style="flex: 1; min-width: 140px;">
      Hora estimada de salida *
      <input type="text" name="hora_salida" class="hora-input" inputmode="numeric" pattern="^([01]\d|2[0-3]):[0-5]\d$" placeholder="HH:MM" maxlength="5" required />
      <span class="campo-advertencia" hidden>Solo números — usa formato HH:MM en 24 horas.</span>
    </label>
  </div>
</div>

<div class="form-card" data-reveal>
  <span class="form-card-numero">2 · Responsable de la estancia</span>

  <label>
    Nombre completo *
    <input type="text" name="responsable_nombre" placeholder="Nombre y apellidos" required />
  </label>

  <label>
    Teléfono durante la estancia (opcional)
    <input type="text" name="responsable_telefono" class="solo-digitos" inputmode="numeric" placeholder="10 dígitos" maxlength="10" />
    <span class="campo-advertencia" hidden>Solo se permiten números, sin letras.</span>
  </label>

  <div class="campo-grupo">
    <span class="campo-grupo-label">Identificación del responsable (imagen o PDF) *</span>
    <label class="dropzone">
      <input type="file" name="responsable_identificacion" accept=".pdf,.jpg,.jpeg,.png" class="dropzone-input" required />
      <span class="dropzone-icon" aria-hidden="true">⬆</span>
      <span class="dropzone-text">Sube la identificación o toma una foto<small>Solo la consulta la Administración. Máximo 10 MB.</small></span>
      <span class="btn btn-ghost-light dropzone-btn" aria-hidden="true">Seleccionar</span>
    </label>
    <p class="dropzone-filenames"></p>
  </div>
</div>

<div class="form-card" data-reveal>
  <span class="form-card-numero">3 · Ocupantes</span>

  <div style="display: flex; gap: 16px; flex-wrap: wrap;">
    <div class="campo-stepper">
      <span class="campo-grupo-label">Adultos</span>
      <div class="campo-stepper-control">
        <button type="button" class="campo-stepper-btn" data-stepper-menos>−</button>
        <input type="number" name="adultos" min="1" max="30" value="1" data-ocupacion-adultos readonly />
        <button type="button" class="campo-stepper-btn" data-stepper-mas>+</button>
      </div>
    </div>
    <div class="campo-stepper">
      <span class="campo-grupo-label">Menores de edad (4 a 17)</span>
      <div class="campo-stepper-control">
        <button type="button" class="campo-stepper-btn" data-stepper-menos>−</button>
        <input type="number" name="menores" min="0" max="30" value="0" data-ocupacion-menores readonly />
        <button type="button" class="campo-stepper-btn" data-stepper-mas>+</button>
      </div>
    </div>
    <div class="campo-stepper">
      <span class="campo-grupo-label">Infantes (3 años o menos)</span>
      <div class="campo-stepper-control">
        <button type="button" class="campo-stepper-btn" data-stepper-menos>−</button>
        <input type="number" name="infantes" min="0" max="30" value="0" data-ocupacion-infantes readonly />
        <button type="button" class="campo-stepper-btn" data-stepper-mas>+</button>
      </div>
    </div>
  </div>

  <div class="ocupacion-barra-wrap" data-ocupacion-resultado>
    <div class="ocupacion-barra-encabezado">
      <strong data-ocupacion-texto>Ocupación: 1 de <?= $capacidadPropia ?: '—' ?> equivalentes</strong>
      <span data-ocupacion-detalle></span>
    </div>
    <div class="ocupacion-barra">
      <div class="ocupacion-barra-dentro" data-ocupacion-barra-dentro></div>
      <div class="ocupacion-barra-excedente" data-ocupacion-barra-excedente></div>
    </div>
    <p class="form-nota" data-ocupacion-nota></p>
  </div>

  <div class="form-card" data-reveal data-repetible-wrap data-repetible-prefijo="Acompañante">
    <span class="campo-grupo-label">Nombres de los acompañantes</span>
    <p class="form-nota">Todos menos el responsable — un renglón por persona (opcional, pero ayuda a Administración a identificarlos en la entrada).</p>
    <div data-repetible-lista>
      <?php $bloqueAcompanante(1); ?>
    </div>
    <template data-repetible-template>
      <?php $bloqueAcompanante(0); ?>
    </template>
    <button type="button" class="btn btn-ghost-light" data-repetible-agregar>+ Agregar acompañante</button>
  </div>
</div>

<div class="form-card" data-reveal>
  <span class="form-card-numero">4 · Vehículos</span>

  <div class="campo-grupo">
    <div class="pill-radio-group" role="radiogroup" aria-label="Vehículo">
      <label class="pill-radio pill-radio--grande">
        <input type="radio" name="tiene_vehiculo" value="si" data-toggle-bloque="bloque-vehiculos" checked />
        <span>Sí, llegamos en vehículo</span>
      </label>
      <label class="pill-radio pill-radio--grande">
        <input type="radio" name="tiene_vehiculo" value="no" data-toggle-bloque="bloque-vehiculos" />
        <span>Sin vehículo</span>
      </label>
    </div>
  </div>

  <div id="bloque-vehiculos" data-repetible-wrap data-repetible-prefijo="Vehículo">
    <div data-repetible-lista>
      <?php $bloqueVehiculo(1); ?>
    </div>
    <template data-repetible-template>
      <?php $bloqueVehiculo(0); ?>
    </template>
    <button type="button" class="btn btn-ghost-light" data-repetible-agregar>+ Agregar otro vehículo</button>
  </div>
</div>

<div class="form-card" data-reveal>
  <span class="form-card-numero">5 · Mascotas</span>

  <div class="campo-grupo">
    <div class="pill-radio-group" role="radiogroup" aria-label="Mascota">
      <label class="pill-radio pill-radio--grande">
        <input type="radio" name="tiene_mascota" value="si" data-toggle-bloque="bloque-mascota" checked />
        <span>Sí, traemos mascota</span>
      </label>
      <label class="pill-radio pill-radio--grande">
        <input type="radio" name="tiene_mascota" value="no" data-toggle-bloque="bloque-mascota" />
        <span>Sin mascota</span>
      </label>
    </div>
  </div>

  <div id="bloque-mascota" class="form-row-flex" data-mostrar-display="flex">
    <label style="flex: 1; min-width: 140px;">
      Tipo de mascota *
      <select name="mascota_tipo">
        <option value="perro">Perro</option>
        <option value="gato">Gato</option>
        <option value="otro">Otro</option>
      </select>
    </label>
    <label style="flex: 1; min-width: 140px;">
      Raza / tamaño
      <input type="text" name="mascota_raza_tamano" placeholder="ej. mediano" />
    </label>
    <label style="flex: 1; min-width: 100px;">
      Cantidad *
      <input type="number" name="mascota_cantidad" min="1" max="10" value="1" />
    </label>
  </div>
</div>

<div class="form-card" data-reveal>
  <span class="form-card-numero">6 · Compromisos del responsable</span>

  <label class="campo-checkbox">
    <input type="checkbox" name="reglamento_aceptado" value="1" required />
    Me hago responsable de que todos los ocupantes cumplan el <a href="/panel/documentos" target="_blank" rel="noopener">Reglamento del Condominio Villas del Palmar</a> durante la estancia. *
  </label>

  <label class="campo-checkbox">
    <input type="checkbox" name="danos_aceptado" value="1" required />
    Me comprometo a cuidar las áreas comunes y a asumir el costo de reparación de cualquier daño que causen los ocupantes, sus vehículos o mascotas. *
  </label>

  <label class="campo-checkbox">
    <input type="checkbox" name="multas_aceptado" value="1" required />
    Acepto que, si algún ocupante incurre en mal comportamiento o en conductas contrarias al Reglamento, la Administración podrá aplicar las multas y sanciones económicas que este establece, y que su importe se cargará al estado de cuenta de la villa. *
  </label>

  <div class="candado-aviso" data-excedente-bloque hidden>
    <div style="width: 100%;">
      <strong style="display: block; margin-bottom: 8px;">Cuota por excedente</strong>
      <p data-excedente-detalle style="margin: 0 0 10px;"></p>
      <label class="campo-checkbox">
        <input type="checkbox" name="excedente_aceptado" value="1" data-excedente-checkbox />
        <span data-excedente-texto-checkbox></span>
      </label>
    </div>
  </div>

  <label>
    Escribe tu nombre completo como firma de aceptación *
    <input type="text" name="firma_nombre" placeholder="Nombre del responsable" required />
  </label>

  <label>
    Comentarios para la Administración (opcional)
    <textarea name="comentarios" rows="3" placeholder="ej. llegaremos después de las 22:00, favor de avisar a caseta"></textarea>
  </label>

  <p class="form-nota">Los datos de este registro se usan solo para controlar el acceso y la seguridad del condominio.</p>
</div>
