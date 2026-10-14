<?php
require_once __DIR__ . '/../app/bootstrap.php';

$title = 'Ingresar — Villas del Palmar';
$description = 'Portal privado de propietarios y comité administrativo de Villas del Palmar.';

if ($usuario) {
    header('Location: /panel');
    exit;
}

$error = false;

// Límite de intentos por IP (no por cuenta): 3 fallos seguidos bloquean
// esta IP por 1 minuto, sin importar qué "usuario" se haya escrito en cada
// intento. $segundosBloqueado se calcula SIEMPRE, no solo al enviar el
// formulario — así recargar la página o cerrar y volver a abrir el
// navegador no resetea nada, porque lo que se recuerda es la IP, no la
// sesión ni el formulario.
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$segundosBloqueado = segundosBloqueadoLogin($ip);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf();

    if ($segundosBloqueado === 0) {
        $usuarioInput = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $cuenta = $usuarioInput !== '' ? getUsuarioPorLogin($usuarioInput) : null;

        if ($cuenta && password_verify($password, $cuenta['password_hash'])) {
            limpiarIntentosLogin($ip);
            // Solo lo necesario en sesión — nunca el password_hash.
            $_SESSION['usuario'] = [
                'id' => $cuenta['id'],
                'tipo' => $cuenta['tipo'],
                'nombre' => $cuenta['nombre'],
                'cargo' => $cuenta['cargo'],
                'villa' => $cuenta['villa'],
            ];
            header('Location: /panel');
            exit;
        }

        registrarIntentoFallidoLogin($ip);
        $_SESSION['login_error'] = true;
    }

    // Post/Redirect/Get: sin esto, recargar la página (F5) después de un
    // intento reenvía el mismo POST con la contraseña otra vez — y encima
    // contaba como un intento fallido extra para el límite de arriba, solo
    // por recargar. El estatus de "bloqueado" no necesita nada especial
    // para sobrevivir este redirect (se vuelve a calcular solo, ya que vive
    // en la base de datos); el de "usuario o contraseña incorrectos" sí,
    // por eso viaja una sola vez en sesión.
    header('Location: /login');
    exit;
}

if (!empty($_SESSION['login_error'])) {
    $error = true;
    unset($_SESSION['login_error']);
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

        <?php if ($segundosBloqueado > 0): ?>
          <p class="login-error" data-login-bloqueo data-segundos="<?= (int) $segundosBloqueado ?>">
            Demasiados intentos fallidos. Espera <span data-segundos-restantes><?= (int) $segundosBloqueado ?></span> segundo<span data-segundos-plural><?= $segundosBloqueado === 1 ? '' : 's' ?></span> antes de volver a intentar.
          </p>
        <?php elseif ($error): ?>
          <p class="login-error">Usuario o contraseña incorrectos.</p>
        <?php endif; ?>

        <form action="/login" method="POST" class="login-form">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
          <label>
            Usuario
            <input type="text" name="usuario" placeholder="ej. propietarios" required autofocus <?= $segundosBloqueado > 0 ? 'disabled' : '' ?> />
          </label>
          <label>
            Contraseña
            <span class="login-password-wrap">
              <input type="password" name="password" id="login-password" placeholder="Tu contraseña" required <?= $segundosBloqueado > 0 ? 'disabled' : '' ?> />
              <button type="button" class="login-password-toggle" id="login-password-toggle" aria-label="Mostrar contraseña">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </span>
          </label>
          <button type="submit" class="btn btn-primary login-submit" <?= $segundosBloqueado > 0 ? 'disabled' : '' ?>>Ingresar al portal</button>
        </form>
      </div>
    </div>

  </div>

  <script src="/js/main.js?v=<?= (int) @filemtime(__DIR__ . '/js/main.js') ?>"></script>

</body>
</html>
