const express = require('express');
const bcrypt = require('bcryptjs');
const { getUsuarioPorLogin } = require('../db');
const { verifyCsrfToken } = require('../middleware/csrf');

const router = express.Router();

router.get('/login', (req, res) => {
  if (req.session.user) return res.redirect('/panel');
  res.render('login', {
    title: 'Ingresar — Villas del Palmar',
    description: 'Portal privado de propietarios y mesa directiva de Villas del Palmar.',
    error: false,
  });
});

router.post('/login', verifyCsrfToken, (req, res) => {
  const { usuario, password } = req.body;
  const cuenta = usuario ? getUsuarioPorLogin(usuario.trim()) : null;

  const credencialesValidas = cuenta && bcrypt.compareSync(password || '', cuenta.password_hash);
  if (!credencialesValidas) {
    return res.render('login', {
      title: 'Ingresar — Villas del Palmar',
      description: 'Portal privado de propietarios y mesa directiva de Villas del Palmar.',
      error: true,
    });
  }

  // Solo lo necesario en sesión — nunca el password_hash.
  req.session.user = {
    id: cuenta.id,
    tipo: cuenta.tipo,
    nombre: cuenta.nombre,
    cargo: cuenta.cargo,
  };
  res.redirect('/panel');
});

router.post('/logout', verifyCsrfToken, (req, res) => {
  req.session.destroy(() => res.redirect('/login'));
});

module.exports = router;
