// Configuración de multer para los archivos adjuntos de publicaciones
// (ej. el PDF de un estado financiero). Se guardan FUERA de /public — así
// nadie puede acceder al archivo por su URL directa sin pasar por la ruta
// protegida /panel/archivos/:id, que sí verifica la sesión primero.
const path = require('path');
const fs = require('fs');
const multer = require('multer');

const uploadsDir = path.join(__dirname, '..', 'uploads');
if (!fs.existsSync(uploadsDir)) fs.mkdirSync(uploadsDir, { recursive: true });

const storage = multer.diskStorage({
  destination: (req, file, cb) => cb(null, uploadsDir),
  filename: (req, file, cb) => {
    const sufijo = Date.now() + '-' + Math.round(Math.random() * 1e9);
    cb(null, sufijo + path.extname(file.originalname));
  },
});

const upload = multer({
  storage,
  limits: { fileSize: 10 * 1024 * 1024 }, // 10 MB
  fileFilter: (req, file, cb) => {
    // Un <input type="file"> vacío igual manda una "parte" en el formulario
    // (con nombre de archivo vacío) — no es un archivo real, solo se ignora.
    // Sin este caso, cualquier edición sin adjuntar nada nuevo se rechazaba
    // como si fuera un archivo inválido y la publicación no se guardaba.
    if (!file.originalname) {
      return cb(null, false);
    }
    const permitidos = ['.pdf', '.jpg', '.jpeg', '.png'];
    if (permitidos.includes(path.extname(file.originalname).toLowerCase())) {
      cb(null, true);
    } else {
      cb(new Error('Tipo de archivo no permitido. Usa PDF, JPG o PNG.'));
    }
  },
});

module.exports = { upload, uploadsDir };
