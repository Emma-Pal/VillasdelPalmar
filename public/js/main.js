// ===== Header: fondo sólido al hacer scroll =====
const header = document.getElementById('header');

const updateHeader = () => {
  header.classList.toggle('scrolled', window.scrollY > 40);
};
updateHeader();
window.addEventListener('scroll', updateHeader);

// ===== Menú móvil =====
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');

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
const form = document.getElementById('contact-form');
const formNote = document.getElementById('form-note');

form.addEventListener('submit', (event) => {
  event.preventDefault();
  // TODO: conectar con un endpoint en Express (ej. POST /api/contacto)
  formNote.hidden = false;
  form.reset();
});

// ===== Año dinámico en el footer =====
document.getElementById('year').textContent = new Date().getFullYear();
