<?php
require_once __DIR__ . '/../db.php';

const CATEGORIAS_DOCUMENTO = ['reglamento', 'politica', 'carta_formato'];

function etiquetaCategoriaDocumento(string $categoria): string
{
    $etiquetas = [
        'reglamento' => 'Reglamentos',
        'politica' => 'Políticas',
        'carta_formato' => 'Cartas y formatos',
    ];
    return $etiquetas[$categoria] ?? $categoria;
}

// $categoria: null = todas. $vigente: true = solo las vigentes (vista
// principal), false = solo las archivadas ("Versiones anteriores").
// $buscar: coincidencia parcial en el título (case-insensitive vía LIKE).
// $orden: 'desc' (más recientes primero, default) o 'asc'.
function getDocumentos(?string $categoria = null, bool $vigente = true, ?string $buscar = null, string $orden = 'desc'): array
{
    $condiciones = ['vigente = ?'];
    $parametros = [$vigente ? 1 : 0];

    if ($categoria) {
        $condiciones[] = 'categoria = ?';
        $parametros[] = $categoria;
    }
    if ($buscar !== null && $buscar !== '') {
        $condiciones[] = 'titulo LIKE ?';
        $parametros[] = '%' . $buscar . '%';
    }

    $direccion = $orden === 'asc' ? 'ASC' : 'DESC';
    $stmt = db()->prepare(
        'SELECT d.*, u.nombre AS autor_nombre
         FROM documentos d JOIN usuarios u ON u.id = d.autor_id
         WHERE ' . implode(' AND ', $condiciones) . "
         ORDER BY d.fecha_documento $direccion, d.id $direccion"
    );
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}

function getDocumentoPorId($id): ?array
{
    $stmt = db()->prepare('SELECT * FROM documentos WHERE id = ?');
    $stmt->execute([$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

// Nombres ya usados antes (para sugerirlos con autocompletar en "Nombre del
// documento" — mismo patrón que ya se usaba para categorías libres).
function getNombresDocumentosUsados(): array
{
    $stmt = db()->query('SELECT DISTINCT titulo FROM documentos ORDER BY titulo');
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Marca como archivadas (vigente=0) todas las versiones vigentes que
// compartan el mismo título — se llama antes de crear un documento nuevo
// cuando se marca "Reemplaza una versión anterior".
function archivarDocumentosConMismoTitulo(string $titulo): void
{
    $stmt = db()->prepare("UPDATE documentos SET vigente = 0 WHERE titulo = ? AND vigente = 1");
    $stmt->execute([$titulo]);
}

function crearDocumento(
    $autorId,
    string $categoria,
    string $titulo,
    ?string $descripcion,
    string $fechaDocumento,
    ?string $vigenteDesde,
    string $archivo,
    string $archivoNombreOriginal
): string {
    $stmt = db()->prepare(
        'INSERT INTO documentos (categoria, titulo, descripcion, fecha_documento, vigente_desde, vigente, archivo, archivo_nombre_original, autor_id, creado_en)
         VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?)'
    );
    $stmt->execute([$categoria, $titulo, $descripcion, $fechaDocumento, $vigenteDesde, $archivo, $archivoNombreOriginal, $autorId, date('Y-m-d H:i:s')]);
    return db()->lastInsertId();
}

// $archivoNuevo/$archivoNombreOriginalNuevo son opcionales — si no se sube
// un archivo nuevo al editar, se conserva el que ya había (igual que la
// contraseña opcional en actualizarUsuario()). Editar nunca archiva
// versiones viejas (eso solo pasa al crear uno nuevo con "Reemplaza...").
function actualizarDocumento(
    $id,
    string $categoria,
    string $titulo,
    ?string $descripcion,
    string $fechaDocumento,
    ?string $vigenteDesde,
    ?string $archivoNuevo = null,
    ?string $archivoNombreOriginalNuevo = null
): void {
    if ($archivoNuevo) {
        $stmt = db()->prepare(
            'UPDATE documentos SET categoria = ?, titulo = ?, descripcion = ?, fecha_documento = ?, vigente_desde = ?, archivo = ?, archivo_nombre_original = ?, actualizado_en = ? WHERE id = ?'
        );
        $stmt->execute([$categoria, $titulo, $descripcion, $fechaDocumento, $vigenteDesde, $archivoNuevo, $archivoNombreOriginalNuevo, date('Y-m-d H:i:s'), $id]);
    } else {
        $stmt = db()->prepare(
            'UPDATE documentos SET categoria = ?, titulo = ?, descripcion = ?, fecha_documento = ?, vigente_desde = ?, actualizado_en = ? WHERE id = ?'
        );
        $stmt->execute([$categoria, $titulo, $descripcion, $fechaDocumento, $vigenteDesde, date('Y-m-d H:i:s'), $id]);
    }
}

function eliminarDocumento($id): void
{
    $stmt = db()->prepare('DELETE FROM documentos WHERE id = ?');
    $stmt->execute([$id]);
}
