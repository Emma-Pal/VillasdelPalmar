// Copia de seguridad de la base de datos. Como toda la información del
// portal vive en un solo archivo SQLite, perderlo (borrado accidental, disco
// dañado, etc.) significaría perder todo el historial de publicaciones.
//
// Uso: pnpm run backup
// Genera data/backups/villasdelpalmar-AAAA-MM-DD_HH-mm-ss.db
//
// Esto NO se programa solo — hay que correrlo (o agendarlo, ej. con el
// Programador de tareas de Windows) para que sirva de algo.
const path = require('path');
const fs = require('fs');
const { db } = require('../db');

const backupsDir = path.join(__dirname, '..', 'data', 'backups');
if (!fs.existsSync(backupsDir)) fs.mkdirSync(backupsDir, { recursive: true });

const marcaDeTiempo = new Date().toISOString().replace(/:/g, '-').replace(/\..+/, '');
const destino = path.join(backupsDir, `villasdelpalmar-${marcaDeTiempo}.db`);

// `.backup()` de better-sqlite3 usa la API nativa de respaldo de SQLite:
// genera una copia consistente aunque la base esté en uso (modo WAL), a
// diferencia de copiar el archivo .db a mano con fs.copyFile.
db.backup(destino)
  .then(() => {
    console.log(`Respaldo creado: ${destino}`);
  })
  .catch((err) => {
    console.error('No se pudo hacer el respaldo:', err);
    process.exit(1);
  });
