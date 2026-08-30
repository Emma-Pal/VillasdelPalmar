// Reglas de contraseña compartidas entre "Mi cuenta" y la administración
// de Usuarios, para no duplicar el mismo número mágico en dos archivos.
const MIN_LARGO_PASSWORD = 8;

function passwordEsValida(password) {
  return typeof password === 'string' && password.length >= MIN_LARGO_PASSWORD;
}

module.exports = { MIN_LARGO_PASSWORD, passwordEsValida };
