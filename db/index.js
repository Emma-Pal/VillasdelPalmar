// Capa de datos del portal. Un solo archivo SQLite (better-sqlite3 es síncrono,
// así que no hace falta async/await ni manejar promesas para consultas simples).
//
// Modelo de cuentas: una sola cuenta compartida tipo "propietario" (todos los
// condóminos entran con la misma), y varias cuentas tipo "mesa" (una por
// cargo: presidente, tesorero, etc.) que sí necesitan identificarse porque
// publican contenido a su nombre.
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
    cargo TEXT,             -- solo aplica a mesa directiva (ej. "Tesorero")
    usuario TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    ultima_visita_avisos TEXT  -- ISO datetime; para saber qué publicaciones son "nuevas" para este usuario
  );

  CREATE TABLE IF NOT EXISTS publicaciones (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    autor_id INTEGER NOT NULL REFERENCES usuarios(id),
    categoria TEXT NOT NULL CHECK (categoria IN ('financiero', 'mejora', 'aviso')),
    titulo TEXT NOT NULL,
    cuerpo TEXT NOT NULL,
    fecha TEXT NOT NULL,       -- YYYY-MM-DD
    creado_en TEXT NOT NULL    -- ISO datetime real de creación, para "qué es nuevo"
  );

  -- Varios archivos por publicación (antes solo se permitía uno).
  CREATE TABLE IF NOT EXISTS archivos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    publicacion_id INTEGER NOT NULL REFERENCES publicaciones(id) ON DELETE CASCADE,
    archivo TEXT NOT NULL,             -- nombre guardado en /uploads
    archivo_nombre_original TEXT NOT NULL
  );
