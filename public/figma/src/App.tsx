import { useState, useEffect, useCallback } from 'react'

type Page = 'panel' | 'noticias' | 'avisos' | 'comite' | 'usuarios'

type Notice = {
  id: number
  type: 'AVISO' | 'NOTICIA' | 'ESTADO FINANCIERO' | 'COMUNICADO'
  title: string
  excerpt: string
  author: string
  role: string
  date: string
  pinned?: boolean
}

const notices: Notice[] = [
  {
    id: 1,
    type: 'AVISO',
    title: 'Actualización del calendario académico 2026-II',
    excerpt: 'Se comunica a docentes y estudiantes que el inicio del ciclo académico ha sido reprogramado para el 5 de octubre.',
    author: 'Dr. Martínez Quispe',
    role: 'Director de Departamento',
    date: '2026-09-22',
    pinned: true,
  },
  {
    id: 2,
    type: 'COMUNICADO',
    title: 'Resultados de evaluación docente — Ciclo 2026-I',
    excerpt: 'Los resultados de la evaluación de desempeño docente del presente ciclo ya se encuentran disponibles en el portal.',
    author: 'Comité Evaluador',
    role: 'Presidente',
    date: '2026-09-20',
  },
  {
    id: 3,
    type: 'NOTICIA',
    title: 'Premio nacional de investigación otorgado al departamento',
    excerpt: 'El proyecto "Optimización de redes distribuidas en entornos universitarios" recibió el primer lugar en la categoría tecnología.',
    author: 'Dra. Villanueva Torres',
    role: 'Coordinadora de Investigación',
    date: '2026-09-18',
  },
  {
    id: 4,
    type: 'ESTADO FINANCIERO',
    title: 'Informe presupuestal — Tercer trimestre 2026',
    excerpt: 'Resumen de ejecución presupuestal correspondiente al período julio-septiembre del presente año fiscal.',
    author: 'Ing. Ramos Flores',
    role: 'Administrador',
    date: '2026-09-15',
  },
]

const typeColors: Record<string, { bg: string; text: string }> = {
  AVISO: { bg: '#fef3c7', text: '#92400e' },
  NOTICIA: { bg: '#dbeafe', text: '#1e3a5f' },
  'ESTADO FINANCIERO': { bg: '#e5e7eb', text: '#374151' },
  COMUNICADO: { bg: '#ede9fe', text: '#4c1d95' },
}

type Slide = {
  id: number
  url: string
  title: string
  caption: string
  tag: string
}

const slides: Slide[] = [
  {
    id: 1,
    url: 'https://images.unsplash.com/photo-1576495199011-eb94736d05d6?w=1400&h=600&fit=crop&auto=format',
    title: 'Campus Universitario — Sede Central',
    caption: 'Vista de las instalaciones principales del campus durante el inicio del ciclo académico 2026-II.',
    tag: 'Instalaciones',
  },
  {
    id: 2,
    url: 'https://images.unsplash.com/photo-1766297247924-6638d54e7c89?w=1400&h=600&fit=crop&auto=format',
    title: 'Laboratorio de Investigación en Sistemas',
    caption: 'El equipo de investigación trabaja en el proyecto de redes distribuidas ganador del premio nacional 2026.',
    tag: 'Investigación',
  },
  {
    id: 3,
    url: 'https://images.unsplash.com/photo-1605781573960-d12a0d37b2d4?w=1400&h=600&fit=crop&auto=format',
    title: 'Aulas Renovadas — Bloque C',
    caption: 'Las nuevas aulas del bloque C cuentan con equipamiento audiovisual de última generación para clases híbridas.',
    tag: 'Infraestructura',
  },
  {
    id: 4,
    url: 'https://images.unsplash.com/photo-1591123120675-6f7f1aae0e5b?w=1400&h=600&fit=crop&auto=format',
    title: 'Biblioteca Académica Central',
    caption: 'Fondo bibliográfico ampliado con más de 2,400 nuevos títulos disponibles para préstamo y consulta digital.',
    tag: 'Biblioteca',
  },
  {
    id: 5,
    url: 'https://images.unsplash.com/photo-1622470190232-81df3782484b?w=1400&h=600&fit=crop&auto=format',
    title: 'Patio Central — Área de Estudio Exterior',
    caption: 'Espacio de encuentro académico disponible para actividades extracurriculares y proyectos colaborativos.',
    tag: 'Campus',
  },
]

