<?php
require_once __DIR__ . '/../app/bootstrap.php';

$title = 'Ingresar — Villas del Palmar';
$description = 'Portal privado de propietarios y mesa directiva de Villas del Palmar.';

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

  <div class="login-screen">
    <div class="login-card">
      <div class="login-logo">
        <span class="logo">VP</span>
        <span class="brand-name">Villas del Palmar</span>
      </div>
      <p class="login-sub">Portal de propietarios y mesa directiva</p>

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
          <input type="password" name="password" placeholder="Tu contraseña" required />
        </label>
        <button type="submit" class="btn btn-primary login-submit">Entrar</button>
      </form>
    </div>
  </div>

</body>
</html>
