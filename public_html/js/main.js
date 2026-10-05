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

// ===== Campos "solo dígitos" (.solo-digitos): ni siquiera deja escribir una
// letra (además del patrón HTML, que solo avisa hasta enviar el formulario).
// Puede haber varios en la página (ej. "Villa núm." en varios modales de
// Usuarios), por eso es por clase y no por id — cada uno busca su propio
// aviso ".campo-advertencia" hermano dentro del mismo <label>. =====
document.querySelectorAll('.solo-digitos').forEach((campo) => {
  const advertencia = campo.parentElement.querySelector('.campo-advertencia');
  let temporizadorAdvertencia = null;

  campo.addEventListener('input', () => {
    const valorEscrito = campo.value;
    const valorSoloNumeros = valorEscrito.replace(/\D/g, '');

    if (advertencia && valorSoloNumeros !== valorEscrito) {
      advertencia.hidden = false;
      clearTimeout(temporizadorAdvertencia);
      temporizadorAdvertencia = setTimeout(() => {
        advertencia.hidden = true;
      }, 2500);
    }

    campo.value = valorSoloNumeros;
  });
});

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
// de avisos, modales de convocatoria/acta en Asambleas, titulares de una
// villa en Usuarios — puede haber varias en la misma página) — el <input
// type="file"> real va dentro del <label> (así que un clic normal ya abre
// el selector solo); esto solo le suma soltar archivos arrastrados y
// mostrar sus nombres. Todo por estructura (siguiente hermano /
// descendiente), sin ids, para que funcione sin importar cuántas haya en la
// página. inicializarDropzone() se expone aparte porque los bloques de
// titular que se agregan dinámicamente (ver más abajo) necesitan llamarla
// de nuevo sobre el nodo recién clonado. =====
function inicializarDropzone(dropzone) {
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
}

document.querySelectorAll('.dropzone').forEach(inicializarDropzone);

// ===== Bloques repetibles genéricos (titulares de una villa en Usuarios;
// acompañantes y vehículos de un Registro de estancia): "+ Agregar..." clona
// la <template> del bloque, renumerando sus name="campo_0" a "_N" (0 es
// solo el marcador de la plantilla) y activando su dropzone (si trae) y su
// botón de quitar. Puede haber más de un data-repetible-wrap en la misma
// página/modal (ej. acompañantes Y vehículos en el mismo formulario), cada
// uno con su propia lista/plantilla/botón — por eso todo se busca relativo
// a "wrap", nunca por id. El primer bloque de una lista (ej. Titular 1, que
// siempre es el propietario) puede no traer data-repetible-quitar — eso ya
// lo decide el PHP que lo imprime, no este JS. =====
document.querySelectorAll('[data-repetible-wrap]').forEach((wrap) => {
  const lista = wrap.querySelector('[data-repetible-lista]');
  const plantilla = wrap.querySelector('template[data-repetible-template]');
  const botonAgregar = wrap.querySelector('[data-repetible-agregar]');
  if (!lista || !plantilla || !botonAgregar) return;

  const prefijoNumero = wrap.dataset.repetiblePrefijo || '';

  let contador = lista.querySelectorAll('.bloque-repetible').length;

  const activarQuitar = (bloque) => {
    const boton = bloque.querySelector('[data-repetible-quitar]');
    if (boton) boton.addEventListener('click', () => bloque.remove());
  };

  lista.querySelectorAll('.bloque-repetible').forEach(activarQuitar);

  botonAgregar.addEventListener('click', () => {
    contador++;
    const fragmento = plantilla.content.cloneNode(true);
    fragmento.querySelectorAll('[name]').forEach((campo) => {
      campo.name = campo.name.replace(/_0$/, '_' + contador);
    });
    fragmento.querySelectorAll('[data-repetible-numero]').forEach((span) => {
      span.textContent = prefijoNumero + ' ' + contador;
    });
    const bloque = fragmento.querySelector('.bloque-repetible');
    lista.appendChild(bloque);
    bloque.querySelectorAll('.dropzone').forEach(inicializarDropzone);
    activarQuitar(bloque);
  });
});