function Carousel() {
  const [current, setCurrent] = useState(0)
  const [transitioning, setTransitioning] = useState(false)

  const go = useCallback(
    (next: number) => {
      if (transitioning) return
      setTransitioning(true)
      setTimeout(() => {
        setCurrent((next + slides.length) % slides.length)
        setTransitioning(false)
      }, 250)
    },
    [transitioning]
  )

  useEffect(() => {
    const t = setInterval(() => go(current + 1), 5000)
    return () => clearInterval(t)
  }, [current, go])

  const slide = slides[current]

  return (
    <div className="mb-16">
      <div className="flex items-end justify-between mb-5">
        <div>
          <p className="text-[10px] tracking-[0.3em] uppercase mb-1" style={{ color: '#c8a96e' }}>
            Galería
          </p>
          <h2
            className="text-3xl"
            style={{ fontFamily: 'var(--font-display)', color: '#1a1e2e', fontWeight: 300 }}
          >
            Imágenes del departamento
          </h2>
        </div>
        {/* Slide counter */}
        <p className="text-xs tabular-nums pb-1" style={{ color: '#8494b0', fontFamily: 'var(--font-body)' }}>
          {String(current + 1).padStart(2, '0')} / {String(slides.length).padStart(2, '0')}
        </p>
      </div>

      {/* Main image */}
      <div
        className="relative overflow-hidden"
        style={{ height: 420, backgroundColor: '#1a1e2e' }}
      >
        <img
          key={slide.id}
          src={slide.url}
          alt={slide.title}
          className="w-full h-full object-cover transition-opacity duration-500"
          style={{ opacity: transitioning ? 0 : 1 }}
        />

        {/* Overlay gradient */}
        <div
          className="absolute inset-0"
          style={{
            background: 'linear-gradient(to top, rgba(14,21,35,0.85) 0%, rgba(14,21,35,0.1) 60%, transparent 100%)',
          }}
        />

        {/* Caption */}
        <div
          className="absolute bottom-0 left-0 right-0 px-8 py-7"
          style={{ transition: 'opacity 0.4s', opacity: transitioning ? 0 : 1 }}
        >
          <span
            className="text-[10px] tracking-[0.25em] uppercase px-2 py-1 mb-3 inline-block"
            style={{ backgroundColor: '#c8a96e', color: '#0e1523', fontFamily: 'var(--font-body)', fontWeight: 500 }}
          >
            {slide.tag}
          </span>
          <h3
            className="text-xl mb-1"
            style={{ fontFamily: 'var(--font-display)', color: '#f7f6f3', fontWeight: 400 }}
          >
            {slide.title}
          </h3>
          <p className="text-sm max-w-xl" style={{ color: 'rgba(247,246,243,0.65)', fontFamily: 'var(--font-body)' }}>
            {slide.caption}
          </p>
        </div>

        {/* Prev / Next */}
        <button
          onClick={() => go(current - 1)}
          className="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center transition-all duration-150"
          style={{
            backgroundColor: 'rgba(14,21,35,0.6)',
            border: '1px solid rgba(200,169,110,0.3)',
            color: '#f7f6f3',
            cursor: 'pointer',
          }}
          onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = 'rgba(200,169,110,0.25)')}
          onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = 'rgba(14,21,35,0.6)')}
          aria-label="Anterior"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <polyline points="15 18 9 12 15 6" />
          </svg>
        </button>
        <button
          onClick={() => go(current + 1)}
          className="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center transition-all duration-150"
          style={{
            backgroundColor: 'rgba(14,21,35,0.6)',
            border: '1px solid rgba(200,169,110,0.3)',
            color: '#f7f6f3',
            cursor: 'pointer',
          }}
          onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = 'rgba(200,169,110,0.25)')}
          onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = 'rgba(14,21,35,0.6)')}
          aria-label="Siguiente"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5">
            <polyline points="9 18 15 12 9 6" />
          </svg>
        </button>
      </div>

      {/* Thumbnails */}
      <div className="flex gap-2 mt-2">
        {slides.map((s, i) => (
          <button
            key={s.id}
            onClick={() => go(i)}
            className="flex-1 relative overflow-hidden transition-all duration-200"
            style={{
              height: 60,
              backgroundColor: '#1a1e2e',
              border: i === current ? '2px solid #c8a96e' : '2px solid transparent',
              cursor: 'pointer',
              padding: 0,
            }}
            aria-label={`Ir a imagen ${i + 1}`}
          >
            <img
              src={s.url.replace('w=1400&h=600', 'w=200&h=80')}
              alt={s.title}
              className="w-full h-full object-cover"
              style={{ opacity: i === current ? 1 : 0.45, transition: 'opacity 0.2s' }}
            />
          </button>
        ))}
      </div>
    </div>
  )
}

