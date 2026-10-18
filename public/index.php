<?php
/**
 * XMOTOS - Página principal
 */

declare(strict_types=1);

require_once __DIR__ . '/../app/config/helpers.php';
require_once __DIR__ . '/../app/config/database.php';

$pageTitle = 'Libertad Sobre Dos Ruedas';
require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- =========================================================
     HERO
     ========================================================= -->
<header class="hero-x">
  <div class="container position-relative" style="z-index: 2;">

    <!-- Carrusel primero -->
    <div class="row justify-content-center mb-4">
      <div class="col-12 col-lg-10 col-xl-8">
        <div id="carouselXMOTOS" class="carousel slide carousel-x rounded-4 overflow-hidden shadow-lg" data-bs-ride="carousel">
          <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselXMOTOS" data-bs-slide-to="0" class="active" aria-label="1"></button>
            <button type="button" data-bs-target="#carouselXMOTOS" data-bs-slide-to="1" aria-label="2"></button>
            <button type="button" data-bs-target="#carouselXMOTOS" data-bs-slide-to="2" aria-label="3"></button>
          </div>
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="<?= base_url('img/img1.png') ?>" class="d-block w-100" alt="XMOTOS 1">
            </div>
            <div class="carousel-item">
              <img src="<?= base_url('img/img2.PNG') ?>" class="d-block w-100" alt="XMOTOS 2">
            </div>
            <div class="carousel-item">
              <img src="<?= base_url('img/img3.PNG') ?>" class="d-block w-100" alt="XMOTOS 3">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselXMOTOS" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselXMOTOS" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
            <span class="visually-hidden">Siguiente</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Texto centrado debajo -->
    <div class="row justify-content-center text-center">
      <div class="col-12 col-lg-9 col-xl-7">
        <span class="x-badge mb-3">XMOTOS · Potencia sin límites</span>
        <h1 class="display-4 fw-bold mb-3">Libertad, potencia y diseño. <br>Tu próxima moto está aquí.</h1>
        <p class="mb-4 text-x-muted fs-5">
          No es solo movilidad: es adrenalina en cada curva. Explora nuestro catálogo, cotiza en minutos y ponte en ruta.
        </p>
        <div class="d-flex gap-2 justify-content-center flex-wrap">
          <a href="<?= base_url('productos.php') ?>" class="btn btn-x-primary btn-lg">
            <i class="bi bi-bag-fill me-2"></i> Ver catálogo
          </a>
          <a href="<?= base_url('registro.php') ?>" class="btn btn-x-outline btn-lg">
            <i class="bi bi-clipboard-check me-2"></i> Cotizar ahora
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- =========================================================
     CATEGORÍAS (dinámicas desde BD)
     ========================================================= -->
<section class="x-section" id="categorias">
  <div class="container">
    <div class="x-section-title">
      <h2>Explora por categorías</h2>
      <p class="text-x-muted mb-0">Encuentra tu estilo: sport, naked o adventure.</p>
    </div>
    <div class="row g-4">
      <?php
      $pdo = Database::getConnection();
      $categorias = $pdo->query("SELECT * FROM categorias ORDER BY id")->fetchAll();
      foreach ($categorias as $cat):
      ?>
        <div class="col-12 col-md-4">
          <div class="x-card h-100 p-4">
            <div class="d-flex align-items-center mb-3">
              <i class="bi <?= e($cat['icono']) ?> fs-3 text-x-primary me-2"></i>
              <h3 class="h5 mb-0"><?= e($cat['nombre']) ?></h3>
            </div>
            <p class="text-x-muted"><?= e($cat['descripcion']) ?></p>
            <a href="<?= base_url('productos.php?categoria=' . $cat['slug']) ?>" class="btn btn-x-primary">
              Ver modelos
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     NOSOTROS
     ========================================================= -->
