<?php
// Llena la base de datos con cuentas y publicaciones DE PRUEBA, para poder
// navegar el portal completo antes de tener datos reales.
// Se puede correr varias veces: limpia las tablas antes de volver a insertar.
//
// Uso (por SSH, desde la raíz del proyecto en el servidor): php scripts/seed.php
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/repos/usuarios.php';
require_once __DIR__ . '/../app/repos/publicaciones.php';
require_once __DIR__ . '/../app/repos/archivos.php';

const PASSWORD_PROPIETARIOS = 'villas2026';
const PASSWORD_MESA = 'mesa2026';

function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT);
}

db()->exec('DELETE FROM archivos');
db()->exec('DELETE FROM publicaciones');
db()->exec('DELETE FROM usuarios');

// ===== Cuenta compartida de propietarios (una sola para todo el condominio) =====
crearUsuario('propietario', 'Propietarios', null, 'propietarios', hash_password(PASSWORD_PROPIETARIOS));

// ===== Mesa directiva (una cuenta por cargo) =====
$mesa = [
    'presidente' => crearUsuario('mesa', 'Ana Delgado', 'Presidente', 'presidente', hash_password(PASSWORD_MESA)),
    'tesorero' => crearUsuario('mesa', 'Roberto Salinas', 'Tesorero', 'tesorero', hash_password(PASSWORD_MESA)),
    'secretario' => crearUsuario('mesa', 'Patricia Núñez', 'Secretario', 'secretario', hash_password(PASSWORD_MESA)),
    'vocal' => crearUsuario('mesa', 'Luis Fernández', 'Vocal', 'vocal', hash_password(PASSWORD_MESA)),
];

// ===== Publicaciones de ejemplo =====
crearPublicacion(
    $mesa['tesorero'],
    'financiero',
    'Estado financiero — julio 2026',
    "Ingresos por cuotas de mantenimiento: \$58,400.\nEgresos: nómina de vigilancia y jardinería, mantenimiento de albercas, luz de áreas comunes.\nSaldo en cuenta al cierre de julio: \$132,900.",
    '2026-08-05'
);
crearPublicacion(
    $mesa['tesorero'],
    'financiero',
    'Estado financiero — junio 2026',
    "Ingresos por cuotas de mantenimiento: \$56,000.\nEgresos: nómina, mantenimiento general y reposición de equipo de bombeo de la alberca infinita.\nSaldo en cuenta al cierre de junio: \$124,300.",
    '2026-07-05'
);

crearPublicacion(
    $mesa['presidente'],
    'mejora',
    'Repintado de fachadas — en progreso',
    "Ya inició el repintado de las fachadas de los edificios más antiguos.\nSe espera terminar la primera etapa a finales de septiembre.\nDisculpen las molestias con el andamiaje.",
    '2026-08-10'
);
crearPublicacion(
    $mesa['vocal'],
    'mejora',
    'Mantenimiento a la alberca con tobogán',
    "Se dio mantenimiento al sistema de filtrado de la alberca con tobogán.\nVuelve a estar disponible con normalidad a partir de esta semana.",
    '2026-08-01'
);

crearPublicacion(
    $mesa['tesorero'],
    'aviso',
    'Recordatorio: cuota de mantenimiento de agosto',
    "Recordamos a los propietarios con adeudo pendiente que la cuota de agosto ya está disponible para pago.\nCualquier duda sobre su estado de cuenta, contactar a tesorería.",
    '2026-08-12'
);
crearPublicacion(
    $mesa['secretario'],
    'aviso',
    'Nuevo horario de la caseta de vigilancia',
    "A partir del 15 de agosto, la caseta de vigilancia atenderá de forma continua las 24 horas (antes tenía un cambio de turno con ventana sin cobertura).\nSin cambios en los accesos.",
    '2026-08-13'
);

echo "Base de datos poblada con datos de prueba.\n\n";
echo "=== Cuenta de propietarios (compartida, contraseña: " . PASSWORD_PROPIETARIOS . ") ===\n";
echo "  propietarios\n";
echo "\n=== Cuentas de mesa directiva (contraseña: " . PASSWORD_MESA . ") ===\n";
echo "  presidente — Ana Delgado\n";
echo "  tesorero — Roberto Salinas\n";
echo "  secretario — Patricia Núñez\n";
echo "  vocal — Luis Fernández\n";
