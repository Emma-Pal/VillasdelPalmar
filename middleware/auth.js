// Middlewares de acceso al portal. La sesión guarda solo el usuario completo
// (sin password_hash) en req.session.user tras un login exitoso.

function requireAuth(req, res, next) {
  if (!req.session.user) {
    return res.redirect('/login');
  }
  next();
}

function requireMesa(req, res, next) {
  if (!req.session.user) {
    return res.redirect('/login');
  }
  if (req.session.user.tipo !== 'mesa') {
    return res.status(403).render('error', {
      title: 'Acceso no permitido — Villas del Palmar',
      description: 'Esta sección es solo para la mesa directiva.',
      mensaje: 'Esta sección es solo para la mesa directiva.',
    });
  }
  next();
}

// Hace disponible el usuario en todas las vistas como `usuario`, sin tener
// que pasarlo manualmente en cada res.render.
function exponerUsuario(req, res, next) {
  res.locals.usuario = req.session.user || null;
  next();
}

module.exports = { requireAuth, requireMesa, exponerUsuario };
