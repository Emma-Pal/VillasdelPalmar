<?php
require_once __DIR__ . '/../../app/bootstrap.php';
requireMesa();

$title = 'Mi cuenta — Villas del Palmar';
$description = 'Editar mis datos de acceso.';
$error = null;
$guardado = false;
$datos = getUsuarioPorId($usuario['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $id = $usuario['id'];
    $nombre = trim($_POST['nombre'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');
    $usuarioLogin = trim($_POST['usuario'] ?? '');
    $contrasenaActual = $_POST['contrasenaActual'] ?? '';
    $password = $_POST['password'] ?? '';
    $passwordConfirmar = $_POST['passwordConfirmar'] ?? '';

    $datos = ['nombre' => $nombre, 'cargo' => $cargo, 'usuario' => $usuarioLogin];

    // Cambiar la contraseña es lo único que exige más que "estar logueado":
    // sin esto, cualquiera que se encuentre la sesión abierta (ej. la
    // computadora del comité desatendida) podría tomar la cuenta con solo
    // escribir una contraseña nueva.
    if ($password !== '') {
        if (!passwordEsValida($password)) {
            $error = 'La nueva contraseña debe tener al menos ' . MIN_LARGO_PASSWORD . ' caracteres.';
        } elseif ($password !== $passwordConfirmar) {
            $error = 'La confirmación no coincide con la nueva contraseña.';
        } else {
            $cuentaActual = getUsuarioPorId($id);
            if ($contrasenaActual === '' || !password_verify($contrasenaActual, $cuentaActual['password_hash'])) {
                $error = 'Tu contraseña actual no es correcta.';
            }
        }
    }

    if ($error === null) {
        try {
            $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;
            actualizarUsuario($id, 'mesa', $nombre, $cargo, $usuarioLogin, $passwordHash);

            // Refrescar la sesión para que el header muestre los datos nuevos de inmediato.
            $_SESSION['usuario'] = ['id' => $id, 'tipo' => 'mesa', 'nombre' => $nombre, 'cargo' => $cargo];
            header('Location: /panel/mi-cuenta?ok=1');
            exit;
        } catch (PDOException $e) {
            $error = stripos($e->getMessage(), 'Duplicate entry') !== false
                ? 'Ese nombre de usuario ya existe. Elige otro.'
                : 'No se pudo guardar el cambio. Revisa los datos.';
        }
    }
} else {
    $guardado = ($_GET['ok'] ?? '') === '1';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <span class="eyebrow">Mi cuenta</span>
      <h1><?= htmlspecialchars($datos['nombre']) ?></h1>
      <p class="page-banner-lead">Edita tu nombre, cargo, usuario o contraseña.</p>
    </div>
  </section>

  <section class="detail-sections">
    <div class="form-card" data-reveal style="max-width: 480px; margin: 0 auto;">
      <?php if ($guardado): ?>
        <p class="login-error" style="background: #f1f8f3; color: #2f6b45;">Tus datos se guardaron correctamente.</p>
      <?php endif; ?>
      <?php if ($error): ?>
        <p class="login-error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form action="/panel/mi-cuenta" method="POST" class="contact-form">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />

        <label>
          Nombre completo
          <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required />
        </label>

        <label>
          Cargo
          <input type="text" name="cargo" value="<?= htmlspecialchars($datos['cargo'] ?? '') ?>" required />
        </label>

        <label>
          Usuario (login)
          <input type="text" name="usuario" value="<?= htmlspecialchars($datos['usuario']) ?>" required />
        </label>

        <hr class="form-separador" />
        <p class="form-nota">Cambiar contraseña (opcional — deja estos 3 campos en blanco para no tocarla)</p>

        <label>
          Contraseña actual
          <input type="password" name="contrasenaActual" autocomplete="current-password" />
        </label>

        <label>
          Nueva contraseña (mínimo 8 caracteres)
          <input type="password" name="password" minlength="8" autocomplete="new-password" />
        </label>

        <label>
          Confirmar nueva contraseña
          <input type="password" name="passwordConfirmar" minlength="8" autocomplete="new-password" />
        </label>

        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </form>
    </div>
  </section>

  <?php include __DIR__ . '/../partials/footer.php'; ?>

</body>
</html>