function NavBar({ current, onNav }: { current: Page; onNav: (p: Page) => void }) {
  const links: { id: Page; label: string }[] = [
    { id: 'panel', label: 'Panel' },
    { id: 'noticias', label: 'Noticias' },
    { id: 'avisos', label: 'Avisos' },
    { id: 'comite', label: 'Comité' },
    { id: 'usuarios', label: 'Usuarios' },
  ]

  return (
    <nav
      className="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-8 h-14"
      style={{ backgroundColor: '#0e1523', borderBottom: '1px solid #1e2d44' }}
    >
      {/* Logo */}
      <button
        onClick={() => onNav('panel')}
        className="flex items-center gap-3 shrink-0"
        style={{ background: 'none', border: 'none', cursor: 'pointer' }}
      >
        <div
          className="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold"
          style={{
            backgroundColor: '#c8a96e',
            color: '#0e1523',
            fontFamily: 'var(--font-display)',
          }}
        >
          DS
        </div>
        <span
          className="text-sm hidden sm:block"
          style={{ color: '#f7f6f3', fontFamily: 'var(--font-display)', fontWeight: 400 }}
        >
          Dep. Sistemas
        </span>
      </button>

      {/* Nav links */}
      <div className="hidden md:flex items-center gap-1">
        {links.map(({ id, label }) => (
          <button
            key={id}
            onClick={() => onNav(id)}
            className="px-4 py-1.5 text-xs tracking-wider uppercase transition-all duration-150"
            style={{
              fontFamily: 'var(--font-body)',
              fontWeight: 500,
              color: current === id ? '#c8a96e' : '#8494b0',
              background: current === id ? 'rgba(200,169,110,0.08)' : 'none',
              border: 'none',
              cursor: 'pointer',
              borderBottom: current === id ? '1px solid #c8a96e' : '1px solid transparent',
            }}
          >
            {label}
          </button>
        ))}
      </div>

      {/* User */}
      <div className="flex items-center gap-3">
        <div className="text-right hidden sm:block">
          <p className="text-xs" style={{ color: '#f7f6f3', fontFamily: 'var(--font-body)' }}>
            Administrador
          </p>
          <p className="text-[10px]" style={{ color: '#8494b0' }}>
            Director
          </p>
        </div>
        <div
          className="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold"
          style={{ backgroundColor: '#1e2d44', color: '#c8a96e', fontFamily: 'var(--font-display)' }}
        >
          A
        </div>
        <button
          className="hidden sm:block text-xs px-3 py-1.5 transition-colors duration-150"
          style={{
            color: '#8494b0',
            border: '1px solid #1e2d44',
            background: 'none',
            cursor: 'pointer',
            fontFamily: 'var(--font-body)',
            borderRadius: 0,
          }}
          onMouseEnter={(e) => {
            e.currentTarget.style.color = '#f7f6f3'
            e.currentTarget.style.borderColor = '#8494b0'
          }}
          onMouseLeave={(e) => {
            e.currentTarget.style.color = '#8494b0'
            e.currentTarget.style.borderColor = '#1e2d44'
          }}
        >
          Cerrar sesión
        </button>
      </div>
    </nav>
  )
}

