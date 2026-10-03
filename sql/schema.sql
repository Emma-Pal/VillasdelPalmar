-- Esquema de Villas del Palmar para MySQL/MariaDB (cPanel).
-- Correr una sola vez desde phpMyAdmin, sobre la base de datos ya creada
-- con el Database Wizard de cPanel.

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('propietario','mesa') NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  cargo VARCHAR(100) NULL,             -- solo aplica a mesa directiva (ej. "Tesorero")
  -- Solo aplica a propietario (cada propietario tiene su propia cuenta y su
  -- propia villa — ya no es una sola cuenta compartida). UNIQUE y no único
  -- parte de una PK compuesta: en InnoDB, varios NULL sí pueden coexistir en
  -- una columna UNIQUE (las cuentas de mesa, que no tienen villa), pero dos
  -- filas nunca pueden compartir el mismo número de villa.
  villa VARCHAR(20) NULL UNIQUE,
  usuario VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  ultima_visita_avisos DATETIME NULL   -- para saber qué publicaciones son "nuevas" para este usuario
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS publicaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  autor_id INT NOT NULL,
  -- VARCHAR y no ENUM: además de "financiero"/"mejora"/"aviso"/"convocatoria"/
  -- "acta" (con estilo y pestaña propios), la mesa directiva puede escribir
  -- una categoría libre ("Otra") — un ENUM no lo permitiría sin ALTER TABLE.
  categoria VARCHAR(50) NOT NULL,
  prioridad ENUM('urgente','importante','informativo') NOT NULL DEFAULT 'informativo',
  destacado TINYINT(1) NOT NULL DEFAULT 0,  -- aparece primero en Avisos y en el Panel
  publicado TINYINT(1) NOT NULL DEFAULT 1,  -- 0 = borrador, solo visible para la mesa
  audiencia ENUM('todos','comite') NOT NULL DEFAULT 'todos', -- 'comite' = invisible para propietarios
  titulo VARCHAR(255) NOT NULL,
  cuerpo TEXT NOT NULL,
  fecha DATE NOT NULL,                 -- fecha editorial; se fija sola al crear, nunca se edita
  fecha_evento DATE NULL,              -- fecha de la asamblea (convocatoria y, opcional, acta)
  -- Las 4 columnas siguientes solo aplican a categoria IN ('convocatoria','acta') —
  -- rediseño de /panel/asambleas, oct. 2026.
  tipo_asamblea ENUM('ordinaria','extraordinaria') NULL,
  hora_evento TIME NULL,               -- solo convocatoria: hora de la 1a convocatoria
  lugar_evento VARCHAR(255) NULL,      -- solo convocatoria
  anio_asamblea SMALLINT NULL,         -- solo acta: año al que corresponde (independiente de fecha_evento, que ahí es opcional)
  creado_en DATETIME NOT NULL,         -- fecha/hora real de creación
  editado_en DATETIME NULL,            -- se llena cada vez que se guarda una edición
  FOREIGN KEY (autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Varios archivos por publicación.
CREATE TABLE IF NOT EXISTS archivos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  publicacion_id INT NOT NULL,
  archivo VARCHAR(255) NOT NULL,               -- nombre guardado en /uploads
  archivo_nombre_original VARCHAR(255) NOT NULL,
  FOREIGN KEY (publicacion_id) REFERENCES publicaciones(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Migración 2026-09: módulos del cliente (Asambleas, Documentos,
-- Directorio, Solicitudes) =====
-- (La tabla "acuerdos" que vivía aquí se quitó en el rediseño de Asambleas
-- de oct. 2026 — ver la migración 2026-10e más abajo.)

-- Biblioteca de documentos (reglamento, escritura, políticas, formatos).
-- Un documento = un archivo (a diferencia de publicaciones, que puede
-- llevar varios) — por eso no reutiliza la tabla `archivos`.
-- "Guía del propietario" (rediseño oct. 2026). vigente=0 es una versión
-- archivada: se reemplazó por una más nueva con el mismo título (ver
-- "Reemplaza una versión anterior" en el formulario), pero se conserva en
-- vez de borrarse, para consultar versiones viejas si hace falta.
CREATE TABLE IF NOT EXISTS documentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria ENUM('reglamento','politica','carta_formato') NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT NULL,
  fecha_documento DATE NOT NULL,       -- fecha del documento (editorial, no de captura) — define el orden de la lista
  vigente_desde DATE NULL,             -- opcional: a partir de cuándo aplica
  vigente TINYINT(1) NOT NULL DEFAULT 1,
  archivo VARCHAR(255) NOT NULL,
  archivo_nombre_original VARCHAR(255) NOT NULL,
  autor_id INT NOT NULL,
  creado_en DATETIME NOT NULL,
  actualizado_en DATETIME NULL,
  FOREIGN KEY (autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Directorio de contactos (administración, seguridad, mantenimiento,
-- emergencias, proveedores autorizados).
CREATE TABLE IF NOT EXISTS contactos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria ENUM('administracion','seguridad','mantenimiento','emergencia','proveedor') NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  puesto VARCHAR(150) NULL,
  telefono VARCHAR(50) NULL,
  correo VARCHAR(150) NULL,
  notas TEXT NULL,
  creado_en DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Solicitudes de propietarios (quejas/fallas/sugerencias). El folio que se
-- le muestra al propietario es simplemente `SOL-` + el id, con ceros a la
-- izquierda — no necesita columna propia.
CREATE TABLE IF NOT EXISTS solicitudes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('queja','falla','sugerencia') NOT NULL,
  asunto VARCHAR(255) NOT NULL,
  descripcion TEXT NOT NULL,
  ubicacion VARCHAR(150) NULL,
  estatus ENUM('pendiente','en_progreso','resuelto') NOT NULL DEFAULT 'pendiente',
  respuesta TEXT NULL,
  autor_id INT NOT NULL,
  creado_en DATETIME NOT NULL,
  actualizado_en DATETIME NULL,
  FOREIGN KEY (autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Migración 2026-09b: Galería administrable =====
-- Las 3 páginas de Galería (alberca, áreas verdes, departamentos) siguen
-- teniendo sus bloques originales fijos en el código; esta tabla es para
-- las que la mesa vaya agregando después, con el mismo formato (foto +
-- eyebrow + título + descripción + lista de características).
CREATE TABLE IF NOT EXISTS galeria_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  categoria ENUM('alberca','areas-verdes','departamentos') NOT NULL,
  eyebrow VARCHAR(100) NOT NULL,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT NOT NULL,
  caracteristicas TEXT NULL,          -- una característica por línea
  archivo VARCHAR(255) NOT NULL,
  archivo_nombre_original VARCHAR(255) NOT NULL,
  autor_id INT NOT NULL,
  creado_en DATETIME NOT NULL,
  FOREIGN KEY (autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===== Migración 2026-10: Avisos en formato tabla (prioridad + publicado) =====
-- Si la base de datos ya existe (instalación en producción), correr esto
-- una sola vez en phpMyAdmin — ya está incluido arriba para instalaciones
-- nuevas desde cero.
-- ALTER TABLE publicaciones
--   ADD COLUMN prioridad ENUM('urgente','importante','informativo') NOT NULL DEFAULT 'informativo' AFTER categoria,
--   ADD COLUMN publicado TINYINT(1) NOT NULL DEFAULT 1 AFTER destacado;

-- ===== Migración 2026-10b: Destinatarios (Todos / Comité) =====
-- ALTER TABLE publicaciones
--   ADD COLUMN audiencia ENUM('todos','comite') NOT NULL DEFAULT 'todos' AFTER publicado;

-- ===== Migración 2026-10c: Cuenta individual por propietario (número de villa) =====
-- ALTER TABLE usuarios
--   ADD COLUMN villa VARCHAR(20) NULL UNIQUE AFTER cargo;

-- ===== Migración 2026-10d: Rediseño de Asambleas (convocatorias y actas) =====
-- ALTER TABLE publicaciones
--   ADD COLUMN tipo_asamblea ENUM('ordinaria','extraordinaria') NULL AFTER fecha_evento,
--   ADD COLUMN hora_evento TIME NULL AFTER tipo_asamblea,
--   ADD COLUMN lugar_evento VARCHAR(255) NULL AFTER hora_evento,
--   ADD COLUMN anio_asamblea SMALLINT NULL AFTER lugar_evento;

-- ===== Migración 2026-10e: se quita "Acuerdos y seguimiento" de Asambleas =====
-- Opcional — la app ya no usa esta tabla para nada, así que no es necesario
-- correr esto para que todo siga funcionando. Solo bórrala si de verdad no
-- te interesa conservar los acuerdos que ya se hayan capturado.
-- DROP TABLE IF EXISTS acuerdos;

-- ===== Migración 2026-10f: "Guía del propietario" (antes "Documentos") =====
-- 1) Ensanchar el ENUM para poder reacomodar los valores viejos sin perderlos:
-- ALTER TABLE documentos MODIFY COLUMN categoria ENUM('reglamento','escritura','politica','formato','carta_formato') NOT NULL;
-- 2) "Escritura constitutiva" y "Formatos" se consolidan en la nueva categoría única "Cartas y formatos":
-- UPDATE documentos SET categoria = 'carta_formato' WHERE categoria IN ('escritura', 'formato');
-- 3) Angostar el ENUM a las 3 categorías finales:
-- ALTER TABLE documentos MODIFY COLUMN categoria ENUM('reglamento','politica','carta_formato') NOT NULL;
-- 4) Columnas nuevas — fecha_documento se llena con creado_en de cada fila ya existente, como mejor valor de partida:
-- ALTER TABLE documentos
--   ADD COLUMN fecha_documento DATE NULL AFTER categoria,
--   ADD COLUMN vigente_desde DATE NULL AFTER fecha_documento,
--   ADD COLUMN vigente TINYINT(1) NOT NULL DEFAULT 1 AFTER vigente_desde;
-- UPDATE documentos SET fecha_documento = DATE(creado_en) WHERE fecha_documento IS NULL;
-- ALTER TABLE documentos MODIFY COLUMN fecha_documento DATE NOT NULL;