<section class="x-section" id="nosotros" style="background: var(--x-surface);">
  <div class="container">
    <div class="x-section-title">
      <h2>XMOTOS</h2>
      <p class="text-x-muted mb-0">Pasión por las motos, compromiso con las personas.</p>
    </div>
    <div class="row g-4">
      <div class="col-12 col-md-4">
        <div class="x-card h-100 p-4 text-center">
          <div class="x-icon-box mx-auto mb-3">
            <i class="bi bi-flag fs-3 text-x-primary"></i>
          </div>
          <h4 class="h5">Misión</h4>
          <p class="text-x-muted mb-0">
            Dedicación a la más alta calidad en servicio al cliente, ofrecido con calidez, orgullo, amistad y espíritu de compañía.
          </p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="x-card h-100 p-4 text-center">
          <div class="x-icon-box mx-auto mb-3">
            <i class="bi bi-eye fs-3 text-x-primary"></i>
          </div>
          <h4 class="h5">Visión</h4>
          <p class="text-x-muted mb-0">
            Ser la mejor empresa de ventas de motos, reconocida por innovación, confianza y una experiencia de compra excepcional.
          </p>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="x-card h-100 p-4 text-center">
          <div class="x-icon-box mx-auto mb-3">
            <i class="bi bi-people fs-3 text-x-primary"></i>
          </div>
          <h4 class="h5">Nosotros</h4>
          <p class="text-x-muted mb-0">
            Nuestra historia está ligada al motociclismo y a la comunidad que lo impulsa. Crecemos con cada ruta y cada cliente.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =========================================================
     CONTACTO
     ========================================================= -->
<section class="x-section" id="contacto">
  <div class="container">
    <div class="x-section-title">
      <h2>Contáctenos</h2>
      <p class="text-x-muted mb-0">Visítanos o escríbenos, un especialista te acompañará.</p>
    </div>
    <div class="row g-4 align-items-stretch">
      <div class="col-12 col-lg-5">
        <div class="x-card h-100 p-4">
          <h4 class="h5 mb-3">Encuéntranos</h4>
          <ul class="list-unstyled mb-4">
            <li class="d-flex align-items-start mb-3">
              <i class="bi bi-geo-alt text-x-primary me-3 fs-5"></i>
              <a href="https://www.google.com/maps/search/?api=1&query=Cra+7+%23+2+sur+99+bl+5+ap+603,+Parque+Campestre,+Soacha" 
                 target="_blank" 
                 class="text-x-muted">
                Cra 7 # 2 sur 99 bl 5 ap 603, Parque Campestre, Soacha
              </a>
            </li>
            <li class="d-flex align-items-start mb-3">
              <i class="bi bi-envelope text-x-primary me-3 fs-5"></i>
              <a href="mailto:contacto@xmotos.com" class="text-x-muted">contacto@xmotos.com</a>
            </li>
            <li class="d-flex align-items-start mb-3">
              <i class="bi bi-telephone text-x-primary me-3 fs-5"></i>
              <a href="tel:+573001112233" class="text-x-muted">+57 300 111 2233</a>
            </li>
          </ul>
          <button type="button" class="btn btn-x-outline w-100" id="btnCompartirUbicacion">
            <i class="bi bi-share me-2"></i> Compartir ubicación
          </button>
        </div>
      </div>
      <div class="col-12 col-lg-7">
        <div class="x-card h-100 overflow-hidden p-0">
          <div class="ratio ratio-16x9">
            <iframe 
              src="https://maps.google.com/maps?q=Cra+7+%23+2+sur+99+bl+5+ap+603,+Parque+Campestre,+Soacha&t=&z=16&ie=UTF8&iwloc=&output=embed" 
              style="border:0;" 
              allowfullscreen="" 
              loading="lazy" 
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- =========================================================
     CTA FINAL
     ========================================================= -->
<section class="x-section pt-0">
  <div class="container">
    <div class="p-4 p-md-5 rounded-4 x-cta">
      <div class="row align-items-center g-3">
        <div class="col-lg-8">
          <h3 class="mb-2">¿Listo para tu cotización gratuita?</h3>
          <p class="mb-0 text-x-muted">Completa el formulario y te contactaremos con la mejor oferta.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="<?= base_url('registro.php') ?>" class="btn btn-x-primary btn-lg">
            <i class="bi bi-clipboard-check me-2"></i> Cotizar ahora
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../app/views/layouts/footer.php'; ?>