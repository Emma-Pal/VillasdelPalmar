-- Esquema de Villas del Palmar para MySQL/MariaDB (cPanel).
-- Correr una sola vez desde phpMyAdmin, sobre la base de datos ya creada
-- con el Database Wizard de cPanel.

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('propietario','mesa') NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  cargo VARCHAR(100) NULL,             -- solo aplica a mesa directiva (ej. "Tesorero")
  usuario VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  ultima_visita_avisos DATETIME NULL   -- para saber qué publicaciones son "nuevas" para este usuario
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS publicaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  autor_id INT NOT NULL,
  categoria ENUM('financiero','mejora','aviso') NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  cuerpo TEXT NOT NULL,
  fecha DATE NOT NULL,
  creado_en DATETIME NOT NULL,         -- fecha/hora real de creación (distinta de "fecha", que es editorial)
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
