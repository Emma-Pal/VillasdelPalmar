const express = require('express');
const { requireAuth, requireMesa } = require('../middleware/auth');
const {
  getPropietarios,
  getUsuarioPorId,
  getSaldo,
  getMovimientos,
  crearMovimiento,
} = require('../db');

const router = express.Router();

// Lista general: la mesa ve a todos los propietarios; un propietario ve
// directo su propio detalle (no tiene sentido mostrarle una "lista" de un solo elemento).
router.get('/panel/pagos', requireAuth, (req, res) => {
  const usuario = req.session.user;
  if (usuario.tipo === 'propietario') {
    return res.redirect(`/panel/pagos/${usuario.id}`);
  }

  const propietarios = getPropietarios().map((p) => ({ ...p, saldo: getSaldo(p.id) }));
  res.render('pagos', {
    title: 'Pagos — Villas del Palmar',
    description: 'Estado de cuenta de los propietarios de Villas del Palmar.',
    propietarios,
  });
});

function puedeVerDetalle(usuario, propietarioId) {
  return usuario.tipo === 'mesa' || usuario.id === propietarioId;
}

router.get('/panel/pagos/:id', requireAuth, (req, res) => {
  const propietarioId = Number(req.params.id);
  const usuario = req.session.user;

  if (!puedeVerDetalle(usuario, propietarioId)) {
    return res.status(403).render('error', {
      title: 'Acceso no permitido — Villas del Palmar',
      description: 'No puedes ver el estado de cuenta de otro propietario.',
      mensaje: 'No puedes ver el estado de cuenta de otro propietario.',
    });
  }

  const propietario = getUsuarioPorId(propietarioId);
  if (!propietario || propietario.tipo !== 'propietario') {
    return res.status(404).render('error', {
      title: 'No encontrado — Villas del Palmar',
      description: 'Propietario no encontrado.',
      mensaje: 'No encontramos a ese propietario.',
    });
  }

  res.render('pago-detalle', {
    title: `${propietario.nombre} — Villas del Palmar`,
    description: `Estado de cuenta de ${propietario.nombre}.`,
    propietario,
    saldo: getSaldo(propietarioId),
    movimientos: getMovimientos(propietarioId),
  });
});

router.post('/panel/pagos/:id', requireMesa, (req, res) => {
  const propietarioId = Number(req.params.id);
  const { fecha, tipo, concepto, monto } = req.body;

  crearMovimiento({
    propietarioId,
    fecha,
    tipo: tipo === 'pago' ? 'pago' : 'cargo',
    concepto,
    monto: Number(monto) || 0,
  });

  res.redirect(`/panel/pagos/${propietarioId}`);
});

module.exports = router;
