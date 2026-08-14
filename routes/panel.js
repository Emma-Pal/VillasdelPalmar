const express = require('express');
const { requireAuth } = require('../middleware/auth');
const { getSaldo, getPropietarios, getPublicaciones } = require('../db');

const router = express.Router();

router.get('/panel', requireAuth, (req, res) => {
  const usuario = req.session.user;
  const ultimosAvisos = getPublicaciones().slice(0, 3);

  if (usuario.tipo === 'propietario') {
    return res.render('panel', {
      title: 'Panel — Villas del Palmar',
      description: 'Panel del propietario en el portal de Villas del Palmar.',
      saldo: getSaldo(usuario.id),
      ultimosAvisos,
    });
  }

  const propietarios = getPropietarios().map((p) => ({ ...p, saldo: getSaldo(p.id) }));
  const resumen = {
    totalPropietarios: propietarios.length,
    morosos: propietarios.filter((p) => p.saldo > 0).length,
    totalAdeudo: propietarios.reduce((acc, p) => acc + Math.max(p.saldo, 0), 0),
  };

  res.render('panel', {
    title: 'Panel — Villas del Palmar',
    description: 'Panel de la mesa directiva en el portal de Villas del Palmar.',
    resumen,
    ultimosAvisos,
  });
});

module.exports = router;
