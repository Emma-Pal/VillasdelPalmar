const express = require('express');
const { requireAuth } = require('../middleware/auth');
const { getPublicaciones, contarPublicaciones } = require('../db');

const router = express.Router();

router.get('/panel', requireAuth, (req, res) => {
  res.render('panel', {
    title: 'Panel — Villas del Palmar',
    description: 'Panel de Villas del Palmar.',
    ultimasPublicaciones: getPublicaciones(null, { limit: 3, offset: 0 }),
    totalPublicaciones: contarPublicaciones(),
  });
});

module.exports = router;
