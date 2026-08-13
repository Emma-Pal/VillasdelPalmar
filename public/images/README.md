# Imágenes del sitio

Carpeta donde van todos los archivos visuales (fotos, iconos, etc.) que use la página. Todo lo de aquí es servido directamente por Express en `/images/...` (por ejemplo, `public/images/hero/fachada.jpg` se referencia en el HTML/CSS como `/images/hero/fachada.jpg`).

## Subcarpetas

- **hero/** — imagen(es) de fondo de la sección de inicio.
- **galeria/** — fotos de "El lugar" (alberca, áreas verdes, departamentos, etc.).
- **amenidades/** — íconos o fotos de apoyo para la sección de amenidades (opcional, hoy usa íconos SVG en el código).
- **logo/** — logotipo en distintos formatos (para el header, favicon, redes sociales).

## Recomendaciones al agregar imágenes

- Formato preferido: **WebP** o **JPG** para fotos; **SVG** o **PNG** para logos/iconos.
- Nombres descriptivos en minúsculas y sin espacios: `alberca-01.webp`, `fachada-noche.jpg`.
- Comprime antes de subir (ideal < 300 KB por foto) para que la página cargue rápido.
- Para el hero, una imagen horizontal (mínimo 1920×1080) se ve mejor a pantalla completa.
