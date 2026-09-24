<?php
require_once __DIR__ . '/../app/bootstrap.php';

$title = 'Ingresar — Villas del Palmar';
$description = 'Portal privado de propietarios y comité administrativo de Villas del Palmar.';

if ($usuario) {
    header('Location: /panel');
    exit;
}

$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    $usuarioInput = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $cuenta = $usuarioInput !== '' ? getUsuarioPorLogin($usuarioInput) : null;

    if ($cuenta && password_verify($password, $cuenta['password_hash'])) {
        // Solo lo necesario en sesión — nunca el password_hash.
        $_SESSION['usuario'] = [
            'id' => $cuenta['id'],
            'tipo' => $cuenta['tipo'],
            'nombre' => $cuenta['nombre'],
            'cargo' => $cuenta['cargo'],
        ];
        header('Location: /panel');
        exit;
    }

    $error = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/partials/head.php'; ?>
</head>
<body>

  <div class="login-split">

    <!-- Panel izquierdo: identidad de la marca (sin datos sensibles) -->
    <div class="login-side-dark">
      <div class="login-mark">
        <span class="login-mark-icon" aria-hidden="true"></span>
        <span class="login-mark-text">Portal privado</span>
      </div>

      <div class="login-side-main">
        <span class="eyebrow">Acceso al portal</span>
        <h1 class="login-side-title">Villas<br /><em>del Palmar</em></h1>
        <span class="login-side-divider" aria-hidden="true"></span>
        <p class="login-side-lead">Portal privado de propietarios y comité administrativo. Ingresa con tu usuario y contraseña.</p>
      </div>

      <p class="login-side-foot">Villas del Palmar · Manzanillo</p>
    </div>

    <!-- Panel derecho: el formulario -->
    <div class="login-side-light">
      <div class="login-form-wrap">
        <h2 class="login-title">Iniciar sesión</h2>
        <p class="login-sub">Ingresa tus datos para continuar</p>

        <?php if ($error): ?>
          <p class="login-error">Usuario o contraseña incorrectos.</p>
        <?php endif; ?>

        <form action="/login" method="POST" class="login-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <label>
            Usuario
            <input type="text" name="usuario" placeholder="ej. propietarios" required autofocus />
          </label>
          <label>
            Contraseña
            <span class="login-password-wrap">
              <input type="password" name="password" id="login-password" placeholder="Tu contraseña" required />
              <button type="button" class="login-password-toggle" id="login-password-toggle" aria-label="Mostrar contraseña">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </span>
          </label>
          <button type="submit" class="btn btn-primary login-submit">Ingresar al portal</button>
        </form>
      </div>
    </div>

  </div>

</body>
</html>