function PanelPage({ onNav }: { onNav: (p: Page) => void }) {
  const total = notices.length
  const pinned = notices.filter((n) => n.pinned).length

  return (
    <div style={{ fontFamily: 'var(--font-body)' }}>
      {/* Hero */}
      <div
        className="relative overflow-hidden"
        style={{ backgroundColor: '#0e1523', minHeight: 180 }}
      >
        {/* Grid texture */}
        <div
          className="absolute inset-0 pointer-events-none opacity-[0.04]"
          style={{
            backgroundImage:
              'linear-gradient(#c8a96e 1px, transparent 1px), linear-gradient(90deg, #c8a96e 1px, transparent 1px)',
            backgroundSize: '40px 40px',
          }}
        />
        {/* Diagonal accent */}
        <div
          className="absolute right-0 top-0 bottom-0 w-64 opacity-20"
          style={{
            background: 'linear-gradient(135deg, transparent 40%, #c8a96e 100%)',
          }}
        />
        <div className="relative z-10 max-w-5xl mx-auto px-8 py-12">
          <p
            className="text-xs tracking-[0.3em] uppercase mb-2"
            style={{ color: '#c8a96e' }}
          >
            Bienvenido, Administrador
          </p>
          <h1
            className="text-4xl md:text-5xl"
            style={{
              fontFamily: 'var(--font-display)',
              color: '#f7f6f3',
              fontWeight: 300,
            }}
          >
            Panel <em style={{ fontStyle: 'italic', color: '#c8a96e' }}>Principal</em>
          </h1>
        </div>
      </div>

      {/* Content */}
      <div className="max-w-5xl mx-auto px-8 py-12">
        {/* Action cards */}
        <div className="grid md:grid-cols-2 gap-5 mb-16">
          {/* Card 1 — resumen */}
          <div
            className="p-8 flex flex-col justify-between"
            style={{
              backgroundColor: '#f0ede6',
              border: '1px solid #d8d5ce',
            }}
          >
            <div>
              <p
                className="text-[10px] tracking-[0.3em] uppercase mb-4"
                style={{ color: '#c8a96e' }}
              >
                Comunicación
              </p>
              <h2
                className="text-2xl mb-3"
                style={{ fontFamily: 'var(--font-display)', color: '#1a1e2e', fontWeight: 400 }}
              >
                {pinned > 0 ? `${pinned} aviso${pinned > 1 ? 's' : ''} destacado${pinned > 1 ? 's' : ''}` : 'Estás al día'}
              </h2>
              <p className="text-sm leading-relaxed mb-6" style={{ color: '#8494b0' }}>
                {total} publicaciones en total entre noticias, comunicados y avisos institucionales.
              </p>
            </div>
            <button
              onClick={() => onNav('avisos')}
              className="self-start px-6 py-2.5 text-xs tracking-widest uppercase transition-all duration-150"
              style={{
                backgroundColor: '#0e1523',
                color: '#f7f6f3',
                border: 'none',
                cursor: 'pointer',
                fontFamily: 'var(--font-body)',
                fontWeight: 500,
                borderRadius: 0,
              }}
              onMouseEnter={(e) => (e.currentTarget.style.backgroundColor = '#c8a96e')}
              onMouseLeave={(e) => (e.currentTarget.style.backgroundColor = '#0e1523')}
            >
              Ver avisos
            </button>
          </div>

          {/* Card 2 — nueva publicación */}
          <div
            className="p-8 flex flex-col justify-between"
            style={{
              backgroundColor: '#f7f6f3',
              border: '1px solid #d8d5ce',
            }}
          >
            <div>
              <p
                className="text-[10px] tracking-[0.3em] uppercase mb-4"
                style={{ color: '#8494b0' }}
              >
                Gestión de contenido
              </p>
              <h2
                className="text-2xl mb-3"
                style={{ fontFamily: 'var(--font-display)', color: '#1a1e2e', fontWeight: 400 }}
              >
                Publicar algo nuevo
              </h2>
              <p className="text-sm leading-relaxed mb-6" style={{ color: '#8494b0' }}>
                Redacta una noticia, un comunicado oficial, un estado financiero o un aviso general.
              </p>
            </div>
            <button
              onClick={() => onNav('noticias')}
              className="self-start px-6 py-2.5 text-xs tracking-widest uppercase transition-all duration-150"
              style={{
                backgroundColor: 'transparent',
                color: '#1a1e2e',
                border: '1px solid #1a1e2e',
                cursor: 'pointer',
                fontFamily: 'var(--font-body)',
                fontWeight: 500,
                borderRadius: 0,
              }}
              onMouseEnter={(e) => {
                e.currentTarget.style.backgroundColor = '#0e1523'
                e.currentTarget.style.color = '#f7f6f3'
              }}
              onMouseLeave={(e) => {
                e.currentTarget.style.backgroundColor = 'transparent'
                e.currentTarget.style.color = '#1a1e2e'
              }}
            >
              Nueva publicación
            </button>
          </div>
        </div>

        {/* Accesos rápidos */}
        <div className="mb-14">
          <p className="text-[10px] tracking-[0.3em] uppercase mb-4" style={{ color: '#8494b0' }}>
            Accesos rápidos
          </p>
          <div className="flex flex-wrap gap-3">
            {(
              [
                { id: 'noticias', label: 'Noticias', icon: '📰' },
                { id: 'avisos', label: 'Avisos', icon: '📌' },
                { id: 'comite', label: 'Comité', icon: '👥' },
                { id: 'usuarios', label: 'Usuarios', icon: '🔐' },
              ] as { id: Page; label: string; icon: string }[]
            ).map(({ id, label, icon }) => (
              <button
                key={id}
                onClick={() => onNav(id)}
                className="flex items-center gap-2 px-5 py-3 text-xs tracking-wider uppercase transition-all duration-150 group"
                style={{
                  backgroundColor: '#f0ede6',
                  color: '#1a1e2e',
                  border: '1px solid #d8d5ce',
                  cursor: 'pointer',
                  fontFamily: 'var(--font-body)',
                  fontWeight: 500,
                  borderRadius: 0,
                }}
                onMouseEnter={(e) => {
                  e.currentTarget.style.borderColor = '#c8a96e'
                  e.currentTarget.style.color = '#c8a96e'
                }}
                onMouseLeave={(e) => {
                  e.currentTarget.style.borderColor = '#d8d5ce'
                  e.currentTarget.style.color = '#1a1e2e'
                }}
              >
                <span style={{ fontSize: 14 }}>{icon}</span>
                {label}
              </button>
            ))}
          </div>
        </div>

        {/* Gallery carousel */}
        <Carousel />

        {/* Latest notices */}
        <div>
          <div className="flex items-end justify-between mb-8">
            <div>
              <p
                className="text-[10px] tracking-[0.3em] uppercase mb-1"
                style={{ color: '#c8a96e' }}
              >
                Al día
              </p>
              <h2
                className="text-3xl"
                style={{ fontFamily: 'var(--font-display)', color: '#1a1e2e', fontWeight: 300 }}
              >
                Últimas publicaciones
              </h2>
            </div>
            <button
              onClick={() => onNav('avisos')}
              className="text-xs tracking-widest uppercase pb-0.5 transition-colors duration-150"
              style={{
                color: '#8494b0',
                background: 'none',
                border: 'none',
                borderBottom: '1px solid #d8d5ce',
                cursor: 'pointer',
                fontFamily: 'var(--font-body)',
              }}
              onMouseEnter={(e) => (e.currentTarget.style.color = '#1a1e2e')}
              onMouseLeave={(e) => (e.currentTarget.style.color = '#8494b0')}
            >
              Ver todas →
            </button>
          </div>

          <div className="grid md:grid-cols-2 gap-4">
            {notices.map((n) => {
              const tc = typeColors[n.type] ?? { bg: '#f3f4f6', text: '#374151' }
              return (
                <div
                  key={n.id}
                  className="p-6 flex flex-col gap-3 transition-all duration-200 cursor-pointer group"
                  style={{
                    backgroundColor: '#f7f6f3',
                    border: '1px solid #d8d5ce',
                  }}
                  onMouseEnter={(e) => {
                    ;(e.currentTarget as HTMLDivElement).style.borderColor = '#c8a96e'
                    ;(e.currentTarget as HTMLDivElement).style.backgroundColor = '#f0ede6'
                  }}
                  onMouseLeave={(e) => {
                    ;(e.currentTarget as HTMLDivElement).style.borderColor = '#d8d5ce'
                    ;(e.currentTarget as HTMLDivElement).style.backgroundColor = '#f7f6f3'
                  }}
                >
                  <div className="flex items-center justify-between">
                    <span
                      className="text-[10px] tracking-widest uppercase px-2 py-1"
                      style={{
                        backgroundColor: tc.bg,
                        color: tc.text,
                        fontFamily: 'var(--font-body)',
                        fontWeight: 500,
                      }}
                    >
                      {n.type}
                    </span>
                    {n.pinned && (
                      <span className="text-[10px] tracking-widest uppercase" style={{ color: '#c8a96e' }}>
                        Destacado
                      </span>
                    )}
                  </div>

                  <div>
                    <h3
                      className="text-lg mb-1 leading-snug"
                      style={{ fontFamily: 'var(--font-display)', color: '#1a1e2e', fontWeight: 400 }}
                    >
                      {n.title}
                    </h3>
                    <p className="text-sm leading-relaxed line-clamp-2" style={{ color: '#8494b0' }}>
                      {n.excerpt}
                    </p>
                  </div>

                  <div className="mt-auto pt-2" style={{ borderTop: '1px solid #e8e4dd' }}>
                    <p className="text-[11px]" style={{ color: '#b0a898' }}>
                      {n.author} · {n.role} — {n.date}
                    </p>
                  </div>
                </div>
              )
            })}
          </div>
        </div>
      </div>
    </div>
  )
}