// ===== Botón "Generar" de la contraseña asignada a una villa (/panel/usuarios):
// rellena el campo con "Palmar-{villa}-XXXX" (4 caracteres alfanuméricos al
// azar) — el propietario la cambia en su primer ingreso, así que no hace
// falta que sea memorable, solo fácil de dictar/copiar una vez. =====
document.querySelectorAll('[data-generar-password]').forEach((boton) => {
  boton.addEventListener('click', () => {
    const campoPassword = document.getElementById(boton.dataset.passwordTarget);
    const campoVilla = document.getElementById(boton.dataset.villaTarget);
    if (!campoPassword) return;

    const alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let sufijo = '';
    for (let i = 0; i < 4; i++) {
      sufijo += alfabeto[Math.floor(Math.random() * alfabeto.length)];
    }
    const villa = (campoVilla && campoVilla.value.trim()) || '0';
    campoPassword.value = `Palmar-${villa}-${sufijo}`;
  });
});

// ===== Recámaras y capacidad (modal de villa en Usuarios) =====
// - Advertencia inmediata si hay más recámaras físicas que registradas
//   (el mismo criterio se vuelve a validar en el servidor).
// - Capacidad de ocupación: ya no se captura a mano, se calcula sola según
//   las recámaras registradas (tabla fija que dio Emmanuel). El <input>
//   real es hidden; esto solo lo mantiene sincronizado con lo que se ve.
//   Misma tabla que capacidadPorRecamaras() en app/funciones.php — si una
//   cambia, la otra también.
function capacidadPorRecamaras(recamaras) {
  const tabla = { 0: 3, 1: 4, 2: 7, 3: 10 };
  if (recamaras in tabla) return tabla[recamaras];
  if (recamaras > 3) return 10 + (recamaras - 3) * 3;
  return 3;
}

document.querySelectorAll('[data-recamaras-registradas]').forEach((campoRegistradas) => {
  const tarjeta = campoRegistradas.closest('.form-card');
  const campoFisicas = tarjeta ? tarjeta.querySelector('[data-recamaras-fisicas]') : null;
  const advertencia = tarjeta ? tarjeta.querySelector('[data-recamaras-advertencia]') : null;
  const capacidadValor = tarjeta ? tarjeta.querySelector('[data-capacidad-valor]') : null;
  const capacidadTexto = tarjeta ? tarjeta.querySelector('[data-capacidad-texto]') : null;
  if (!campoFisicas || !advertencia) return;

  const revisarDiferencia = () => {
    const registradas = parseInt(campoRegistradas.value, 10) || 0;
    const fisicas = parseInt(campoFisicas.value, 10) || 0;
    advertencia.hidden = fisicas <= registradas;
  };

  const actualizarCapacidad = () => {
    if (!capacidadValor || !capacidadTexto) return;
    const registradas = parseInt(campoRegistradas.value, 10) || 0;
    const capacidad = capacidadPorRecamaras(registradas);
    capacidadValor.value = capacidad;
    capacidadTexto.textContent = capacidad + ' ocupantes';
  };

  campoRegistradas.addEventListener('input', () => {
    revisarDiferencia();
    actualizarCapacidad();
  });
  campoFisicas.addEventListener('input', revisarDiferencia);

  revisarDiferencia();
  actualizarCapacidad();
});

