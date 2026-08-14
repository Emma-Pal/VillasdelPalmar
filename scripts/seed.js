// Llena la base de datos con propietarios, mesa directiva, pagos y publicaciones
// DE PRUEBA, para poder navegar el portal completo antes de tener datos reales.
// Se puede correr varias veces: limpia las tablas antes de volver a insertar.
//
// Uso: pnpm run seed
const bcrypt = require('bcryptjs');
const { db, crearUsuario, crearMovimiento, crearPublicacion } = require('../db');

const PASSWORD_PROPIETARIOS = 'villas2026';
const PASSWORD_MESA = 'mesa2026';

function hash(password) {
  return bcrypt.hashSync(password, 10);
}

db.exec('DELETE FROM publicaciones; DELETE FROM movimientos; DELETE FROM usuarios;');

// ===== Mesa directiva =====
const mesa = {
  presidente: crearUsuario({
    tipo: 'mesa',
    nombre: 'Ana Delgado',
    cargo: 'Presidente',
    usuario: 'presidente',
    passwordHash: hash(PASSWORD_MESA),
  }),
  tesorero: crearUsuario({
    tipo: 'mesa',
    nombre: 'Roberto Salinas',
    cargo: 'Tesorero',
    usuario: 'tesorero',
    passwordHash: hash(PASSWORD_MESA),
  }),
  secretario: crearUsuario({
    tipo: 'mesa',
    nombre: 'Patricia Núñez',
    cargo: 'Secretario',
    usuario: 'secretario',
    passwordHash: hash(PASSWORD_MESA),
  }),
  vocal: crearUsuario({
    tipo: 'mesa',
    nombre: 'Luis Fernández',
    cargo: 'Vocal',
    usuario: 'vocal',
    passwordHash: hash(PASSWORD_MESA),
  }),
};

// ===== Propietarios =====
const propietarios = {
  depto1: crearUsuario({
    tipo: 'propietario',
    nombre: 'Laura Méndez',
    unidad: 'Depto 1',
    usuario: 'depto1',
    passwordHash: hash(PASSWORD_PROPIETARIOS),
  }),
  depto2: crearUsuario({
    tipo: 'propietario',
    nombre: 'Jorge Ramírez',
    unidad: 'Depto 2',
    usuario: 'depto2',
    passwordHash: hash(PASSWORD_PROPIETARIOS),
  }),
  depto3: crearUsuario({
    tipo: 'propietario',
    nombre: 'Sofía Torres',
    unidad: 'Depto 3',
    usuario: 'depto3',
    passwordHash: hash(PASSWORD_PROPIETARIOS),
  }),
  depto4: crearUsuario({
    tipo: 'propietario',
    nombre: 'Miguel Ángel Ruiz',
    unidad: 'Depto 4',
    usuario: 'depto4',
    passwordHash: hash(PASSWORD_PROPIETARIOS),
  }),
};

// ===== Movimientos: cuota mensual de $800 en jun/jul/ago 2026, con distintos avances de pago =====
const MESES = [
  { fecha: '2026-06-01', concepto: 'Cuota de mantenimiento — junio 2026' },
  { fecha: '2026-07-01', concepto: 'Cuota de mantenimiento — julio 2026' },
  { fecha: '2026-08-01', concepto: 'Cuota de mantenimiento — agosto 2026' },
];
const CUOTA = 800;

function cargarCuotasYPagos(propietarioId, mesesPagados) {
  MESES.forEach((mes, i) => {
    crearMovimiento({ propietarioId, fecha: mes.fecha, tipo: 'cargo', concepto: mes.concepto, monto: CUOTA });
    if (i < mesesPagados) {
      crearMovimiento({
        propietarioId,
        fecha: mes.fecha,
        tipo: 'pago',
        concepto: `Pago recibido — ${mes.concepto}`,
        monto: CUOTA,
      });
    }
  });
}

cargarCuotasYPagos(propietarios.depto1, 2); // debe agosto ($800)
cargarCuotasYPagos(propietarios.depto2, 0); // moroso, debe los 3 meses ($2400)
cargarCuotasYPagos(propietarios.depto3, 3); // al corriente ($0)
cargarCuotasYPagos(propietarios.depto4, 1); // debe jul/ago ($1600)

// ===== Publicaciones de ejemplo =====
crearPublicacion({
  autorId: mesa.tesorero,
  categoria: 'financiero',
  titulo: 'Estado financiero — julio 2026',
  cuerpo:
    'Ingresos por cuotas de mantenimiento: $58,400. Egresos: nómina de vigilancia y jardinería, mantenimiento de albercas, luz de áreas comunes. Saldo en cuenta al cierre de julio: $132,900.',
  fecha: '2026-08-05',
});
crearPublicacion({
  autorId: mesa.tesorero,
  categoria: 'financiero',
  titulo: 'Estado financiero — junio 2026',
  cuerpo:
    'Ingresos por cuotas de mantenimiento: $56,000. Egresos: nómina, mantenimiento general y reposición de equipo de bombeo de la alberca infinita. Saldo en cuenta al cierre de junio: $124,300.',
  fecha: '2026-07-05',
});

crearPublicacion({
  autorId: mesa.presidente,
  categoria: 'mejora',
  titulo: 'Repintado de fachadas — en progreso',
  cuerpo:
    'Ya inició el repintado de las fachadas de los edificios más antiguos. Se espera terminar la primera etapa a finales de septiembre. Disculpen las molestias con el andamiaje.',
  fecha: '2026-08-10',
});
crearPublicacion({
  autorId: mesa.vocal,
  categoria: 'mejora',
  titulo: 'Mantenimiento a la alberca con tobogán',
  cuerpo:
    'Se dio mantenimiento al sistema de filtrado de la alberca con tobogán. Vuelve a estar disponible con normalidad a partir de esta semana.',
  fecha: '2026-08-01',
});

crearPublicacion({
  autorId: mesa.tesorero,
  categoria: 'aviso',
  titulo: 'Recordatorio: cuota de mantenimiento de agosto',
  cuerpo:
    'Recordamos a los propietarios con adeudo pendiente que la cuota de agosto ya está disponible para pago. Cualquier duda sobre su estado de cuenta, contactar a tesorería.',
  fecha: '2026-08-12',
});
crearPublicacion({
  autorId: mesa.secretario,
  categoria: 'aviso',
  titulo: 'Nuevo horario de la caseta de vigilancia',
  cuerpo:
    'A partir del 15 de agosto, la caseta de vigilancia atenderá de forma continua las 24 horas (antes tenía un cambio de turno con ventana sin cobertura). Sin cambios en los accesos.',
  fecha: '2026-08-13',
});

console.log('Base de datos poblada con datos de prueba.\n');
console.log('=== Cuentas de propietarios (contraseña: %s) ===', PASSWORD_PROPIETARIOS);
console.log('  depto1 — Laura Méndez (debe $800)');
console.log('  depto2 — Jorge Ramírez (debe $2400)');
console.log('  depto3 — Sofía Torres (al corriente)');
console.log('  depto4 — Miguel Ángel Ruiz (debe $1600)');
console.log('\n=== Cuentas de mesa directiva (contraseña: %s) ===', PASSWORD_MESA);
console.log('  presidente — Ana Delgado');
console.log('  tesorero — Roberto Salinas');
console.log('  secretario — Patricia Núñez');
console.log('  vocal — Luis Fernández');
