<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>XMOTOS — Acerca de Nosotros</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons (opcional) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root {
      --x-primary: #0d6efd;
      --x-bg-dark: #0a0a0a;
    }
    body { background: var(--x-bg-dark); color: #eaeaea; }

    /* Navbar */
    .navbar { backdrop-filter: blur(6px); background: rgba(10,10,10,.6); }

    /* Hero con fondo */
    .hero {
      position: relative;
      min-height: 56vh;
      display: grid;
      place-items: center;
      text-align: center;
      padding: 4rem 1rem 3rem;
      overflow: hidden;
    }
    .hero-media {
      position: absolute;
      inset: 0;
      z-index: 0;
    }
    .hero-media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      display: block;
    }
    .hero-overlay {
      position: absolute;
      inset: 0;
      z-index: 1;
      background: linear-gradient(180deg, rgba(0,0,0,.55), rgba(0,0,0,.65));
    }
    .hero .headline {
      z-index: 2;
      max-width: 920px;
    }

    /* Tarjetas informativas */
    .card-x {
      background: #111418;
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 1rem;
      height: 100%;
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .card-x:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 38px rgba(0,0,0,.35);
    }
    .card-x .icon {
      width: 48px; height: 48px;
      display: grid; place-items: center;
      border-radius: 12px;
      background: rgba(13,110,253,.12);
      color: #7cb1ff;
    }

    /* Bloque contacto */
    .contact-card {
      background: #0f1216;
      border: 1px solid rgba(255,255,255,.08);
      border-radius: 1rem;
    }
    .list-group-dark .list-group-item {
      background: transparent;
      color: #d3d3d3;
      border-color: rgba(255,255,255,.08);
    }

    /* Footer */
    .footer {
      background: #0b0c0f;
      border-top: 1px solid rgba(255,255,255,.08);
      color: #bdbdbd;
    }

    a.text-muted:hover { color: #eaeaea !important; }
  </style>
</head>
<body>
  <!-- NAV -->
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.html">
        <i class="bi bi-lightning-charge-fill me-1"></i> XMOTOS
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.html">Inicio</a></li>
          <li class="nav-item"><a class="nav-link active" aria-current="page" href="nosotrs.htm">Información</a></li>
          <li class="nav-item"><a class="nav-link" href="productos.html">Productos</a></li>
          <li class="nav-item"><a class="nav-link" href="coti.html">Cotización</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- HERO -->
  <header class="hero">
    <div class="hero-media">
      <!-- Si cambias la imagen y no se actualiza, añade ?v=2 -->
      <img src="img/fondo.jpg" alt="Fondo XMOTOS">
    </div>
    <div class="hero-overlay"></div>

    <div class="headline px-3">
      <span class="badge text-bg-primary mb-3">Conócenos</span>
      <h1 class="display-6 fw-bold mb-3">Pasión por las motos, compromiso con las personas</h1>
      <p class="text-secondary mb-0">
        En XMOTOS trabajamos para que vivas la mejor experiencia sobre dos ruedas.
        Servicio cercano, asesoría honesta y modelos pensados para cada estilo.
      </p>
    </div>
  </header>

  <!-- CONTENIDO PRINCIPAL -->
  <main class="container py-5">
    <!-- Misión, Visión, Sobre Nosotros -->
    <div class="row g-4 mb-5">
      <div class="col-12 col-md-4">
        <div class="card-x p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="icon me-2"><i class="bi bi-flag"></i></div>
            <h5 class="mb-0">Misión</h5>
          </div>
          <p class="text-secondary mb-0">
            Dedicación a la más alta calidad en servicio al cliente, ofrecido con
            calidez, orgullo, amistad y espíritu de compañía.
          </p>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card-x p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="icon me-2"><i class="bi bi-eye"></i></div>
            <h5 class="mb-0">Visión</h5>
          </div>
          <p class="text-secondary mb-0">
            Ser la mejor empresa de ventas de motos, reconocida por innovación,
            confianza y una experiencia de compra excepcional.
          </p>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card-x p-4">
          <div class="d-flex align-items-center mb-3">
            <div class="icon me-2"><i class="bi bi-people"></i></div>
            <h5 class="mb-0">Sobre nosotros</h5>
          </div>
          <p class="text-secondary mb-0">
            Nuestra historia está ligada al motociclismo y a la comunidad que lo
            impulsa. Crecemos con cada ruta y cada cliente, impulsando la cultura
            de movilidad y las competencias del sector.
          </p>
        </div>
      </div>
    </div>

    <!-- Contacto -->
    <div class="row g-4 align-items-stretch">
      <div class="col-12 col-lg-7">
        <div class="contact-card p-4 h-100">
          <h4 class="mb-3">Contáctanos</h4>
          <p class="text-secondary">
            ¿Tienes dudas o necesitas asesoría? Escríbenos y un especialista te acompañará en el proceso.
          </p>

          <ul class="list-group list-group-dark mb-3">
            <!-- Reemplaza estos datos por los reales -->
            <li class="list-group-item d-flex align-items-center">
              <i class="bi bi-geo-alt me-2"></i>
              <span>Av. Principal # 27 - 31, Ciudad</span>
            </li>
            <li class="list-group-item d-flex align-items-center">
              <i class="bi bi-envelope me-2"></i>
              <a class="text-decoration-none text-muted" href="mailto:contacto@xmotos.com">contacto@xmotos.com</a>
            </li>
            <li class="list-group-item d-flex align-items-center">
              <i class="bi bi-telephone me-2"></i>
              <a class="text-decoration-none text-muted" href="tel:+573001112233">+57 300 111 2233</a>
            </li>
          </ul>

          <a href="coti.html" class="btn btn-primary btn-lg">
            <i class="bi bi-clipboard-check me-2"></i> Solicitar cotización
          </a>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="contact-card p-0 overflow-hidden h-100">
          <!-- Mapa opcional (puedes sustituir el src por tu ubicación) -->
          <div class="ratio ratio-4x3">
            <iframe
              src="https://maps.google.com/maps?q=Bogota&t=&z=12&ie=UTF8&iwloc=&output=embed"
              style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- FOOTER -->
  <footer class="footer py-4">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center">
      <p class="mb-2 mb-sm-0 small">© <span id="year"></span> XMOTOS. Todos los derechos reservados.</p>
      <div class="d-flex gap-3">
        <a href="#" class="text-decoration-none text-secondary small"><i class="bi bi-facebook me-1"></i>Facebook</a>
        <a href="#" class="text-decoration-none text-secondary small"><i class="bi bi-instagram me-1"></i>Instagram</a>
        <a href="#" class="text-decoration-none text-secondary small"><i class="bi bi-youtube me-1"></i>YouTube</a>
      </div>
    </div>
  </footer>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Año actual
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
