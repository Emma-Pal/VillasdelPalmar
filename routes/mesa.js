const express = require('express');
const { requireAuth } = require('../middleware/auth');
const { getMesa } = require('../db');

const router = express.Router();

router.get('/panel/mesa', requireAuth, (req, res) => {
  res.render('mesa', {
    title: 'Mesa directiva — Villas del Palmar',
    description: 'Integrantes de la mesa directiva de Villas del Palmar.',
    mesa: getMesa(),
  });
});

module.exports = router;
