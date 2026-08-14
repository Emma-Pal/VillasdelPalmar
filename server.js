const express = require('express');
const path = require('path');
const session = require('express-session');
const { exponerUsuario, requireAuth } = require('./middleware/auth');

const app = express();
// Puerto dedicado para este proyecto: el 3000 lo ha estado ocupando otro
// proyecto (Despacho Jurídico) de forma intermitente, causando que a veces
// cargara ese sitio en vez de este. Usamos un puerto propio para no competir.
const PORT = process.env.PORT || 3100;

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(express.urlencoded({ extended: true })); // formularios normales (login, movimientos)

app.use(
  session({
    secret: process.env.SESSION_SECRET || 'villas-del-palmar-dev-secret',
    resave: false,
    saveUninitialized: false,
    cookie: { maxAge: 1000 * 60 * 60 * 8 }, // 8 horas
  })
);
app.use(exponerUsuario);

// Disponible en todas las vistas para marcar el link activo en el nav del portal.
app.use((req, res, next) => {
  res.locals.currentPath = req.path;
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
app.use(require('./routes/pagos'));
app.use(require('./routes/avisos'));
app.use(require('./routes/mesa'));
app.use(require('./routes/instalaciones'));
app.use(require('./routes/usuarios'));

// 404 genérico para cualquier otra ruta dentro del portal.
app.use(requireAuth, (req, res) => {
  res.status(404).render('error', {
    title: 'No encontrado — Villas del Palmar',
    description: 'Página no encontrada.',
    mensaje: 'No encontramos esa página.',
  });
});

app.listen(PORT, () => {
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
