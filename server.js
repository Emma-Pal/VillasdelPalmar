const express = require('express');
const path = require('path');
const session = require('express-session');
const SqliteStore = require('better-sqlite3-session-store')(session);
const { exponerUsuario, requireAuth } = require('./middleware/auth');
const { attachCsrfToken } = require('./middleware/csrf');
const { db, contarPublicacionesDesde, getUltimaVisitaAvisos } = require('./db');

const app = express();
// Puerto dedicado para este proyecto: el 3000 lo ha estado ocupando otro
// proyecto (Despacho Jurídico) de forma intermitente, causando que a veces
// cargara ese sitio en vez de este. Usamos un puerto propio para no competir.
const PORT = process.env.PORT || 3100;

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Convierte texto plano (lo que se escribe en un <textarea>) a HTML seguro:
// escapa cualquier etiqueta y respeta los saltos de línea como <br>. Sin esto,
// un aviso de varios párrafos se veía todo pegado en un solo bloque.
app.locals.nl2br = function nl2br(texto) {
  const escapado = String(texto || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
  return escapado.replace(/\n/g, '<br>');
};

// Para decidir si un archivo adjunto se muestra como vista previa de imagen
// o como link de descarga (ej. un PDF).
app.locals.esImagen = function esImagen(nombreArchivo) {
  return /\.(jpe?g|png)$/i.test(String(nombreArchivo || ''));
};

app.use(express.urlencoded({ extended: true })); // formularios normales (login, usuarios, avisos)

app.use(
  session({
    // Guardadas en la misma base SQLite (tabla `sessions`, aparte de las
    // nuestras) en vez del MemoryStore por defecto de express-session:
    // así un reinicio del servidor (ej. al aplicar una corrección) no
    // desconecta a todo mundo ni invalida los formularios ya abiertos.
    store: new SqliteStore({ client: db, expired: { clear: true, intervalMs: 900000 } }),
    secret: process.env.SESSION_SECRET || 'villas-del-palmar-dev-secret',
    resave: false,
    saveUninitialized: false,
    cookie: {
      maxAge: 1000 * 60 * 60 * 8, // 8 horas
      sameSite: 'lax',
      // `secure` exige HTTPS: en desarrollo local (http://localhost) rompería
      // el login, así que solo se activa cuando esto corra en producción real.
      secure: process.env.NODE_ENV === 'production',
    },
  })
);
app.use(exponerUsuario);
app.use(attachCsrfToken);

// Disponible en todas las vistas para marcar el link activo en el nav del portal.
app.use((req, res, next) => {
  res.locals.currentPath = req.path;
  next();
});

// Cuántas publicaciones hay desde la última vez que este usuario entró a
// Avisos — para el globito de "nuevo" en el nav. Se calcula en cada request
// (es una sola consulta muy barata) pero NUNCA actualiza la fecha de
// "última visita" — eso solo lo hace la ruta de /panel/avisos al visitarla.
app.use((req, res, next) => {
  if (req.session.user) {
    // Se busca fresco en la BD (no en la sesión, que se quedaría vieja en
    // cuanto el usuario visite Avisos una vez y esa fecha se actualice).
    const ultimaVisita = getUltimaVisitaAvisos(req.session.user.id);
    res.locals.avisosNuevos = contarPublicacionesDesde(ultimaVisita);
  }
  next();
});

// Ídem para las páginas renderizadas (no solo CSS/JS): así un F5 normal
// siempre trae el HTML más reciente en vez de una versión vieja cacheada.
app.use((req, res, next) => {
  res.setHeader('Cache-Control', 'no-store');
  next();
});

// Sirve todo lo que esté dentro de /public (css, js, imágenes) como archivos estáticos.
// Mientras seguimos en desarrollo, se desactiva el caché del navegador para estos
// archivos: sin esto, el navegador puede quedarse con una copia vieja de styles.css
// y la página se ve "rota" hasta que se fuerza una recarga completa (Ctrl+Shift+R).
// Antes de pasar a producción conviene reactivar el caché con un query de versión
// (ej. /css/styles.css?v=2) en vez de desactivarlo por completo.
app.use(
  express.static(path.join(__dirname, 'public'), {
    etag: false,
    lastModified: false,
    cacheControl: false,
    setHeaders: (res) => {
      res.setHeader('Cache-Control', 'no-store');
    },
  })
);

// El sitio es completamente privado: "/" solo decide a dónde mandar según la sesión.
app.get('/', (req, res) => {
  res.redirect(req.session.user ? '/panel' : '/login');
});

app.use(require('./routes/auth'));
app.use(require('./routes/panel'));
app.use(require('./routes/avisos'));
app.use(require('./routes/mesa'));
app.use(require('./routes/instalaciones'));
app.use(require('./routes/usuarios'));
app.use(require('./routes/cuenta'));

// 404 genérico para cualquier otra ruta dentro del portal.
app.use(requireAuth, (req, res) => {
  res.status(404).render('error', {
    title: 'No encontrado — Villas del Palmar',
    description: 'Página no encontrada.',
    mensaje: 'No encontramos esa página.',
  });
});

// Manejador de errores (ej. multer: archivo muy pesado o de un tipo no
// permitido). Sin esto, ese tipo de error mostraba la página de crash
// genérica de Express en vez de un mensaje entendible.
// eslint-disable-next-line no-unused-vars
app.use((err, req, res, next) => {
  console.error(err);
  res.status(400).render('error', {
    title: 'No se pudo completar — Villas del Palmar',
    description: 'Ocurrió un error al procesar la solicitud.',
    mensaje: err.message || 'Ocurrió un error al procesar la solicitud. Inténtalo de nuevo.',
  });
});

app.listen(PORT, () => {
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
