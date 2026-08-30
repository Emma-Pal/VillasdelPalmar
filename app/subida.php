<?php
// Validación y guardado de archivos adjuntos ($_FILES['archivos']), FUERA
// del docroot — equivalente a middleware/upload.js.

define('UPLOADS_DIR', __DIR__ . '/../uploads');
define('EXTENSIONES_PERMITIDAS', ['pdf', 'jpg', 'jpeg', 'png']);
define('TAMANO_MAXIMO_BYTES', 10 * 1024 * 1024); // 10 MB

if (!is_dir(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}

// Procesa $_FILES['archivos'] (input múltiple, name="archivos[]") y regresa
// un arreglo de ['archivo' => nombreGuardado, 'archivo_nombre_original' => ...]
// por cada archivo. Un <input> dejado vacío se ignora sin error (igual que
// el fileFilter de multer con file.originalname vacío); un archivo con
// extensión no permitida o demasiado pesado sí lanza una excepción real,
// que la página debe atrapar y mostrar con renderError().
function procesarArchivosSubidos(): array
{
    if (empty($_FILES['archivos'])) {
        return [];
    }

    $archivos = $_FILES['archivos'];
    $cantidad = count($archivos['name']);
    $resultado = [];

    for ($i = 0; $i < $cantidad; $i++) {
        if ($archivos['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue; // campo vacío, no es un error
        }
        if ($archivos['error'][$i] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Hubo un problema al subir el archivo. Inténtalo de nuevo.');
        }
        if ($archivos['size'][$i] > TAMANO_MAXIMO_BYTES) {
            throw new RuntimeException('Uno de los archivos pesa más de 10 MB.');
        }

        $nombreOriginal = $archivos['name'][$i];
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if (!in_array($extension, EXTENSIONES_PERMITIDAS, true)) {
            throw new RuntimeException('Tipo de archivo no permitido. Usa PDF, JPG o PNG.');
        }

        $nombreGuardado = bin2hex(random_bytes(16)) . '.' . $extension;
        move_uploaded_file($archivos['tmp_name'][$i], UPLOADS_DIR . '/' . $nombreGuardado);

        $resultado[] = [
            'archivo' => $nombreGuardado,
            'archivo_nombre_original' => $nombreOriginal,
        ];
    }

    return $resultado;
}

function rutaArchivoFisico(string $nombreGuardado): string
{
    return UPLOADS_DIR . '/' . $nombreGuardado;
}
