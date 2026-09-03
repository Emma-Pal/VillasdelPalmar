<!-- ===== Header del portal =====
     Una sola fila que usa todo el ancho de la pantalla (sin el max-width de
     1140px que usan las secciones de contenido) — marca a la izquierda, nav
     centrado, usuario/salir a la derecha. Fondo verde oscuro de la marca. -->
<header class="site-header scrolled portal-header" id="header">
  <div class="nav-wrap">
    <a href="/panel" class="brand">
      <span class="logo">VP</span>
      <span class="brand-name">Villas del Palmar</span>
    </a>

    <nav class="portal-nav" id="nav-menu">
      <a href="/panel" class="<?= rutaActivaExacta('/panel', $currentPath) ?>">Panel</a>
      <a href="/panel/avisos" class="<?= rutaActivaPrefijo('/panel/avisos', $currentPath) ?>">
        Avisos
        <?php if ($avisosNuevos > 0): ?><span class="nav-badge"><?= (int) $avisosNuevos ?></span><?php endif; ?>
      </a>
      <a href="/panel/instalaciones" class="<?= rutaActivaPrefijo('/panel/instalaciones', $currentPath) ?>">Instalaciones</a>
      <a href="/panel/mesa" class="<?= rutaActivaExacta('/panel/mesa', $currentPath) ?>">Comité</a>
      <?php if ($usuario['tipo'] === 'mesa'): ?>
        <a href="/panel/usuarios" class="<?= rutaActivaPrefijo('/panel/usuarios', $currentPath) ?>">Usuarios</a>
      <?php endif; ?>
    </nav>

    <div class="portal-header-actions">
      <?php if ($usuario['tipo'] === 'mesa'): ?>
        <a href="/panel/mi-cuenta" class="nav-user" title="Editar mi cuenta">
          <span class="nav-user-avatar"><?= htmlspecialchars(mb_substr($usuario['nombre'], 0, 1)) ?></span>
          <span class="nav-user-text">
            <span class="nav-user-nombre"><?= htmlspecialchars($usuario['nombre']) ?></span>
            <span class="nav-user-rol"><?= htmlspecialchars($usuario['cargo']) ?></span>
          </span>
        </a>
      <?php else: ?>
        <span class="nav-user">
          <span class="nav-user-avatar"><?= htmlspecialchars(mb_substr($usuario['nombre'], 0, 1)) ?></span>
          <span class="nav-user-text">
            <span class="nav-user-nombre"><?= htmlspecialchars($usuario['nombre']) ?></span>
            <span class="nav-user-rol">Propietario</span>
          </span>
        </span>
      <?php endif; ?>
      <form action="/logout" method="POST" class="nav-logout-form">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>" />
        <button type="submit" class="nav-logout-btn">Cerrar sesión</button>
      </form>
      <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
