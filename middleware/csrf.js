// Protección CSRF casera (patrón "token sincronizador"), sin depender de un
// paquete externo: se genera un token por sesión, se manda como campo oculto
// en cada formulario, y se verifica en cada POST que coincida con el de la sesión.
const crypto = require('crypto');

// Se asegura de que la sesión tenga un token, y lo deja disponible en las
// vistas como `csrfToken` para meterlo en los formularios.
function attachCsrfToken(req, res, next) {
  if (!req.session.csrfToken) {
    req.session.csrfToken = crypto.randomBytes(24).toString('hex');
  }
  res.locals.csrfToken = req.session.csrfToken;
  next();
}

// Bloquea cualquier POST cuyo campo _csrf no coincida con el de la sesión
// (típico de un formulario falso montado en otro sitio).
function verifyCsrfToken(req, res, next) {
  const tokenEnviado = req.body && req.body._csrf;
  if (!tokenEnviado || tokenEnviado !== req.session.csrfToken) {
    return res.status(403).render('error', {
      title: 'Solicitud rechazada — Villas del Palmar',
      description: 'Token de seguridad inválido.',
      mensaje: 'Tu sesión o el formulario expiraron. Regresa e inténtalo de nuevo.',
    });
  }
  next();
}

module.exports = { attachCsrfToken, verifyCsrfToken };
