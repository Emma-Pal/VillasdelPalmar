const express = require('express');
const path = require('path');

const app = express();
// Puerto dedicado para este proyecto: el 3000 lo ha estado ocupando otro
// proyecto (Despacho Jurídico) de forma intermitente, causando que a veces
// cargara ese sitio en vez de este. Usamos un puerto propio para no competir.
const PORT = process.env.PORT || 3100;

// Motor de plantillas: las páginas viven en /views y comparten header/footer
// (views/partials/) para no repetir ese HTML en cada archivo.
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

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

// Una ruta por página. Cada una solo define su título/descripción y qué vista renderizar;
// el layout (header, fuentes, footer) ya está resuelto dentro de cada .ejs vía los partials.
app.get('/', (req, res) => {
  res.render('index', {
    title: 'Villas del Palmar',
    description: 'Villas del Palmar — departamentos con alberca, áreas verdes y seguridad 24/7.',
  });
});

app.get('/alberca', (req, res) => {
  res.render('alberca', {
    title: 'Alberca & terraza — Villas del Palmar',
    description: 'Conoce las tres albercas de Villas del Palmar: alberca con tobogán, alberca infinita y alberca común.',
  });
});

app.get('/areas-verdes', (req, res) => {
  res.render('areas-verdes', {
    title: 'Áreas verdes — Villas del Palmar',
    description: 'Jardines, andadores y espacios abiertos de Villas del Palmar.',
  });
});

app.get('/departamentos', (req, res) => {
  res.render('departamentos', {
    title: 'Departamentos — Villas del Palmar',
    description: 'Conoce los departamentos de Villas del Palmar: fachadas, terrazas y su entorno natural.',
  });
});

app.listen(PORT, () => {
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
