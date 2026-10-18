<?php
/**
 * XMOTOS - Página de confirmación de registro
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/config/helpers.php';

// Verificar que haya un registro exitoso en sesión
if (!isset($_SESSION['registro_exitoso'])) {
    header('Location: ' . base_url('registro.php'));
    exit;
}

$datos = $_SESSION['registro_exitoso'];
$pageTitle = 'Cuenta creada';
require_once __DIR__ . '/../app/views/layouts/header.php';
?>

<!-- HERO -->
<header class="hero-x" style="min-height: auto; padding: 3rem 1rem;">
  <div class="container position-relative" style="z-index: 2;">
    <div class="row justify-content-center text-center">
      <div class="col-12 col-lg-8">
        <span class="x-badge mb-3">XMOTOS · Registro</span>
        <h1 class="display-5 fw-bold mb-3">¡Cuenta creada!</h1>
        <p class="mb-0 text-x-muted fs-5">Estamos casi listos.</p>
      </div>
    </div>
  </div>
</header>

<!-- CONTENIDO -->
<section class="x-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-8 col-xl-6">
        <div class="x-card p-4 p-md-5 text-center">

          <!-- Icono -->
          <div class="x-icon-box mx-auto mb-4" style="width: 90px; height: 90px;">
            <i class="bi bi-envelope-check text-x-primary" style="font-size: 2.5rem;"></i>
          </div>

          <h2 class="h3 mb-3">Revisa tu correo electrónico</h2>

          <p class="text-x-muted mb-4">
            Hemos enviado un mensaje de verificación a:
          </p>
          <p class="text-x-primary fw-bold mb-4"><?= e($datos['email']) ?></p>

          <p class="text-x-muted mb-4">
            Abre el correo y haz clic en el enlace de verificación para activar tu cuenta.
            Una vez verificada, podrás iniciar sesión.
          </p>

          <!-- Enlace de verificación en modo DEV -->
          <?php if (($_ENV['APP_ENV'] ?? 'local') === 'local'): ?>
            <div class="alert alert-warning text-start small mb-4">
              <strong>Modo desarrollo:</strong> Como no tenemos servidor de correo configurado,
              puedes verificar tu cuenta con este enlace:
              <br>
              <a href="<?= base_url('verificar.php?token=' . $datos['token']) ?>" 
                 class="text-x-primary fw-bold">
                Verificar mi cuenta ahora
              </a>
            </div>
          <?php endif; ?>

          <!-- Botones -->
          <div class="d-flex flex-column flex-sm-row gap-2 justify-content-center">
            <a href="<?= base_url('login.php?rol=cliente') ?>" class="btn btn-x-primary">
              <i class="bi bi-box-arrow-in-right me-2"></i> Ir a iniciar sesión
            </a>
            <a href="<?= base_url() ?>" class="btn btn-x-outline">
              <i class="bi bi-house me-2"></i> Volver al inicio
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
// Limpiar sesión después de mostrar
unset($_SESSION['registro_exitoso']);
require_once __DIR__ . '/../app/views/layouts/footer.php';
?>