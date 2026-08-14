// Capa de datos del portal. Un solo archivo SQLite (better-sqlite3 es síncrono,
// así que no hace falta async/await ni manejar promesas para consultas simples).
const path = require('path');
const fs = require('fs');
const Database = require('better-sqlite3');

const dataDir = path.join(__dirname, '..', 'data');
if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });

const db = new Database(path.join(dataDir, 'villasdelpalmar.db'));
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

db.exec(`
  CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tipo TEXT NOT NULL CHECK (tipo IN ('propietario', 'mesa')),
    nombre TEXT NOT NULL,
    unidad TEXT,            -- solo aplica a propietarios (ej. "Depto 4B")
    cargo TEXT,             -- solo aplica a mesa directiva (ej. "Tesorero")
    usuario TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL
  );

  CREATE TABLE IF NOT EXISTS movimientos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    -- ON DELETE CASCADE: si se borra un propietario, su historial de pagos
    -- (que es exclusivamente suyo) se borra con él.
    propietario_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    fecha TEXT NOT NULL,    -- YYYY-MM-DD
    tipo TEXT NOT NULL CHECK (tipo IN ('cargo', 'pago')),
    concepto TEXT NOT NULL,
    monto REAL NOT NULL
  );

  CREATE TABLE IF NOT EXISTS publicaciones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    autor_id INTEGER NOT NULL REFERENCES usuarios(id),
    categoria TEXT NOT NULL CHECK (categoria IN ('financiero', 'mejora', 'aviso')),
    titulo TEXT NOT NULL,
    cuerpo TEXT NOT NULL,
    archivo TEXT,                  -- nombre del archivo guardado en /uploads
    archivo_nombre_original TEXT,  -- nombre original, para mostrarlo al descargar
    fecha TEXT NOT NULL            -- YYYY-MM-DD
  );
`);

// ===== Usuarios =====

function getUsuarioPorLogin(usuario) {
  return db.prepare('SELECT * FROM usuarios WHERE usuario = ?').get(usuario);
}

function getUsuarioPorId(id) {
  return db.prepare('SELECT * FROM usuarios WHERE id = ?').get(id);
}

function getPropietarios() {
  return db
    .prepare("SELECT * FROM usuarios WHERE tipo = 'propietario' ORDER BY unidad")
    .all();
}

function getMesa() {
  return db.prepare("SELECT * FROM usuarios WHERE tipo = 'mesa' ORDER BY cargo").all();
}

function crearUsuario({ tipo, nombre, unidad, cargo, usuario, passwordHash }) {
  return db
    .prepare(
      `INSERT INTO usuarios (tipo, nombre, unidad, cargo, usuario, password_hash)
       VALUES (?, ?, ?, ?, ?, ?)`
    )
    .run(tipo, nombre, unidad || null, cargo || null, usuario, passwordHash).lastInsertRowid;
}

function getUsuarios() {
  return db.prepare('SELECT * FROM usuarios ORDER BY tipo, unidad, cargo').all();
}

// Actualiza datos de un usuario. `passwordHash` es opcional — si no se manda,
// se conserva la contraseña actual (así "editar" no obliga a resetear clave).
function actualizarUsuario({ id, tipo, nombre, unidad, cargo, usuario, passwordHash }) {
  if (passwordHash) {
    db.prepare(
      `UPDATE usuarios SET tipo = ?, nombre = ?, unidad = ?, cargo = ?, usuario = ?, password_hash = ?
       WHERE id = ?`
    ).run(tipo, nombre, unidad || null, cargo || null, usuario, passwordHash, id);
  } else {
    db.prepare(
      `UPDATE usuarios SET tipo = ?, nombre = ?, unidad = ?, cargo = ?, usuario = ?
       WHERE id = ?`
    ).run(tipo, nombre, unidad || null, cargo || null, usuario, id);
  }
}

// Lanza el error de SQLite tal cual (FOREIGN KEY constraint failed) si el
// usuario tiene publicaciones — así no se puede borrar sin querer al autor
// de un estado financiero ya publicado. La ruta que llama esto decide cómo
// mostrar ese error de forma amigable.
function eliminarUsuario(id) {
  db.prepare('DELETE FROM usuarios WHERE id = ?').run(id);
}

// ===== Movimientos (pagos/cargos) =====

function getMovimientos(propietarioId) {
  return db
    .prepare('SELECT * FROM movimientos WHERE propietario_id = ? ORDER BY fecha DESC, id DESC')
    .all(propietarioId);
}

// Saldo positivo = el propietario debe esa cantidad (cargos > pagos).
function getSaldo(propietarioId) {
  const row = db
    .prepare(
      `SELECT
         COALESCE(SUM(CASE WHEN tipo = 'cargo' THEN monto ELSE 0 END), 0) AS cargos,
         COALESCE(SUM(CASE WHEN tipo = 'pago' THEN monto ELSE 0 END), 0) AS pagos
       FROM movimientos WHERE propietario_id = ?`
    )
    .get(propietarioId);
  return row.cargos - row.pagos;
}

function crearMovimiento({ propietarioId, fecha, tipo, concepto, monto }) {
  return db
    .prepare(
      `INSERT INTO movimientos (propietario_id, fecha, tipo, concepto, monto)
       VALUES (?, ?, ?, ?, ?)`
    )
    .run(propietarioId, fecha, tipo, concepto, monto).lastInsertRowid;
}

// ===== Publicaciones (estados financieros / mejoras / avisos) =====

function getPublicaciones(categoria) {
  const base = `
    SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
    FROM publicaciones p
    JOIN usuarios u ON u.id = p.autor_id`;
  if (categoria) {
    return db
      .prepare(`${base} WHERE p.categoria = ? ORDER BY p.fecha DESC, p.id DESC`)
      .all(categoria);
  }
  return db.prepare(`${base} ORDER BY p.fecha DESC, p.id DESC`).all();
}

function getPublicacionPorId(id) {
  return db
    .prepare(
      `SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
       FROM publicaciones p JOIN usuarios u ON u.id = p.autor_id
       WHERE p.id = ?`
    )
    .get(id);
}

function crearPublicacion({ autorId, categoria, titulo, cuerpo, archivo, archivoNombreOriginal, fecha }) {
  return db
    .prepare(
      `INSERT INTO publicaciones (autor_id, categoria, titulo, cuerpo, archivo, archivo_nombre_original, fecha)
       VALUES (?, ?, ?, ?, ?, ?, ?)`
    )
    .run(autorId, categoria, titulo, cuerpo, archivo || null, archivoNombreOriginal || null, fecha)
    .lastInsertRowid;
}

function eliminarPublicacion(id) {
  db.prepare('DELETE FROM publicaciones WHERE id = ?').run(id);
}

module.exports = {
  db,
  getUsuarioPorLogin,
  getUsuarioPorId,
  getPropietarios,
  getMesa,
  getUsuarios,
  crearUsuario,
  actualizarUsuario,
  eliminarUsuario,
  getMovimientos,
  getSaldo,
  crearMovimiento,
  getPublicaciones,
  getPublicacionPorId,
  crearPublicacion,
  eliminarPublicacion,
};
