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

// ===== Estados de cuenta (Pompa): el sistema externo directamente da
// error si se abre desde el celular — mejor avisar antes que dejar que
// la persona llegue a esa pantalla rota. 700px es el mismo punto donde el
// resto del sitio ya considera que es una pantalla de celular. =====
const linkEstadosCuenta = document.getElementById('nav-estados-cuenta');

if (linkEstadosCuenta) {
  linkEstadosCuenta.addEventListener('click', (event) => {
    if (window.innerWidth <= 700) {
      event.preventDefault();
      alert('El sistema de Estados de cuenta solo funciona desde una computadora - Ábrelo desde una computadora para poder consultarlo.');
    }
  });
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

// ===== Panel: carrusel de la galería (fotos reales del residencial) =====
const galeriaMain = document.getElementById('galeria-main');
const galeriaSlides = window.VP_GALERIA_SLIDES;

if (galeriaMain && Array.isArray(galeriaSlides) && galeriaSlides.length > 0) {
  const galeriaImg = document.getElementById('galeria-img');
  const galeriaTag = document.getElementById('galeria-tag');
  const galeriaTitulo = document.getElementById('galeria-titulo');
  const galeriaDescripcion = document.getElementById('galeria-descripcion');
  const galeriaContador = document.getElementById('galeria-contador');
  const galeriaMiniaturas = document.querySelectorAll('.galeria-carousel-mini');
  const galeriaPrev = document.getElementById('galeria-prev');
  const galeriaNext = document.getElementById('galeria-next');

  let galeriaIndiceActual = 0;
  let galeriaTemporizador = null;

  const pad2 = (n) => String(n).padStart(2, '0');

  const mostrarSlide = (indice) => {
    galeriaIndiceActual = (indice + galeriaSlides.length) % galeriaSlides.length;
    const slide = galeriaSlides[galeriaIndiceActual];

    galeriaImg.style.opacity = '0';
    setTimeout(() => {
      galeriaImg.src = slide.url;
      galeriaImg.alt = slide.titulo;
      galeriaTag.textContent = slide.tag;
      galeriaTitulo.textContent = slide.titulo;
      galeriaDescripcion.textContent = slide.descripcion;
      galeriaImg.style.opacity = '1';
    }, 200);

    galeriaContador.textContent = `${pad2(galeriaIndiceActual + 1)} / ${pad2(galeriaSlides.length)}`;

    galeriaMiniaturas.forEach((mini, i) => {
      mini.classList.toggle('is-active', i === galeriaIndiceActual);
    });
  };

  const reiniciarAutoAvanceGaleria = () => {
    if (galeriaTemporizador) clearInterval(galeriaTemporizador);
    galeriaTemporizador = setInterval(() => mostrarSlide(galeriaIndiceActual + 1), 5000);
  };

  if (galeriaPrev) {
    galeriaPrev.addEventListener('click', () => {
      mostrarSlide(galeriaIndiceActual - 1);
      reiniciarAutoAvanceGaleria();
    });
  }
  if (galeriaNext) {
    galeriaNext.addEventListener('click', () => {
      mostrarSlide(galeriaIndiceActual + 1);
      reiniciarAutoAvanceGaleria();
    });
  }
  galeriaMiniaturas.forEach((mini, i) => {
    mini.addEventListener('click', () => {
      mostrarSlide(i);
      reiniciarAutoAvanceGaleria();
    });
  });

  reiniciarAutoAvanceGaleria();
}

// ===== Login: mostrar/ocultar la contraseña =====
const loginPasswordInput = document.getElementById('login-password');
const loginPasswordToggle = document.getElementById('login-password-toggle');

if (loginPasswordInput && loginPasswordToggle) {
  loginPasswordToggle.addEventListener('click', () => {
    const seVaAMostrar = loginPasswordInput.type === 'password';
    loginPasswordInput.type = seVaAMostrar ? 'text' : 'password';
    loginPasswordToggle.classList.toggle('is-active', seVaAMostrar);
    loginPasswordToggle.setAttribute('aria-label', seVaAMostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
  });
}

// ===== Año dinámico en el footer =====
const yearEl = document.getElementById('year');
if (yearEl) yearEl.textContent = new Date().getFullYear();

// ===== Formulario de usuario: "Cargo" solo aplica al comité, "Número de
// villa" solo a un propietario (cada uno tiene su propia cuenta) =====
const tipoSelect = document.getElementById('tipo-select');
const campoCargo = document.getElementById('campo-cargo');
const campoVilla = document.getElementById('campo-villa');

if (tipoSelect && (campoCargo || campoVilla)) {
  const actualizarCampos = () => {
    if (campoCargo) campoCargo.hidden = tipoSelect.value !== 'mesa';
    if (campoVilla) campoVilla.hidden = tipoSelect.value !== 'propietario';
  };
  actualizarCampos();
  tipoSelect.addEventListener('change', actualizarCampos);
}

// ===== Número de villa: solo dígitos, ni siquiera deja escribir una letra
// (además del patrón HTML, que solo avisa hasta enviar el formulario). Si lo
// que se tecleó o pegó traía algo que no era número, se muestra un aviso
// breve explicando por qué desapareció. =====
const villaInput = document.getElementById('villa-input');
const villaAdvertencia = document.getElementById('villa-advertencia');

if (villaInput) {
  let temporizadorAdvertenciaVilla = null;

  villaInput.addEventListener('input', () => {
    const valorEscrito = villaInput.value;
    const valorSoloNumeros = valorEscrito.replace(/\D/g, '');

    if (villaAdvertencia && valorSoloNumeros !== valorEscrito) {
      villaAdvertencia.hidden = false;
      clearTimeout(temporizadorAdvertenciaVilla);
      temporizadorAdvertenciaVilla = setTimeout(() => {
        villaAdvertencia.hidden = true;
      }, 2500);
    }

    villaInput.value = valorSoloNumeros;
  });
}

// ===== Hora de convocatoria (.hora-input): se teclea directo en vez del
// selector nativo de hora (tedioso de scrollear). Va formateando "HH:MM"
// solo mientras se escribe, nunca deja pasar de 23 en horas ni de 59 en
// minutos, y avisa cuando se intenta algo inválido (una letra, o un número
// fuera de rango). Puede haber más de uno en la página (un modal por
// convocatoria a editar + el de "Nueva convocatoria"), por eso querySelectorAll
// en vez de un id fijo. =====
document.querySelectorAll('.hora-input').forEach((horaInput) => {
  const advertencia = horaInput.parentElement.querySelector('.campo-advertencia');
  let temporizadorAdvertenciaHora = null;

  const avisar = () => {
    if (!advertencia) return;
    advertencia.hidden = false;
    clearTimeout(temporizadorAdvertenciaHora);
    temporizadorAdvertenciaHora = setTimeout(() => {
      advertencia.hidden = true;
    }, 2500);
  };

  horaInput.addEventListener('input', () => {
    const crudo = horaInput.value;
    let huboRechazo = crudo.replace(/\D/g, '').length !== crudo.replace(/:/g, '').length;

    let digitos = crudo.replace(/\D/g, '').slice(0, 4);
    let horas = digitos.slice(0, 2);
    let minutos = digitos.slice(2, 4);

    if (horas.length === 2 && parseInt(horas, 10) > 23) {
      horas = '23';
      huboRechazo = true;
    }
    if (minutos.length === 2 && parseInt(minutos, 10) > 59) {
      minutos = '59';
      huboRechazo = true;
    }

    horaInput.value = horas.length === 2 ? `${horas}:${minutos}` : horas;

    if (huboRechazo) avisar();
  });
});

// ===== Campos de fecha: que toda la casilla abra el calendario, no solo
// el iconito (el navegador por sí solo a veces solo lo abre ahí) =====
document.querySelectorAll('input[type="date"]').forEach((campo) => {
  campo.addEventListener('click', () => {
    if (typeof campo.showPicker === 'function') {
      try {
        campo.showPicker();
      } catch (error) {
        // Algunos navegadores lo bloquean si no vino de una interacción
        // directa del usuario — sin problema, el click ya cuenta como esa
        // interacción en el resto de los casos.
      }
    }
  });
});

// ===== "Concepto del documento" (modal de acta, en Asambleas): el campo de
// texto libre solo aplica cuando se elige "Otro (especificar)". Puede haber
// varios en la página (un modal por acta a editar + el de "Nueva acta"),
// por eso se busca dentro del <form> más cercano en vez de por id. =====
document.querySelectorAll('.campo-concepto-select').forEach((select) => {
  const campoOtro = select.closest('form')?.querySelector('.campo-concepto-otro');
  if (!campoOtro) return;

  const actualizarCampoOtro = () => {
    campoOtro.hidden = select.value !== '__otro__';
  };
  actualizarCampoOtro();
  select.addEventListener('change', actualizarCampoOtro);
});

// ===== Casilla de fecha "disfrazada" (.fecha-wrap): el <input type="date">
// real queda invisible (para que nunca se vea su resaltado nativo) y este
// texto propio, con los estilos del sitio, muestra el valor por encima. =====
document.querySelectorAll('.fecha-wrap').forEach((envoltura) => {
  const real = envoltura.querySelector('.fecha-real');
  const texto = envoltura.querySelector('.fecha-texto');
  if (!real || !texto) return;

  const formatearFecha = (valorIso) => {
    if (!valorIso) return texto.dataset.placeholder || 'Selecciona una fecha';
    const [anio, mes, dia] = valorIso.split('-');
    return `${dia}/${mes}/${anio}`;
  };

  const actualizarTexto = () => {
    texto.textContent = formatearFecha(real.value);
    texto.classList.toggle('fecha-texto--vacio', !real.value);
  };

  actualizarTexto();
  real.addEventListener('input', actualizarTexto);
  real.addEventListener('change', actualizarTexto);
});

// ===== Zona(s) de "arrastrar y soltar" para archivos adjuntos (formulario
// de avisos, modales de convocatoria/acta en Asambleas — puede haber varias
// en la misma página, una por modal) — el <input type="file"> real va
// dentro del <label> (así que un clic normal ya abre el selector solo);
// esto solo le suma soltar archivos arrastrados y mostrar sus nombres. Todo
// por estructura (siguiente hermano / descendiente), sin ids, para que
// funcione sin importar cuántas haya en la página. =====
document.querySelectorAll('.dropzone').forEach((dropzone) => {
  const dropzoneInput = dropzone.querySelector('input[type="file"]');
  const dropzoneFilenames = dropzone.nextElementSibling;
  if (!dropzoneInput || !dropzoneFilenames || !dropzoneFilenames.classList.contains('dropzone-filenames')) return;

  const actualizarNombres = () => {
    const nombres = Array.from(dropzoneInput.files).map((archivo) => archivo.name);
    dropzoneFilenames.textContent = nombres.join(', ');
  };

  dropzoneInput.addEventListener('change', actualizarNombres);

  ['dragenter', 'dragover'].forEach((evento) => {
    dropzone.addEventListener(evento, (event) => {
      event.preventDefault();
      dropzone.classList.add('is-dragover');
    });
  });

  ['dragleave', 'drop'].forEach((evento) => {
    dropzone.addEventListener(evento, (event) => {
      event.preventDefault();
      dropzone.classList.remove('is-dragover');
    });
  });

  dropzone.addEventListener('drop', (event) => {
    if (event.dataTransfer && event.dataTransfer.files.length > 0) {
      dropzoneInput.files = event.dataTransfer.files;
      actualizarNombres();
    }
  });
});

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
      // Si la imagen se abrió desde dentro de un modal de aviso (ver más
      // abajo), ese modal sigue abierto detrás — no quitar el scroll
      // bloqueado en ese caso.
      const sigueAbiertoOtro = document.querySelector('.aviso-modal-overlay:not([hidden])');
      if (!sigueAbiertoOtro) document.body.style.overflow = '';
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

// ===== Modal de aviso: abrir la publicación completa (tabla de /panel/avisos)
// sin salir de la página. Cada fila de la tabla tiene un modal propio, ya
// renderizado y oculto (mismo partial que antes se mostraba en la lista),
// identificado por data-modal-target. Un clic en la fila lo muestra; un
// clic en la celda de "Acción" (los archivos adjuntos) NO debe abrirlo,
// de ahí el data-no-row-click. =====
const avisoModalOverlays = document.querySelectorAll('.aviso-modal-overlay');

if (avisoModalOverlays.length > 0) {
  const abrirModal = (overlay) => {
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
  };

  const cerrarModal = (overlay) => {
    overlay.hidden = true;
    // Solo se quita el scroll bloqueado si no queda ningún otro overlay
    // abierto (ej. el lightbox de una imagen, abierto desde dentro de este
    // mismo modal).
    const sigueAbiertoOtro = document.querySelector('.aviso-modal-overlay:not([hidden]), .lightbox-overlay:not([hidden])');
    if (!sigueAbiertoOtro) document.body.style.overflow = '';
  };

  document.querySelectorAll('.tabla-fila-clic[data-modal-target]').forEach((fila) => {
    const abrirDesdeEstaFila = (event) => {
      if (event.target.closest('[data-no-row-click], a, button')) return;
      const overlay = document.getElementById(fila.dataset.modalTarget);
      if (overlay) abrirModal(overlay);
    };
    fila.addEventListener('click', abrirDesdeEstaFila);
    fila.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        abrirDesdeEstaFila(event);
      }
    });
  });

  avisoModalOverlays.forEach((overlay) => {
    overlay.querySelectorAll('[data-modal-close]').forEach((boton) => {
      boton.addEventListener('click', () => cerrarModal(overlay));
    });
    overlay.addEventListener('click', (event) => {
      if (event.target === overlay) cerrarModal(overlay);
    });
  });

  // Disparador genérico: cualquier botón con data-modal-open="id-del-modal"
  // lo abre directamente (ej. "+ Nueva convocatoria", "Editar" en una fila
  // de Asambleas) — el modal destino ya vive renderizado y oculto en la
  // página, igual que los de arriba.
  document.querySelectorAll('[data-modal-open]').forEach((boton) => {
    boton.addEventListener('click', () => {
      const overlay = document.getElementById(boton.dataset.modalOpen);
      if (overlay) abrirModal(overlay);
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const overlayAbierto = document.querySelector('.aviso-modal-overlay:not([hidden])');
    if (overlayAbierto) cerrarModal(overlayAbierto);
  });

  // Deep link desde "Últimos avisos" del Panel (/panel/avisos#aviso-42):
  // antes llevaba a una tarjeta visible en la lista; ahora esa tarjeta vive
  // dentro de un modal oculto, así que hay que abrirlo directamente.
  if (location.hash.startsWith('#aviso-')) {
    const tarjeta = document.querySelector(location.hash);
    const overlay = tarjeta ? tarjeta.closest('.aviso-modal-overlay') : null;
    if (overlay) abrirModal(overlay);
  }
}

// ===== Desplegable "Archivos (N)" de la columna Acción, cuando un aviso
// tiene más de un archivo adjunto (con uno solo, el botón ya es un link de
// descarga directa y no necesita nada de esto). =====
document.querySelectorAll('[data-archivos-toggle]').forEach((boton) => {
  const menu = boton.nextElementSibling;
  if (!menu) return;

  boton.addEventListener('click', (event) => {
    event.stopPropagation();
    const yaAbierto = !menu.hidden;
    // Cerrar cualquier otro desplegable de archivos que haya quedado abierto.
    document.querySelectorAll('.archivos-dropdown-menu').forEach((m) => { m.hidden = true; });
    menu.hidden = yaAbierto;
  });
});

document.addEventListener('click', () => {
  document.querySelectorAll('.archivos-dropdown-menu').forEach((m) => { m.hidden = true; });
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.archivos-dropdown-menu').forEach((m) => { m.hidden = true; });
  }
});
