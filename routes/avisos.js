const express = require('express');
const path = require('path');
const fs = require('fs');
const { requireAuth, requireMesa } = require('../middleware/auth');
const { verifyCsrfToken } = require('../middleware/csrf');
const { upload, uploadsDir } = require('../middleware/upload');
const {
  CATEGORIAS_VALIDAS,
  getPublicaciones,
  contarPublicaciones,
  getPublicacionPorId,
  crearPublicacion,
  actualizarPublicacion,
  eliminarPublicacion,
  getUltimaVisitaAvisos,
  marcarVisitaAvisos,
  agregarArchivo,
  getArchivoPorId,
  eliminarArchivo,
} = require('../db');

const router = express.Router();

const POR_PAGINA = 10;

router.get('/panel/avisos', requireAuth, (req, res) => {
  const categoriaActual = CATEGORIAS_VALIDAS.includes(req.query.categoria) ? req.query.categoria : null;
  const paginaActual = Math.max(1, parseInt(req.query.pagina, 10) || 1);
  const total = contarPublicaciones(categoriaActual);
  const totalPaginas = Math.max(1, Math.ceil(total / POR_PAGINA));

  // La fecha de "última visita" ANTES de actualizarla es la que sirve para
  // marcar qué publicaciones son nuevas en esta misma carga de la página.
  const ultimaVisitaAnterior = getUltimaVisitaAvisos(req.session.user.id);

  const publicaciones = getPublicaciones(categoriaActual, {
    limit: POR_PAGINA,
    offset: (paginaActual - 1) * POR_PAGINA,
  });
  publicaciones.forEach((p) => {
    p.esNueva = ultimaVisitaAnterior ? p.creado_en > ultimaVisitaAnterior : true;
  });

  marcarVisitaAvisos(req.session.user.id, new Date().toISOString());

  res.render('avisos', {
    title: 'Avisos — Villas del Palmar',
    description: 'Estados financieros, mejoras y avisos de Villas del Palmar.',
    publicaciones,
    categoriaActual,
    paginaActual,
    totalPaginas,
  });
});

router.get('/panel/avisos/nueva', requireMesa, (req, res) => {
  res.render('aviso-form', {
    title: 'Nueva publicación — Villas del Palmar',
    description: 'Publicar un nuevo aviso, mejora o estado financiero.',
    publicacionEditada: null,
  });
});

router.get('/panel/avisos/:id/editar', requireMesa, (req, res) => {
  const publicacion = getPublicacionPorId(req.params.id);
  if (!publicacion) return res.redirect('/panel/avisos');

  res.render('aviso-form', {
    title: 'Editar publicación — Villas del Palmar',
    description: 'Editar una publicación existente.',
    publicacionEditada: publicacion,
  });
});

router.post('/panel/avisos', requireMesa, upload.array('archivos', 5), verifyCsrfToken, (req, res) => {
  const { categoria, titulo, cuerpo, fecha } = req.body;

  const id = crearPublicacion({
    autorId: req.session.user.id,
    categoria: CATEGORIAS_VALIDAS.includes(categoria) ? categoria : 'aviso',
    titulo,
    cuerpo,
    fecha: fecha || new Date().toISOString().slice(0, 10),
  });

  (req.files || []).forEach((archivo) => {
    agregarArchivo({ publicacionId: id, archivo: archivo.filename, archivoNombreOriginal: archivo.originalname });
  });

  res.redirect('/panel/avisos');
});

router.post('/panel/avisos/:id/editar', requireMesa, upload.array('archivos', 5), verifyCsrfToken, (req, res) => {
  const id = req.params.id;
  const { categoria, titulo, cuerpo, fecha } = req.body;

  actualizarPublicacion({
    id,
    categoria: CATEGORIAS_VALIDAS.includes(categoria) ? categoria : 'aviso',
    titulo,
    cuerpo,
    fecha,
  });

  // Los archivos nuevos se agregan a los que ya tenía (no los reemplazan);
  // para quitar uno existente hay un botón "Eliminar" por archivo en el formulario.
  (req.files || []).forEach((archivo) => {
    agregarArchivo({ publicacionId: id, archivo: archivo.filename, archivoNombreOriginal: archivo.originalname });
  });

  res.redirect('/panel/avisos');
});

router.post('/panel/avisos/:id/archivos/:archivoId/eliminar', requireMesa, verifyCsrfToken, (req, res) => {
  const archivo = getArchivoPorId(req.params.archivoId);
  if (archivo) {
    fs.unlink(path.join(uploadsDir, archivo.archivo), () => {});
    eliminarArchivo(archivo.id);
  }
  res.redirect(`/panel/avisos/${req.params.id}/editar`);
});

// Solo mesa puede borrar publicaciones. Los archivos físicos se borran del
// disco antes de eliminar el registro (las filas de `archivos` se limpian
// solas por el ON DELETE CASCADE en la base de datos).
router.post('/panel/avisos/:id/eliminar', requireMesa, verifyCsrfToken, (req, res) => {
  const publicacion = getPublicacionPorId(req.params.id);
  if (publicacion) {
    publicacion.archivos.forEach((archivo) => {
      fs.unlink(path.join(uploadsDir, archivo.archivo), () => {});
    });
  }
  eliminarPublicacion(req.params.id);
  res.redirect('/panel/avisos');
});

// Sirve un archivo adjunto solo si hay sesión — por eso no vive en /public.
router.get('/panel/archivos/:archivoId', requireAuth, (req, res) => {
  const archivo = getArchivoPorId(req.params.archivoId);
  if (!archivo) {
    return res.status(404).render('error', {
      title: 'No encontrado — Villas del Palmar',
      description: 'Archivo no encontrado.',
      mensaje: 'No encontramos ese archivo.',
    });
  }
  res.download(path.join(uploadsDir, archivo.archivo), archivo.archivo_nombre_original);
});

module.exports = router;
