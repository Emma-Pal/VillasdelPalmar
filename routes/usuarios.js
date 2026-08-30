const express = require('express');
const bcrypt = require('bcryptjs');
const { requireMesa } = require('../middleware/auth');
const { verifyCsrfToken } = require('../middleware/csrf');
const { getUsuarios, getUsuarioPorId, crearUsuario, actualizarUsuario, eliminarUsuario } = require('../db');
const { MIN_LARGO_PASSWORD, passwordEsValida } = require('../lib/validacion');

const router = express.Router();

function renderForm(res, { usuario = null, error = null } = {}) {
  res.render('usuario-form', {
    title: (usuario ? 'Editar usuario' : 'Nuevo usuario') + ' — Villas del Palmar',
    description: 'Administración de cuentas de Villas del Palmar.',
    usuarioEditado: usuario,
    error,
  });
}

router.get('/panel/usuarios', requireMesa, (req, res) => {
  res.render('usuarios', {
    title: 'Usuarios — Villas del Palmar',
    description: 'Administración de cuentas de propietarios y mesa directiva.',
    usuarios: getUsuarios(),
  });
});

router.get('/panel/usuarios/nuevo', requireMesa, (req, res) => renderForm(res));

router.post('/panel/usuarios/nuevo', requireMesa, verifyCsrfToken, (req, res) => {
  const { tipo, nombre, cargo, usuario, password, passwordConfirmar } = req.body;

  if (!passwordEsValida(password)) {
    return renderForm(res, { error: `La contraseña es obligatoria y debe tener al menos ${MIN_LARGO_PASSWORD} caracteres.` });
  }
  if (password !== passwordConfirmar) {
    return renderForm(res, { error: 'La confirmación no coincide con la contraseña.' });
  }

  try {
    crearUsuario({
      tipo,
      nombre,
      cargo,
      usuario,
      passwordHash: bcrypt.hashSync(password, 10),
    });
    res.redirect('/panel/usuarios');
  } catch (err) {
    const mensaje = String(err.message).includes('UNIQUE')
      ? 'Ese nombre de usuario ya existe. Elige otro.'
      : 'No se pudo crear la cuenta. Revisa los datos.';
    renderForm(res, { error: mensaje });
  }
});

router.get('/panel/usuarios/:id/editar', requireMesa, (req, res) => {
  const usuario = getUsuarioPorId(req.params.id);
  if (!usuario) return res.redirect('/panel/usuarios');
  renderForm(res, { usuario });
});

router.post('/panel/usuarios/:id/editar', requireMesa, verifyCsrfToken, (req, res) => {
  const id = Number(req.params.id);
  const { tipo, nombre, cargo, usuario, password, passwordConfirmar } = req.body;

  // La contraseña es opcional al editar (dejarla en blanco = no se toca),
  // pero si se manda algo, debe cumplir el mínimo y estar bien confirmada.
  if (password) {
    if (!passwordEsValida(password)) {
      return renderForm(res, {
        usuario: getUsuarioPorId(id),
        error: `La nueva contraseña debe tener al menos ${MIN_LARGO_PASSWORD} caracteres.`,
      });
    }
    if (password !== passwordConfirmar) {
      return renderForm(res, { usuario: getUsuarioPorId(id), error: 'La confirmación no coincide con la nueva contraseña.' });
    }
  }

  try {
    actualizarUsuario({
      id,
      tipo,
      nombre,
      cargo,
      usuario,
      passwordHash: password ? bcrypt.hashSync(password, 10) : null,
    });

    // Si el usuario se edita a sí mismo (ej. cambia su propio nombre/usuario),
    // se refresca la sesión para que el header muestre los datos correctos.
    if (req.session.user.id === id) {
      req.session.user = { id, tipo, nombre, cargo: tipo === 'mesa' ? cargo || null : null };
    }

    res.redirect('/panel/usuarios');
  } catch (err) {
    const mensaje = String(err.message).includes('UNIQUE')
      ? 'Ese nombre de usuario ya existe. Elige otro.'
      : 'No se pudo guardar el cambio. Revisa los datos.';
    renderForm(res, { usuario: getUsuarioPorId(id), error: mensaje });
  }
});

router.post('/panel/usuarios/:id/eliminar', requireMesa, verifyCsrfToken, (req, res) => {
  const id = Number(req.params.id);

  if (id === req.session.user.id) {
    return res.status(400).render('error', {
      title: 'Acción no permitida — Villas del Palmar',
      description: 'No puedes eliminar tu propia cuenta.',
      mensaje: 'No puedes eliminar tu propia cuenta mientras la tienes abierta.',
    });
  }

  try {
    eliminarUsuario(id);
  } catch (err) {
    // FK constraint: el usuario tiene publicaciones (autor_id) y no se cascadea a propósito.
    return res.status(400).render('error', {
      title: 'No se puede eliminar — Villas del Palmar',
      description: 'Este usuario tiene contenido asociado.',
      mensaje:
        'No se puede eliminar: este usuario tiene publicaciones a su nombre. Elimina esas publicaciones primero si de verdad quieres borrar la cuenta.',
    });
  }

  res.redirect('/panel/usuarios');
});

module.exports = router;
