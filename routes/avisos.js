const express = require('express');
const path = require('path');
const fs = require('fs');
const { requireAuth, requireMesa } = require('../middleware/auth');
const { upload, uploadsDir } = require('../middleware/upload');
const { getPublicaciones, getPublicacionPorId, crearPublicacion, eliminarPublicacion } = require('../db');

const router = express.Router();

const CATEGORIAS_VALIDAS = ['financiero', 'mejora', 'aviso'];

router.get('/panel/avisos', requireAuth, (req, res) => {
  const categoriaActual = CATEGORIAS_VALIDAS.includes(req.query.categoria) ? req.query.categoria : null;
  res.render('avisos', {
    title: 'Avisos — Villas del Palmar',
    description: 'Estados financieros, mejoras y avisos de Villas del Palmar.',
    publicaciones: getPublicaciones(categoriaActual),
    categoriaActual,
  });
});

router.get('/panel/avisos/nueva', requireMesa, (req, res) => {
  res.render('aviso-nueva', {
    title: 'Nueva publicación — Villas del Palmar',
    description: 'Publicar un nuevo aviso, mejora o estado financiero.',
  });
});

router.post('/panel/avisos', requireMesa, upload.single('archivo'), (req, res) => {
  const { categoria, titulo, cuerpo } = req.body;

  crearPublicacion({
    autorId: req.session.user.id,
    categoria: CATEGORIAS_VALIDAS.includes(categoria) ? categoria : 'aviso',
    titulo,
    cuerpo,
    archivo: req.file ? req.file.filename : null,
    archivoNombreOriginal: req.file ? req.file.originalname : null,
    fecha: new Date().toISOString().slice(0, 10),
  });

  res.redirect('/panel/avisos');
});

// Solo mesa puede borrar publicaciones. Si tenía archivo adjunto, se borra
// también del disco para no dejar basura huérfana en /uploads.
router.post('/panel/avisos/:id/eliminar', requireMesa, (req, res) => {
  const publicacion = getPublicacionPorId(req.params.id);
  if (publicacion && publicacion.archivo) {
    const rutaArchivo = path.join(uploadsDir, publicacion.archivo);
    fs.unlink(rutaArchivo, () => {}); // si falla (ej. ya no existe), no es crítico
  }

  eliminarPublicacion(req.params.id);
  res.redirect('/panel/avisos');
});

// Sirve el archivo adjunto solo si hay sesión — por eso no vive en /public.
router.get('/panel/archivos/:id', requireAuth, (req, res) => {
  const publicacion = getPublicacionPorId(req.params.id);
  if (!publicacion || !publicacion.archivo) {
    return res.status(404).render('error', {
      title: 'No encontrado — Villas del Palmar',
      description: 'Archivo no encontrado.',
      mensaje: 'No encontramos ese archivo.',
    });
  }
  res.download(
    path.join(uploadsDir, publicacion.archivo),
    publicacion.archivo_nombre_original || publicacion.archivo
  );
});

module.exports = router;
