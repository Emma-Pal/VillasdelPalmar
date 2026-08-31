// ===== Header: fondo sólido al hacer scroll =====
// (guardas con `if`: no todas las páginas tienen header — ej. login)
const header = document.getElementById('header');

if (header) {
  const updateHeader = () => {
    // El header del portal (.portal-header) no tiene hero detrás: siempre
    // se ve "sólido", así que no se le quita la clase al estar arriba.
    if (header.classList.contains('portal-header')) return;
    header.classList.toggle('scrolled', window.scrollY > 40);
  };
  updateHeader();
  window.addEventListener('scroll', updateHeader);
}

// ===== Menú móvil =====
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');

if (navToggle && navMenu) {
  navToggle.addEventListener('click', () => {
    const isOpen = navMenu.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });

  // Cierra el menú al elegir una opción (útil en móvil)
  navMenu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      navMenu.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

// ===== Animación de aparición al hacer scroll =====
const revealTargets = document.querySelectorAll('[data-reveal]');

const revealObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.15 }
);

revealTargets.forEach((el) => revealObserver.observe(el));

// ===== Formulario de contacto (aún sin backend real) =====
// Solo existe en la página de inicio, por eso se revisa antes de usarlo
// (este mismo archivo se comparte con las páginas de detalle).
const form = document.getElementById('contact-form');
const formNote = document.getElementById('form-note');

if (form) {
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    // TODO: conectar con un endpoint en Express (ej. POST /api/contacto)
    formNote.hidden = false;
    form.reset();
  });
}

// ===== Año dinámico en el footer =====
const yearEl = document.getElementById('year');
if (yearEl) yearEl.textContent = new Date().getFullYear();

// ===== Formulario de usuario: el campo "Cargo" solo aplica a mesa directiva =====
const tipoSelect = document.getElementById('tipo-select');
const campoCargo = document.getElementById('campo-cargo');

if (tipoSelect && campoCargo) {
  const actualizarCampos = () => {
    campoCargo.hidden = tipoSelect.value !== 'mesa';
  };
  actualizarCampos();
  tipoSelect.addEventListener('change', actualizarCampos);
}

// ===== Formulario de publicaciones: el campo de texto libre solo aplica
// cuando se elige "Otra (especificar)" en Categoría =====
const categoriaSelect = document.getElementById('categoria-select');
const campoCategoriaOtra = document.getElementById('campo-categoria-otra');

if (categoriaSelect && campoCategoriaOtra) {
  const actualizarCampoCategoria = () => {
    campoCategoriaOtra.hidden = categoriaSelect.value !== '__otra__';
  };
  actualizarCampoCategoria();
  categoriaSelect.addEventListener('change', actualizarCampoCategoria);
}

// ===== Lightbox de imágenes en publicaciones =====
// Al hacer clic en una imagen se abre en grande, con una animación de zoom
// que sale exactamente del lugar donde estaba la miniatura (técnica "FLIP":
// se mide dónde empieza y dónde termina, y se anima la diferencia con
// transform, que es lo único que el navegador puede animar sin trabarse).
const lightboxOverlay = document.getElementById('lightbox-overlay');
const lightboxImg = document.getElementById('lightbox-img');
const lightboxDownload = document.getElementById('lightbox-download');
const lightboxClose = document.getElementById('lightbox-close');

if (lightboxOverlay && lightboxImg && lightboxDownload && lightboxClose) {
  let origenRect = null;

  const transformDesdeOrigen = (origen, destino) => {
    const dx = origen.left + origen.width / 2 - (destino.left + destino.width / 2);
    const dy = origen.top + origen.height / 2 - (destino.top + destino.height / 2);
    const sx = origen.width / destino.width;
    const sy = origen.height / destino.height;
    return `translate(${dx}px, ${dy}px) scale(${sx}, ${sy})`;
  };

  const animarDesdeOrigen = () => {
    const destinoRect = lightboxImg.getBoundingClientRect();
    lightboxImg.style.transition = 'none';
    lightboxImg.style.transform = transformDesdeOrigen(origenRect, destinoRect);
    lightboxImg.style.opacity = '0.5';
    // Forzar que el navegador aplique lo de arriba ANTES de animar a su lugar final.
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        lightboxImg.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
        lightboxImg.style.transform = 'translate(0, 0) scale(1, 1)';
        lightboxImg.style.opacity = '1';
      });
    });
  };

  const abrirLightbox = (boton, src, nombre) => {
    origenRect = boton.getBoundingClientRect();
    lightboxDownload.href = src;
    lightboxOverlay.hidden = false;
    document.body.style.overflow = 'hidden';

    if (lightboxImg.src.endsWith(src) && lightboxImg.complete) {
      animarDesdeOrigen();
    } else {
      lightboxImg.src = src;
      lightboxImg.alt = nombre || '';
      lightboxImg.onload = animarDesdeOrigen;
    }
  };

  const cerrarLightbox = () => {
    if (origenRect) {
      const actualRect = lightboxImg.getBoundingClientRect();
      lightboxImg.style.transition = 'transform 0.22s ease, opacity 0.22s ease';
      lightboxImg.style.transform = transformDesdeOrigen(origenRect, actualRect);
      lightboxImg.style.opacity = '0';
    }
    setTimeout(() => {
      lightboxOverlay.hidden = true;
      lightboxImg.src = '';
      lightboxImg.style.transition = '';
      lightboxImg.style.transform = '';
      lightboxImg.style.opacity = '';
      document.body.style.overflow = '';
    }, origenRect ? 220 : 0);
  };

  document.querySelectorAll('[data-lightbox-src]').forEach((boton) => {
    boton.addEventListener('click', () => {
      abrirLightbox(boton, boton.dataset.lightboxSrc, boton.dataset.lightboxNombre);
    });
  });

  lightboxClose.addEventListener('click', cerrarLightbox);

  // Cerrar al hacer clic afuera de la imagen (pero no al hacer clic en la imagen o en descargar)
  lightboxOverlay.addEventListener('click', (event) => {
    if (event.target === lightboxOverlay) cerrarLightbox();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !lightboxOverlay.hidden) cerrarLightbox();
  });
}