`);

// ===== Usuarios =====

function getUsuarioPorLogin(usuario) {
  return db.prepare('SELECT * FROM usuarios WHERE usuario = ?').get(usuario);
}

function getUsuarioPorId(id) {
  return db.prepare('SELECT * FROM usuarios WHERE id = ?').get(id);
}

function getMesa() {
  return db.prepare("SELECT * FROM usuarios WHERE tipo = 'mesa' ORDER BY cargo").all();
}

function getUsuarios() {
  return db.prepare('SELECT * FROM usuarios ORDER BY tipo DESC, cargo, nombre').all();
}

function crearUsuario({ tipo, nombre, cargo, usuario, passwordHash }) {
  return db
    .prepare(
      `INSERT INTO usuarios (tipo, nombre, cargo, usuario, password_hash)
       VALUES (?, ?, ?, ?, ?)`
    )
    .run(tipo, nombre, tipo === 'mesa' ? cargo || null : null, usuario, passwordHash).lastInsertRowid;
}

// Actualiza datos de un usuario. `passwordHash` es opcional — si no se manda,
// se conserva la contraseña actual (así "editar" no obliga a resetear clave).
function actualizarUsuario({ id, tipo, nombre, cargo, usuario, passwordHash }) {
  const cargoFinal = tipo === 'mesa' ? cargo || null : null;
  if (passwordHash) {
    db.prepare(
      `UPDATE usuarios SET tipo = ?, nombre = ?, cargo = ?, usuario = ?, password_hash = ?
       WHERE id = ?`
    ).run(tipo, nombre, cargoFinal, usuario, passwordHash, id);
  } else {
    db.prepare(
      `UPDATE usuarios SET tipo = ?, nombre = ?, cargo = ?, usuario = ?
       WHERE id = ?`
    ).run(tipo, nombre, cargoFinal, usuario, id);
  }
}

// Lanza el error de SQLite tal cual (FOREIGN KEY constraint failed) si el
// usuario tiene publicaciones — así no se puede borrar sin querer al autor
// de un estado financiero ya publicado. La ruta que llama esto decide cómo
// mostrar ese error de forma amigable.
function eliminarUsuario(id) {
  db.prepare('DELETE FROM usuarios WHERE id = ?').run(id);
}

function getUltimaVisitaAvisos(usuarioId) {
  const row = db.prepare('SELECT ultima_visita_avisos FROM usuarios WHERE id = ?').get(usuarioId);
  return row ? row.ultima_visita_avisos : null;
}

function marcarVisitaAvisos(usuarioId, fechaIso) {
  db.prepare('UPDATE usuarios SET ultima_visita_avisos = ? WHERE id = ?').run(fechaIso, usuarioId);
}

// ===== Publicaciones (estados financieros / mejoras / avisos) =====

const CATEGORIAS_VALIDAS = ['financiero', 'mejora', 'aviso'];

function getPublicaciones(categoria, { limit = 10, offset = 0 } = {}) {
  const base = `
    SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
    FROM publicaciones p
    JOIN usuarios u ON u.id = p.autor_id`;
  const params = [];
  let where = '';
  if (categoria) {
    where = ' WHERE p.categoria = ?';
    params.push(categoria);
  }
  const filas = db
    .prepare(`${base}${where} ORDER BY p.fecha DESC, p.id DESC LIMIT ? OFFSET ?`)
    .all(...params, limit, offset);

  filas.forEach((p) => {
    p.archivos = getArchivosDePublicacion(p.id);
  });
  return filas;
}

function contarPublicaciones(categoria) {
  if (categoria) {
    return db.prepare('SELECT COUNT(*) AS n FROM publicaciones WHERE categoria = ?').get(categoria).n;
  }
  return db.prepare('SELECT COUNT(*) AS n FROM publicaciones').get().n;
}

function getPublicacionPorId(id) {
  const publicacion = db
    .prepare(
      `SELECT p.*, u.nombre AS autor_nombre, u.cargo AS autor_cargo
       FROM publicaciones p JOIN usuarios u ON u.id = p.autor_id
       WHERE p.id = ?`
    )
    .get(id);
  if (publicacion) publicacion.archivos = getArchivosDePublicacion(id);
  return publicacion;
}

function contarPublicacionesDesde(fechaIso) {
  if (!fechaIso) return contarPublicaciones();
  // Se compara por fecha de creación real (creado_en), no por la fecha "editorial" (fecha).
  return db.prepare('SELECT COUNT(*) AS n FROM publicaciones WHERE creado_en > ?').get(fechaIso).n;
}

function crearPublicacion({ autorId, categoria, titulo, cuerpo, fecha }) {
  return db
    .prepare(
      `INSERT INTO publicaciones (autor_id, categoria, titulo, cuerpo, fecha, creado_en)
       VALUES (?, ?, ?, ?, ?, ?)`
    )
    .run(autorId, categoria, titulo, cuerpo, fecha, new Date().toISOString()).lastInsertRowid;
}

function actualizarPublicacion({ id, categoria, titulo, cuerpo, fecha }) {
  db.prepare('UPDATE publicaciones SET categoria = ?, titulo = ?, cuerpo = ?, fecha = ? WHERE id = ?').run(
    categoria,
    titulo,
    cuerpo,
    fecha,
    id
  );
}

function eliminarPublicacion(id) {
  // Los archivos (fila de la tabla `archivos`) se borran solos por el
  // ON DELETE CASCADE; los ARCHIVOS FÍSICOS hay que borrarlos aparte desde
  // la ruta (ahí sí se sabe la carpeta de uploads), antes de llamar esto.
  db.prepare('DELETE FROM publicaciones WHERE id = ?').run(id);
}

// ===== Archivos adjuntos (varios por publicación) =====

function getArchivosDePublicacion(publicacionId) {
  return db.prepare('SELECT * FROM archivos WHERE publicacion_id = ? ORDER BY id').all(publicacionId);
}

function getArchivoPorId(id) {
  return db.prepare('SELECT * FROM archivos WHERE id = ?').get(id);
}

function agregarArchivo({ publicacionId, archivo, archivoNombreOriginal }) {
  return db
    .prepare(
      `INSERT INTO archivos (publicacion_id, archivo, archivo_nombre_original) VALUES (?, ?, ?)`
    )
    .run(publicacionId, archivo, archivoNombreOriginal).lastInsertRowid;
}

function eliminarArchivo(id) {
  db.prepare('DELETE FROM archivos WHERE id = ?').run(id);
}

module.exports = {
  db,
  CATEGORIAS_VALIDAS,
  getUsuarioPorLogin,
  getUsuarioPorId,
  getMesa,
  getUsuarios,
  crearUsuario,
  actualizarUsuario,
  eliminarUsuario,
  getUltimaVisitaAvisos,
  marcarVisitaAvisos,
  getPublicaciones,
  contarPublicaciones,
  contarPublicacionesDesde,
  getPublicacionPorId,
  crearPublicacion,
  actualizarPublicacion,
  eliminarPublicacion,
  getArchivosDePublicacion,
  getArchivoPorId,
  agregarArchivo,
  eliminarArchivo,
};