function PlaceholderPage({ title, subtitle }: { title: string; subtitle: string }) {
  return (
    <div className="max-w-5xl mx-auto px-8 py-20" style={{ fontFamily: 'var(--font-body)' }}>
      <p className="text-[10px] tracking-[0.3em] uppercase mb-3" style={{ color: '#c8a96e' }}>
        {subtitle}
      </p>
      <h1
        className="text-4xl mb-4"
        style={{ fontFamily: 'var(--font-display)', color: '#1a1e2e', fontWeight: 300 }}
      >
        {title}
      </h1>
      <div className="h-px w-12 mb-8" style={{ backgroundColor: '#c8a96e' }} />
      <p className="text-sm" style={{ color: '#8494b0' }}>
        Esta sección está en construcción. Próximamente disponible.
      </p>
    </div>
  )
}

export default function App() {
  const [page, setPage] = useState<Page>('panel')

  const pageMap: Record<Exclude<Page, 'panel'>, { title: string; subtitle: string }> = {
    noticias: { title: 'Gestión de Noticias', subtitle: 'Contenido institucional' },
    avisos: { title: 'Avisos y Comunicados', subtitle: 'Tablón de publicaciones' },
    comite: { title: 'Comité de Departamento', subtitle: 'Gestión interna' },
    usuarios: { title: 'Administración de Usuarios', subtitle: 'Accesos y permisos' },
  }

  return (
    <div style={{ backgroundColor: '#f7f6f3', minHeight: '100vh' }}>
      <NavBar current={page} onNav={setPage} />
      <div style={{ paddingTop: 56 }}>
        {page === 'panel' ? (
          <PanelPage onNav={setPage} />
        ) : (
          <PlaceholderPage {...pageMap[page]} />
        )}
      </div>
    </div>
  )
}
