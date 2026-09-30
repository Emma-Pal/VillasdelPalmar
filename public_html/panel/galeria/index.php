<?php
require_once __DIR__ . '/../../../app/bootstrap.php';
requireAuth();

$title = 'Acerca de — Villas del Palmar';
$description = 'La historia de Villas del Palmar y fotografías de sus instalaciones y amenidades, con sus horarios.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php include __DIR__ . '/../../partials/head.php'; ?>
</head>
<body>

  <?php include __DIR__ . '/../../partials/portal-header.php'; ?>

  <section class="page-banner page-banner--plain">
    <div class="page-banner-content">
      <span class="eyebrow">Conoce Villas del Palmar</span>
      <h1>Acerca de</h1>
      <p class="page-banner-lead">La historia del condominio y fotografías de sus instalaciones y amenidades.</p>
    </div>
  </section>

  <section class="detail-sections" style="padding-bottom: 0;">
    <div class="historia" data-reveal>
      <span class="eyebrow">Nuestra historia</span>
      <h2>Historia del condominio</h2>
      <img class="historia-img" src="/images/galeria/entrada-principal-de.jpg" alt="Entrada principal de Villas del Palmar" />
      <p>
        El Condominio Villas del Palmar se encuentra en una zona privilegiada en la Península de Santiago,
        municipio de Manzanillo, Colima. Su origen está ligado al desarrollo turístico que tomó forma en esa
        península durante la segunda mitad del siglo XX y que, con el tiempo, convirtió a Manzanillo en uno
        de los destinos más conocidos del Pacífico mexicano.
      </p>
      <p>
        En ese entorno se construyeron tres conjuntos residenciales: Villas del Palmar, Villas de PalmAlta
        y Villas de las Palmas. Aunque cada uno tuvo su propio origen, con los años quedaron incorporados
        a un mismo régimen de propiedad en condominio. De esa integración surgió el condominio que hoy
        lleva el nombre de Villas del Palmar.
      </p>
      <p>
        La unión de los tres conjuntos permitió concentrar en una sola administración el mantenimiento
        de las áreas comunes y reunir en una misma asamblea las decisiones de todos los propietarios.
      </p>
      <p>
        Existen documentos que sitúan el desarrollo, por lo menos, desde la década de 1970. Actualmente
        el condominio está integrado por 186 villas.
      </p>
      <p>
        A lo largo de estas décadas, la organización interna se ha ido ajustando a lo que las circunstancias
        han exigido: se formalizó la administración, se estableció un servicio de vigilancia y los
        propietarios, por medio de la Asamblea y del Comité de Administración, han participado en las
        decisiones sobre la conservación de las instalaciones.
      </p>
      <p>
        Villas del Palmar es hoy el resultado de aquella integración y del trabajo de varias generaciones
        de propietarios. Conservar las áreas comunes, mantener una convivencia ordenada y cuidar lo que
        pertenece a todos siguen siendo las tareas principales de la comunidad.
      </p>
    </div>
  </section>

  <section class="gallery" style="max-width: var(--max-width); margin: 0 auto; padding: 56px 24px 96px;">
    <div style="text-align: center; margin-bottom: 32px;">
      <span class="eyebrow">Fotografías</span>
      <h2>Instalaciones y amenidades</h2>
    </div>
    <?php if ($usuario['tipo'] === 'mesa'): ?>
      <p style="text-align: right; margin-bottom: 16px;">
        <a href="/panel/galeria/admin" class="btn btn-ghost-light">Administrar galería</a>
      </p>
    <?php endif; ?>
    <div class="gallery-grid" data-reveal>
      <a href="/panel/galeria/alberca" class="gallery-item gallery-item--wide">
        <span class="gallery-thumb thumb-1"></span>
        <span class="gallery-caption">Alberca &amp; terraza <em>Ver más →</em></span>
      </a>
      <a href="/panel/galeria/areas-verdes" class="gallery-item">
        <span class="gallery-thumb thumb-2"></span>
        <span class="gallery-caption">Áreas verdes <em>Ver más →</em></span>
      </a>
      <a href="/panel/galeria/departamentos" class="gallery-item">
        <span class="gallery-thumb thumb-3"></span>
        <span class="gallery-caption">Departamentos <em>Ver más →</em></span>
      </a>
    </div>
  </section>

  <?php include __DIR__ . '/../../partials/footer.php'; ?>

</body>
</html>
