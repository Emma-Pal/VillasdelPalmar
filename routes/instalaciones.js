const express = require('express');
const { requireAuth } = require('../middleware/auth');

const router = express.Router();

const PAGINAS = {
  alberca: { titulo: 'Alberca & terraza', desc: 'Las tres albercas de Villas del Palmar.' },
  'areas-verdes': { titulo: 'Áreas verdes', desc: 'Jardines y espacios abiertos de Villas del Palmar.' },
  departamentos: { titulo: 'Departamentos', desc: 'Fachadas y terrazas de los departamentos.' },
};

router.get('/panel/instalaciones', requireAuth, (req, res) => {
  res.render('instalaciones/index', {
    title: 'Instalaciones — Villas del Palmar',
    description: 'Referencia de instalaciones para residentes de Villas del Palmar.',
  });
});

router.get('/panel/instalaciones/:pagina', requireAuth, (req, res, next) => {
  const info = PAGINAS[req.params.pagina];
  if (!info) return next(); // deja que caiga al 404 general

  res.render(`instalaciones/${req.params.pagina}`, {
    title: `${info.titulo} — Villas del Palmar`,
    description: info.desc,
  });
});

module.exports = router;
