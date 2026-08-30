// "Recuperar contraseña" de emergencia: no hay un flujo de autoservicio en la
// página (eso requeriría poder enviar correos, y no tenemos un servicio de
// correo configurado). Mientras tanto, si alguien de la mesa se queda sin
// poder entrar, esto sirve como respaldo: quien tenga acceso a esta terminal
// puede resetear cualquier contraseña directo en la base de datos.
//
// Uso: node scripts/reset-password.js <usuario> <nueva-contraseña>
// (mejor invocarlo así, directo con node, que con "pnpm run" — pasar
// argumentos con "pnpm run ... -- ..." no siempre los reenvía bien)
// Ejemplo: node scripts/reset-password.js tesorero unaClaveNueva123
const bcrypt = require('bcryptjs');
const { getUsuarioPorLogin, actualizarUsuario } = require('../db');

const [usuario, nuevaPassword] = process.argv.slice(2);

if (!usuario || !nuevaPassword) {
  console.error('Uso: node scripts/reset-password.js <usuario> <nueva-contraseña>');
  process.exit(1);
}

const cuenta = getUsuarioPorLogin(usuario);
if (!cuenta) {
  console.error(`No existe ninguna cuenta con el usuario "${usuario}".`);
  process.exit(1);
}

actualizarUsuario({
  id: cuenta.id,
  tipo: cuenta.tipo,
  nombre: cuenta.nombre,
  cargo: cuenta.cargo,
  usuario: cuenta.usuario,
  passwordHash: bcrypt.hashSync(nuevaPassword, 10),
});

console.log(`Contraseña actualizada para "${usuario}" (${cuenta.nombre}).`);
