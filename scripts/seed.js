// Llena la base de datos con cuentas y publicaciones DE PRUEBA, para poder
// navegar el portal completo antes de tener datos reales.
// Se puede correr varias veces: limpia las tablas antes de volver a insertar.
//
// Uso: pnpm run seed
const bcrypt = require('bcryptjs');
const { db, crearUsuario, crearPublicacion } = require('../db');

const PASSWORD_PROPIETARIOS = 'villas2026';
const PASSWORD_MESA = 'mesa2026';

function hash(password) {
  return bcrypt.hashSync(password, 10);
}

db.exec('DELETE FROM archivos; DELETE FROM publicaciones; DELETE FROM usuarios;');

// ===== Cuenta compartida de propietarios (una sola para todo el condominio) =====
crearUsuario({
  tipo: 'propietario',
  nombre: 'Propietarios',
  usuario: 'propietarios',
  passwordHash: hash(PASSWORD_PROPIETARIOS),
});

// ===== Mesa directiva (una cuenta por cargo) =====
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

// ===== Publicaciones de ejemplo =====
crearPublicacion({
  autorId: mesa.tesorero,
  categoria: 'financiero',
  titulo: 'Estado financiero — julio 2026',
  cuerpo:
    'Ingresos por cuotas de mantenimiento: $58,400.\nEgresos: nómina de vigilancia y jardinería, mantenimiento de albercas, luz de áreas comunes.\nSaldo en cuenta al cierre de julio: $132,900.',
  fecha: '2026-08-05',
});
crearPublicacion({
  autorId: mesa.tesorero,
  categoria: 'financiero',
  titulo: 'Estado financiero — junio 2026',
  cuerpo:
    'Ingresos por cuotas de mantenimiento: $56,000.\nEgresos: nómina, mantenimiento general y reposición de equipo de bombeo de la alberca infinita.\nSaldo en cuenta al cierre de junio: $124,300.',
  fecha: '2026-07-05',
});

crearPublicacion({
  autorId: mesa.presidente,
  categoria: 'mejora',
  titulo: 'Repintado de fachadas — en progreso',
  cuerpo:
    'Ya inició el repintado de las fachadas de los edificios más antiguos.\nSe espera terminar la primera etapa a finales de septiembre.\nDisculpen las molestias con el andamiaje.',
  fecha: '2026-08-10',
});
crearPublicacion({
  autorId: mesa.vocal,
  categoria: 'mejora',
  titulo: 'Mantenimiento a la alberca con tobogán',
  cuerpo:
    'Se dio mantenimiento al sistema de filtrado de la alberca con tobogán.\nVuelve a estar disponible con normalidad a partir de esta semana.',
  fecha: '2026-08-01',
});

crearPublicacion({
  autorId: mesa.tesorero,
  categoria: 'aviso',
  titulo: 'Recordatorio: cuota de mantenimiento de agosto',
  cuerpo:
    'Recordamos a los propietarios con adeudo pendiente que la cuota de agosto ya está disponible para pago.\nCualquier duda sobre su estado de cuenta, contactar a tesorería.',
  fecha: '2026-08-12',
});
crearPublicacion({
  autorId: mesa.secretario,
  categoria: 'aviso',
  titulo: 'Nuevo horario de la caseta de vigilancia',
  cuerpo:
    'A partir del 15 de agosto, la caseta de vigilancia atenderá de forma continua las 24 horas (antes tenía un cambio de turno con ventana sin cobertura).\nSin cambios en los accesos.',
  fecha: '2026-08-13',
});

console.log('Base de datos poblada con datos de prueba.\n');
console.log('=== Cuenta de propietarios (compartida, contraseña: %s) ===', PASSWORD_PROPIETARIOS);
console.log('  propietarios');
console.log('\n=== Cuentas de mesa directiva (contraseña: %s) ===', PASSWORD_MESA);
console.log('  presidente — Ana Delgado');
console.log('  tesorero — Roberto Salinas');
console.log('  secretario — Patricia Núñez');
console.log('  vocal — Luis Fernández');
