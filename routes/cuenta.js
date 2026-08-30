const express = require('express');
const bcrypt = require('bcryptjs');
const { requireMesa } = require('../middleware/auth');
const { verifyCsrfToken } = require('../middleware/csrf');
const { getUsuarioPorId, actualizarUsuario } = require('../db');
const { MIN_LARGO_PASSWORD, passwordEsValida } = require('../lib/validacion');

const router = express.Router();

function renderForm(res, { datos, error }) {
  res.render('mi-cuenta', {
    title: 'Mi cuenta — Villas del Palmar',
    description: 'Editar mis datos de acceso.',
    datos,
    error,
    guardado: false,
  });
}

// Solo mesa directiva: cada quien edita su propio nombre/cargo/usuario/clave
// sin tener que pasar por la lista general de Usuarios. La cuenta compartida
// de propietarios no tiene esta opción (si hace falta cambiarle algo, se
// hace desde Usuarios, como con cualquier otra cuenta).
router.get('/panel/mi-cuenta', requireMesa, (req, res) => {
  res.render('mi-cuenta', {
    title: 'Mi cuenta — Villas del Palmar',
    description: 'Editar mis datos de acceso.',
    datos: getUsuarioPorId(req.session.user.id),
    error: null,
    guardado: req.query.ok === '1',
  });
});

router.post('/panel/mi-cuenta', requireMesa, verifyCsrfToken, (req, res) => {
  const id = req.session.user.id;
  const { nombre, cargo, usuario, contrasenaActual, password, passwordConfirmar } = req.body;
  const datosFormulario = { nombre, cargo, usuario };

  // Cambiar la contraseña es lo único que exige más que "estar logueado":
  // sin esto, cualquiera que se encuentre la sesión abierta (ej. la
  // computadora de la mesa desatendida) podría tomar la cuenta con solo
  // escribir una contraseña nueva.
  if (password) {
    if (!passwordEsValida(password)) {
      return renderForm(res, {
        datos: datosFormulario,
        error: `La nueva contraseña debe tener al menos ${MIN_LARGO_PASSWORD} caracteres.`,
      });
    }
    if (password !== passwordConfirmar) {
      return renderForm(res, { datos: datosFormulario, error: 'La confirmación no coincide con la nueva contraseña.' });
    }

    const cuentaActual = getUsuarioPorId(id);
    if (!contrasenaActual || !bcrypt.compareSync(contrasenaActual, cuentaActual.password_hash)) {
      return renderForm(res, { datos: datosFormulario, error: 'Tu contraseña actual no es correcta.' });
    }
  }

  try {
    actualizarUsuario({
      id,
      tipo: 'mesa',
      nombre,
      cargo,
      usuario,
      passwordHash: password ? bcrypt.hashSync(password, 10) : null,
    });

    // Refrescar la sesión para que el header muestre los datos nuevos de inmediato.
    req.session.user = { id, tipo: 'mesa', nombre, cargo };
    res.redirect('/panel/mi-cuenta?ok=1');
  } catch (err) {
    const mensaje = String(err.message).includes('UNIQUE')
      ? 'Ese nombre de usuario ya existe. Elige otro.'
      : 'No se pudo guardar el cambio. Revisa los datos.';
    renderForm(res, { datos: datosFormulario, error: mensaje });
  }
});

module.exports = router;