// ===== Registro de estancia (/panel/registro-estancia): steppers +/-,
// ocupación en vivo (equivalentes de adulto/menor), cuota por excedente y
// mostrar/ocultar los bloques de vehículos/mascota. Mismo criterio de
// reparto que calcularOcupacionEstancia() en app/repos/estancias.php — si
// uno cambia, el otro también. =====
function ocupacionEquivalenteEstancia(capacidad, adultos, menores) {
  let adultosExcedentes = 0;
  let menoresExcedentes = 0;
  if (adultos > capacidad) {
    adultosExcedentes = adultos - capacidad;
    menoresExcedentes = menores;
  } else {
    const libres = capacidad - adultos;
    menoresExcedentes = Math.max(0, menores - Math.floor(libres * 2));
  }
  return { ocupacionEquivalente: adultos + menores * 0.5, adultosExcedentes, menoresExcedentes };
}

function nochesEntreFechasEstancia(fechaLlegada, fechaSalida) {
  if (!fechaLlegada || !fechaSalida) return 1;
  const llegada = new Date(fechaLlegada + 'T00:00:00');
  const salida = new Date(fechaSalida + 'T00:00:00');
  const noches = Math.round((salida - llegada) / 86400000);
  return Math.max(1, noches);
}

document.querySelectorAll('[data-ocupacion-adultos]').forEach((campoAdultos) => {
  const form = campoAdultos.closest('form');
  const resultado = form ? form.querySelector('[data-ocupacion-resultado]') : null;
  const campoMenores = form ? form.querySelector('[data-ocupacion-menores]') : null;
  const campoInfantes = form ? form.querySelector('[data-ocupacion-infantes]') : null;
  if (!form || !resultado || !campoMenores || !campoInfantes) return;

  const campoLlegada = form.querySelector('[data-fecha-llegada]');
  const campoSalida = form.querySelector('[data-fecha-salida]');
  const villaSelect = form.querySelector('[data-villa-select]');
  const villaFija = form.querySelector('[data-villa-capacidad-fija]');
  const textoEl = resultado.querySelector('[data-ocupacion-texto]');
  const detalleEl = resultado.querySelector('[data-ocupacion-detalle]');
  const barraDentro = resultado.querySelector('[data-ocupacion-barra-dentro]');
  const barraExcedente = resultado.querySelector('[data-ocupacion-barra-excedente]');
  const notaEl = resultado.querySelector('[data-ocupacion-nota]');
  const excedenteBloque = form.querySelector('[data-excedente-bloque]');
  const excedenteDetalle = form.querySelector('[data-excedente-detalle]');
  const excedenteCheckbox = form.querySelector('[data-excedente-checkbox]');
  const excedenteTextoCheckbox = form.querySelector('[data-excedente-texto-checkbox]');

  const capacidadActual = () => {
    if (villaSelect) {
      const opcion = villaSelect.selectedOptions[0];
      return opcion ? parseInt(opcion.dataset.capacidad, 10) || 0 : 0;
    }
    if (villaFija) return parseInt(villaFija.dataset.villaCapacidadFija, 10) || 0;
    return 0;
  };

  const recalcular = () => {
    const adultos = parseInt(campoAdultos.value, 10) || 0;
    const menores = parseInt(campoMenores.value, 10) || 0;
    const infantes = parseInt(campoInfantes.value, 10) || 0;
    const capacidad = capacidadActual();
    const { ocupacionEquivalente, adultosExcedentes, menoresExcedentes } = ocupacionEquivalenteEstancia(capacidad, adultos, menores);

    if (textoEl) textoEl.textContent = `Ocupación: ${ocupacionEquivalente} de ${capacidad} equivalentes`;
    if (detalleEl) detalleEl.textContent = `${adultos + menores + infantes} personas · ${Math.max(0, adultos - 1) + menores + infantes} acompañantes`;

    if (barraDentro && barraExcedente) {
      const porcentajeDentro = capacidad > 0 ? Math.min(100, (ocupacionEquivalente / capacidad) * 100) : 0;
      const porcentajeExcedente = capacidad > 0 && ocupacionEquivalente > capacidad ? Math.min(100 - porcentajeDentro, ((ocupacionEquivalente - capacidad) / capacidad) * 100) : 0;
      barraDentro.style.width = porcentajeDentro + '%';
      barraExcedente.style.width = porcentajeExcedente + '%';
    }

    const hayExcedente = adultosExcedentes > 0 || menoresExcedentes > 0;
    if (notaEl) {
      const libres = capacidad - ocupacionEquivalente;
      notaEl.textContent = !hayExcedente && libres > 0 ? `Aún caben ${libres}.` : '';
    }

    if (excedenteBloque) {
      excedenteBloque.hidden = !hayExcedente;
      if (hayExcedente) {
        const noches = nochesEntreFechasEstancia(campoLlegada ? campoLlegada.value : '', campoSalida ? campoSalida.value : '');
        const cuota = adultosExcedentes * 150 * noches + menoresExcedentes * 75 * noches;
        if (excedenteDetalle) {
          excedenteDetalle.textContent = `Adultos excedentes: ${adultosExcedentes} × $150.00 × ${noches} noche(s). Menores excedentes: ${menoresExcedentes} × $75.00 × ${noches} noche(s).`;
        }
        if (excedenteTextoCheckbox) {
          excedenteTextoCheckbox.textContent = `Acepto el excedente de ocupación indicado y me comprometo a pagar la cuota de $${cuota.toFixed(2)} MXN a la Administración. *`;
        }
        if (excedenteCheckbox) excedenteCheckbox.required = true;
      } else if (excedenteCheckbox) {
        excedenteCheckbox.required = false;
        excedenteCheckbox.checked = false;
      }
    }
  };

  campoAdultos.addEventListener('input', recalcular);
  campoMenores.addEventListener('input', recalcular);
  campoInfantes.addEventListener('input', recalcular);
  if (campoLlegada) campoLlegada.addEventListener('input', recalcular);
  if (campoSalida) campoSalida.addEventListener('input', recalcular);
  if (villaSelect) villaSelect.addEventListener('change', recalcular);

  recalcular();
});

