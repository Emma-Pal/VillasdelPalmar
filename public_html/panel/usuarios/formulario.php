<?php
// Compartido por nuevo.php y editar.php.

$esEdicion = isset($_GET['id']);
$usuarioEditado = null;
$error = null;

if ($esEdicion) {
    $usuarioEditado = getUsuarioPorId((int) $_GET['id']);
    if (!$usuarioEditado) {
        header('Location: /panel/usuarios');
        exit;
    }
}

$title = ($esEdicion ? 'Editar usuario' : 'Nuevo usuario') . ' — Villas del Palmar';
$description = 'Administración de cuentas de Villas del Palmar.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $tipo = $_POST['tipo'] ?? 'propietario';
    $nombre = trim($_POST['nombre'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $usuarioLogin = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirmar = $_POST['passwordConfirmar'] ?? '';

    // Al crear, la contraseña es obligatoria; al editar es opcional (dejarla
    // en blanco = no se toca), pero si se manda algo debe cumplir el mínimo
    // y estar bien confirmada.
    if (!$esEdicion || $password !== '') {
        if (!passwordEsValida($password)) {
            $error = $esEdicion
                ? 'La nueva contraseña debe tener al menos ' . MIN_LARGO_PASSWORD . ' caracteres.'
                : 'La contraseña es obligatoria y debe tener al menos ' . MIN_LARGO_PASSWORD . ' caracteres.';
        } elseif ($password !== $passwordConfirmar) {
            $error = $esEdicion
                ? 'La confirmación no coincide con la nueva contraseña.'
                : 'La confirmación no coincide con la contraseña.';
        }
    }

    if ($error === null) {
        try {
            if ($esEdicion) {
                $id = $usuarioEditado['id'];
                $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;
                actualizarUsuario($id, $tipo, $nombre, $cargo, $usuarioLogin, $passwordHash);

                // Si el usuario se edita a sí mismo, se refresca la sesión
                // para que el header muestre los datos correctos de inmediato.
                if ((int) $_SESSION['usuario']['id'] === (int) $id) {
                    $_SESSION['usuario'] = [
                        'id' => $id,
                        'tipo' => $tipo,
                        'nombre' => $nombre,
                        'cargo' => $tipo === 'mesa' ? $cargo : null,
                    ];
                }
            } else {
                crearUsuario($tipo, $nombre, $cargo, $usuarioLogin, password_hash($password, PASSWORD_BCRYPT));
            }
            header('Location: /panel/usuarios');
            exit;
        } catch (PDOException $e) {
            $error = stripos($e->getMessage(), 'Duplicate entry') !== false
                ? 'Ese nombre de usuario ya existe. Elige otro.'
                : ($esEdicion ? 'No se pudo guardar el cambio. Revisa los datos.' : 'No se pudo crear la cuenta. Revisa los datos.');
        }
    }

    // Si hubo error, se conservan los datos capturados para no perderlos.
    $datosFormulario = ['tipo' => $tipo, 'nombre' => $nombre, 'cargo' => $cargo, 'usuario' => $usuarioLogin];
} else {
    $datosFormulario = $usuarioEditado ?: ['tipo' => 'propietario', 'nombre' => '', 'cargo' => '', 'usuario' => ''];
}

$accionFormulario = $esEdicion ? '/panel/usuarios/editar?id=' . (int) $usuarioEditado['id'] : '/panel/usuarios/nuevo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <a href="/panel/usuarios" class="back-link">← Volver a usuarios</a>
      <span class="eyebrow">Comité</span>
      <h1><?= $esEdicion ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
      <?php if ($esEdicion): ?>
        <p class="page-banner-lead">Cambiar aquí el "Tipo" da o quita privilegios de comité.</p>
      <?php endif; ?>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 560px; margin: 0 auto;">
      <?php if ($error): ?>
        <p class="login-error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form
        action="<?= htmlspecialchars($accionFormulario) ?>"
        method="POST"
        class="contact-form"
      >
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

        <label>
          Tipo de cuenta
          <select name="tipo" id="tipo-select" required>
            <option value="propietario" <?= $datosFormulario['tipo'] === 'propietario' ? 'selected' : '' ?>>Propietario (cuenta compartida)</option>
            <option value="mesa" <?= $datosFormulario['tipo'] === 'mesa' ? 'selected' : '' ?>>Comité (privilegios de administrador)</option>
          </select>
        </label>

        <label>
          Nombre completo
          <input type="text" name="nombre" value="<?= htmlspecialchars($datosFormulario['nombre']) ?>" required />
        </label>

        <label id="campo-cargo">
          Cargo (ej. "Tesorero")
          <input type="text" name="cargo" value="<?= htmlspecialchars($datosFormulario['cargo'] ?? '') ?>" />
        </label>

        <label>
          Usuario (login)
          <input type="text" name="usuario" value="<?= htmlspecialchars($datosFormulario['usuario']) ?>" required />
        </label>

        <label>
          <?= $esEdicion ? 'Nueva contraseña (dejar en blanco para no cambiarla, mínimo 8 caracteres)' : 'Contraseña (mínimo 8 caracteres)' ?>
          <input type="password" name="password" minlength="8" autocomplete="new-password" <?= $esEdicion ? '' : 'required' ?> />
        </label>

        <label>
          Confirmar <?= $esEdicion ? 'nueva contraseña' : 'contraseña' ?>
          <input type="password" name="passwordConfirmar" minlength="8" autocomplete="new-password" <?= $esEdicion ? '' : 'required' ?> />
        </label>

        <button type="submit" class="btn btn-primary">
          <?= $esEdicion ? 'Guardar cambios' : 'Crear usuario' ?>
        </button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