// Steppers (+/-) de los campos numéricos de ocupantes — el <input> se deja
// "readonly" (nunca de tipo oculto, porque sigue siendo el valor real que
// se manda al servidor) para que solo se cambie con los botones, nunca
// tecleando directo, y así nunca quede en blanco o con texto raro.
document.querySelectorAll('.campo-stepper-control').forEach((control) => {
  const input = control.querySelector('input[type="number"]');
  const menos = control.querySelector('[data-stepper-menos]');
  const mas = control.querySelector('[data-stepper-mas]');
  if (!input) return;

  const disparar = () => input.dispatchEvent(new Event('input', { bubbles: true }));

  if (menos) {
    menos.addEventListener('click', () => {
      const min = parseInt(input.min, 10) || 0;
      input.value = Math.max(min, (parseInt(input.value, 10) || 0) - 1);
      disparar();
    });
  }
  if (mas) {
    mas.addEventListener('click', () => {
      const max = parseInt(input.max, 10) || 99;
      input.value = Math.min(max, (parseInt(input.value, 10) || 0) + 1);
      disparar();
    });
  }
});

// ===== Mostrar/ocultar un bloque según un grupo de pill-radio (Vehículos /
// Mascota en Registro de estancia). A propósito usa display directo (no el
// atributo hidden): algunos de estos bloques ya traen su propio display
// (ej. .form-row-flex) y un hidden de atributo pierde contra eso — mismo
// motivo que .candado-aviso[hidden] más arriba, pero aquí es más simple
// evitarlo del todo que ir agregando una excepción de CSS por cada bloque. =====
document.querySelectorAll('[data-toggle-bloque]').forEach((radio) => {
  const bloque = document.getElementById(radio.dataset.toggleBloque);
  if (!bloque) return;
  const displayVisible = bloque.dataset.mostrarDisplay || 'block';

  const actualizar = () => {
    if (radio.checked) bloque.style.display = radio.value === 'si' ? displayVisible : 'none';
  };
  radio.addEventListener('change', actualizar);
  actualizar();
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
